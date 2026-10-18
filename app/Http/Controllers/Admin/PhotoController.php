<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Photo;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PhotoController extends Controller
{
    /**
     * Store photos — with thumbnail generation for optimization
     */
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:102400',
        ]);

        if ($request->hasFile('photos')) {
            $order = $project->photos()->max('display_order') ?? 0;

            foreach ($request->file('photos') as $file) {
                // Store original (high quality)
                $path = $file->store('projects/' . $project->id, 'public');

                // Generate optimized thumbnail (800px wide)
                $thumbPath = 'projects/' . $project->id . '/thumbs/' . preg_replace('/\.[^.]+$/', '.webp', basename($path));
                $this->generateThumbnail($file, $thumbPath);

                // Auto-detect orientation: Landscape -> 3x2 (Petit Paysage), Portrait -> 2x3 (Petit Portrait)
                $imageSize = @getimagesize($file->getRealPath());
                $isLandscape = (!$imageSize || $imageSize[0] >= $imageSize[1]);
                $cols = $isLandscape ? 3 : 2;
                $rows = $isLandscape ? 2 : 3;

                $project->photos()->create([
                    'image_path' => $path,
                    'thumbnail_path' => $thumbPath,
                    'span_cols' => $cols,
                    'span_rows' => $rows,
                    'display_order' => ++$order,
                ]);
            }
        }

        return back()->with('success', count($request->file('photos', [])) . ' photo(s) ajoutée(s).');
    }


    /**
     * Update photo properties (grid size, caption, tags, order)
     */
    public function update(Request $request, Project $project, Photo $photo)
    {
        $validated = $request->validate([
            'caption' => 'nullable|string',
            'instagram_tag' => 'nullable|string',
            'span_cols' => 'required|integer|min:1|max:12',
            'span_rows' => 'required|integer|min:1|max:12',
            'col_start' => 'nullable|integer|min:1|max:12',
            'row_start' => 'nullable|integer|min:1',
            'display_order' => 'required|integer',
            'is_highlighted' => 'sometimes|boolean',
            'is_cover' => 'sometimes|boolean',
        ]);

        // If setting as cover, unset other covers in same project
        if (!empty($validated['is_cover']) && $photo->project_id) {
            Photo::where('project_id', $photo->project_id)
                ->where('id', '!=', $photo->id)
                ->update(['is_cover' => false]);
        }

        $photo->update($validated);

        return back();
    }

    /**
     * Bulk update display order and optional grid positions
     */
    public function reorder(Request $request, Project $project)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:photos,id',
            'order.*.display_order' => 'required|integer',
            'order.*.col_start' => 'nullable|integer',
            'order.*.row_start' => 'nullable|integer',
            'order.*.span_cols' => 'nullable|integer',
            'order.*.span_rows' => 'nullable|integer',
        ]);

        foreach ($request->input('order') as $item) {
            $updateData = ['display_order' => $item['display_order']];
            if (array_key_exists('col_start', $item)) {
                $updateData['col_start'] = $item['col_start'];
            }
            if (array_key_exists('row_start', $item)) {
                $updateData['row_start'] = $item['row_start'];
            }
            if (array_key_exists('span_cols', $item)) {
                $updateData['span_cols'] = $item['span_cols'];
            }
            if (array_key_exists('span_rows', $item)) {
                $updateData['span_rows'] = $item['span_rows'];
            }
            Photo::where('id', $item['id'])->update($updateData);
        }

        return back();
    }

    /**
     * Bulk action on multiple photos (format, tags, highlight)
     */
    public function bulkAction(Request $request, Project $project)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:photos,id',
            'action' => 'required|string|in:set_format,set_instagram_tag,set_highlight,remove_highlight',
            'span_cols' => 'nullable|integer',
            'span_rows' => 'nullable|integer',
            'instagram_tag' => 'nullable|string|max:255',
        ]);

        $query = $project->photos()->whereIn('id', $validated['ids']);

        switch ($validated['action']) {
            case 'set_format':
                if (isset($validated['span_cols']) && isset($validated['span_rows'])) {
                    $query->update([
                        'span_cols' => $validated['span_cols'],
                        'span_rows' => $validated['span_rows'],
                    ]);
                }
                break;
            case 'set_instagram_tag':
                $query->update(['instagram_tag' => $validated['instagram_tag'] ?? null]);
                break;
            case 'set_highlight':
                $query->update(['is_highlighted' => true]);
                break;
            case 'remove_highlight':
                $query->update(['is_highlighted' => false]);
                break;
        }

        return back()->with('success', count($validated['ids']) . ' photo(s) mise(s) à jour.');
    }

    /**
     * Bulk delete multiple photos
     */
    public function bulkDestroy(Request $request, Project $project)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:photos,id',
        ]);

        $photos = $project->photos()->whereIn('id', $validated['ids'])->get();

        foreach ($photos as $photo) {
            Storage::disk('public')->delete($photo->image_path);
            if ($photo->thumbnail_path) {
                Storage::disk('public')->delete($photo->thumbnail_path);
            }
            $photo->delete();
        }

        return back()->with('success', count($photos) . ' photo(s) supprimée(s).');
    }

    /**
     * Delete a photo
     */
    public function destroy(Project $project, Photo $photo)
    {
        Storage::disk('public')->delete($photo->image_path);
        if ($photo->thumbnail_path) {
            Storage::disk('public')->delete($photo->thumbnail_path);
        }

        $photo->delete();

        return back()->with('success', 'Photo supprimée.');
    }

    /**
     * Generate an optimized thumbnail
     */
    private function generateThumbnail($file, string $thumbPath): void
    {
        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->decode($file);

            // Resize to max 800px width, keeping aspect ratio
            $image->scaleDown(width: 800);

            // Encode as WebP for best compression (quality 80)
            $encoded = $image->encodeUsingFileExtension('webp', quality: 80);

            Storage::disk('public')->put($thumbPath, (string) $encoded);
        } catch (\Exception $e) {
            // If thumbnail generation fails, just skip it silently
            \Log::warning('Thumbnail generation failed: ' . $e->getMessage());
        }
    }
}
