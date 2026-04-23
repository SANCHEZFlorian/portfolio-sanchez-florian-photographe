<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Photo;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Inertia\Inertia;

class StandalonePhotoController extends Controller
{
    /**
     * Display a listing of standalone photos
     */
    public function index()
    {
        return Inertia::render('Admin/Standalone/Index', [
            'photos' => Photo::standalone()->with('category')->orderBy('display_order')->get(),
            'categories' => Category::all(),
        ]);
    }

    /**
     * Store standalone photos
     */
    public function store(Request $request)
    {
        $request->validate([
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:20000',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if ($request->hasFile('photos')) {
            $order = Photo::standalone()->max('display_order') ?? 0;

            foreach ($request->file('photos') as $file) {
                $path = $file->store('standalone', 'public');
                
                // Generate optimized thumbnail (800px wide)
                $thumbPath = 'standalone/thumbs/' . preg_replace('/\.[^.]+$/', '.webp', basename($path));
                $this->generateThumbnail($file, $thumbPath);

                Photo::create([
                    'image_path' => $path,
                    'thumbnail_path' => $thumbPath,
                    'category_id' => $request->input('category_id'),
                    'span_cols' => 1,
                    'span_rows' => 1,
                    'display_order' => ++$order,
                ]);
            }
        }

        return back()->with('success', 'Photos ajoutées à la galerie.');
    }

    /**
     * Update photo properties
     */
    public function update(Request $request, Photo $photo)
    {
        $validated = $request->validate([
            'caption' => 'nullable|string',
            'instagram_tag' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'span_cols' => 'required|integer|min:1|max:4',
            'span_rows' => 'required|integer|min:1|max:4',
            'display_order' => 'required|integer',
            'is_highlighted' => 'sometimes|boolean',
        ]);

        $photo->update($validated);

        return back();
    }

    /**
     * Delete a photo
     */
    public function destroy(Photo $photo)
    {
        Storage::disk('public')->delete($photo->image_path);
        if ($photo->thumbnail_path) {
            Storage::disk('public')->delete($photo->thumbnail_path);
        }

        $photo->delete();

        return back()->with('success', 'Photo supprimée.');
    }

    /**
     * Bulk update display order
     */
    public function reorder(Request $request)
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
     * Generate an optimized thumbnail
     */
    private function generateThumbnail($file, string $thumbPath): void
    {
        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->decode($file);
            $image->scaleDown(width: 800);
            $encoded = $image->encodeUsingFileExtension('webp', quality: 80);
            Storage::disk('public')->put($thumbPath, (string) $encoded);
        } catch (\Exception $e) {
            \Log::warning('Thumbnail generation failed: ' . $e->getMessage());
        }
    }
}
