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

                $project->photos()->create([
                    'image_path' => $path,
                    'thumbnail_path' => $thumbPath,
                    'span_cols' => 1,
                    'span_rows' => 1,
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
            'span_cols' => 'required|integer|min:1|max:4',
            'span_rows' => 'required|integer|min:1|max:4',
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
     * Bulk update display order (for drag & drop reordering)
     */
    public function reorder(Request $request, Project $project)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:photos,id',
            'order.*.display_order' => 'required|integer',
        ]);

        foreach ($request->input('order') as $item) {
            Photo::where('id', $item['id'])->update(['display_order' => $item['display_order']]);
        }

        return back();
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
