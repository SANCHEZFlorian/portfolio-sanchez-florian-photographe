<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Photo;
use App\Models\Project;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMessage;
use App\Mail\ContactConfirmation;
use Inertia\Inertia;

class PublicController extends Controller
{
    /**
     * Page d'accueil — hero + projets featured + photos en avant
     */
    public function home()
    {
        $featuredProjects = Project::published()
            ->featured()
            ->with('category', 'photos')
            ->latest('date')
            ->take(6)
            ->get()
            ->map(function ($project) {
                $cover = $project->photos->where('is_cover', true)->first()
                    ?? $project->photos->first();
                $project->cover_url = $cover
                    ? '/storage/' . ($cover->thumbnail_path ?? $cover->image_path)
                    : null;
                $project->photo_count = $project->photos->count();
                return $project;
            });

        $highlightedPhotos = Photo::where('is_highlighted', true)
            ->with('project')
            ->orderBy('display_order')
            ->take(8)
            ->get();

        $settings = SiteSetting::allAsArray();

        return Inertia::render('Public/Home', [
            'featuredProjects' => $featuredProjects,
            'highlightedPhotos' => $highlightedPhotos,
            'settings' => $settings,
        ]);
    }

    /**
     * Liste des projets avec filtre par catégorie
     */
    public function projects(Request $request)
    {
        $query = Project::published()->with('category', 'photos')->latest('date');

        if ($request->has('category') && $request->category !== 'all') {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        $projects = $query->get()->map(function ($project) {
            $cover = $project->photos->where('is_cover', true)->first()
                ?? $project->photos->first();
            $project->cover_url = $cover
                ? '/storage/' . ($cover->thumbnail_path ?? $cover->image_path)
                : null;
            $project->photo_count = $project->photos->count();
            return $project;
        });

        $categories = Category::whereHas('projects', fn($q) => $q->published())->get();

        return Inertia::render('Public/Projects', [
            'projects' => $projects,
            'categories' => $categories,
            'currentCategory' => $request->category ?? 'all',
        ]);
    }

    /**
     * Détail d'un projet — galerie masonry
     */
    public function projectShow(string $slug)
    {
        $project = Project::published()
            ->where('slug', $slug)
            ->with(['category', 'photos' => fn($q) => $q->orderBy('display_order')])
            ->firstOrFail();

        return Inertia::render('Public/ProjectShow', [
            'project' => $project,
        ]);
    }

    /**
     * Page galerie — photos en vrac (standalone) + toutes les photos
     */
    public function gallery(Request $request)
    {
        $query = Photo::standalone()->with('category')->orderBy('display_order');

        if ($request->has('category') && $request->category !== 'all') {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        $photos = $query->get();
        $categories = Category::whereHas('photos', fn($q) => $q->standalone())->get();

        return Inertia::render('Public/Gallery', [
            'photos' => $photos,
            'categories' => $categories,
            'currentCategory' => $request->category ?? 'all',
        ]);
    }

    /**
     * Page À propos
     */
    public function about()
    {
        $settings = SiteSetting::allAsArray();

        return Inertia::render('Public/About', [
            'settings' => $settings,
        ]);
    }

    /**
     * Page Contact — formulaire
     */
    public function contact()
    {
        $settings = SiteSetting::allAsArray();

        return Inertia::render('Public/Contact', [
            'settings' => $settings,
        ]);
    }

    /**
     * Soumission du formulaire de contact
     */
    public function contactStore(Request $request)
    {
        // Honeypot check
        if ($request->filled('full_name')) {
            return back()->with('success', 'Votre message a été envoyé avec succès !');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $contact = Contact::create($validated);

        // Notify admin
        try {
            Mail::to(config('mail.from.address'))->send(new ContactMessage($contact));
            
            // Send confirmation to user
            Mail::to($contact->email)->send(new ContactConfirmation($contact));
        } catch (\Exception $e) {
            \Log::error('Mail sending failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Votre message a été envoyé avec succès !');
    }
}
