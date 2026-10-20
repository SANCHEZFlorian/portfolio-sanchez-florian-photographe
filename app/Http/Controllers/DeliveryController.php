<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Project;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use ZipArchive;

class DeliveryController extends Controller
{
    /**
     * Affiche l'espace de livraison client d'un projet via son lien sécurisé
     */
    public function show(string $token)
    {
        $project = Project::where('delivery_token', $token)
            ->where('delivery_enabled', true)
            ->with(['category', 'photos' => fn($q) => $q->orderBy('display_order')])
            ->firstOrFail();

        // Vérification de la date d'expiration éventuelle
        if ($project->delivery_expires_at && $project->delivery_expires_at->isPast()) {
            abort(404, 'Ce lien de livraison a expiré.');
        }

        // Calcul de la taille totale des photos originales
        $totalBytes = 0;
        foreach ($project->photos as $photo) {
            if (Storage::disk('public')->exists($photo->image_path)) {
                $totalBytes += Storage::disk('public')->size($photo->image_path);
            }
        }

        $formattedSize = $this->formatBytes($totalBytes);
        $settings = SiteSetting::allAsArray();

        return Inertia::render('Public/DeliveryShow', [
            'project' => $project,
            'totalSizeFormatted' => $formattedSize,
            'settings' => $settings,
        ]);
    }

    /**
     * Télécharge l'archive complète ZIP contenant toutes les photos originales HD avec métadonnées intactes
     */
    public function downloadZip(string $token)
    {
        $project = Project::where('delivery_token', $token)
            ->where('delivery_enabled', true)
            ->with(['photos' => fn($q) => $q->orderBy('display_order')])
            ->firstOrFail();

        if ($project->delivery_expires_at && $project->delivery_expires_at->isPast()) {
            abort(404, 'Ce lien de livraison a expiré.');
        }

        if ($project->photos->isEmpty()) {
            return back()->with('error', 'Aucune photo disponible pour ce projet.');
        }

        // Dossier temporaire pour le ZIP
        $tempDir = storage_path('app/temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $zipFileName = 'Shooting_' . Str::slug($project->delivery_title ?: $project->title) . '_Florian_Sanchez.zip';
        $zipPath = $tempDir . '/' . uniqid('delivery_zip_') . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Impossible de générer l\'archive ZIP.');
        }

        $index = 1;
        foreach ($project->photos as $photo) {
            if (Storage::disk('public')->exists($photo->image_path)) {
                $filePath = Storage::disk('public')->path($photo->image_path);
                $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                // Nommage soigné du fichier dans le ZIP avec numéro séquentiel
                $photoSlug = $photo->caption ? Str::slug($photo->caption) : 'photo';
                $entryName = sprintf('%02d_%s.%s', $index, $photoSlug, $ext);

                $zip->addFile($filePath, $entryName);
                $index++;
            }
        }

        // Si aucune photo n'a pu être trouvée sur le disque, on ajoute une note
        if ($index === 1) {
            $zip->addFromString('README.txt', "Shooting: " . ($project->delivery_title ?: $project->title) . "\nPhotographe: Florian Sanchez\n");
        }

        $zip->close();

        // Incrémenter le compteur de téléchargements
        $project->increment('delivery_download_count');

        return response()->download($zipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Télécharge une photo individuelle en haute définition originale avec toutes ses métadonnées EXIF
     */
    public function downloadPhoto(string $token, Photo $photo)
    {
        $project = Project::where('delivery_token', $token)
            ->where('delivery_enabled', true)
            ->firstOrFail();

        if ($project->delivery_expires_at && $project->delivery_expires_at->isPast()) {
            abort(404, 'Ce lien de livraison a expiré.');
        }

        if ($photo->project_id !== $project->id) {
            abort(403, 'Cette photo n\'appartient pas à ce projet.');
        }

        if (!Storage::disk('public')->exists($photo->image_path)) {
            abort(404, 'Le fichier original est introuvable.');
        }

        $filePath = Storage::disk('public')->path($photo->image_path);
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $downloadName = sprintf(
            '%s_Florian_Sanchez_%02d.%s',
            Str::slug($project->delivery_title ?: $project->title),
            $photo->display_order ?: $photo->id,
            $ext
        );

        return response()->download($filePath, $downloadName);
    }

    /**
     * Formate les octets en format lisible (Mo, Go)
     */
    private function formatBytes(int $bytes, int $precision = 1): string
    {
        if ($bytes <= 0) return '0 Mo';
        $units = ['o', 'Ko', 'Mo', 'Go', 'To'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return round($bytes / pow(1024, $power), $precision) . ' ' . ($units[$power] ?? 'Mo');
    }
}
