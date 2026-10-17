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
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:102400',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if ($request->hasFile('photos')) {
            $order = Photo::standalone()->max('display_order') ?? 0;

            foreach ($request->file('photos') as $file) {
                $path = $file->store('standalone', 'public');
                
                // Generate optimized thumbnail (800px wide)
                $thumbPath = 'standalone/thumbs/' . preg_replace('/\.[^.]+$/', '.webp', basename($path));
                $this->generateThumbnail($file, $thumbPath);

                // Auto-detect orientation: Landscape -> 3x2 (Petit Paysage), Portrait -> 2x3 (Petit Portrait)
                $imageSize = @getimagesize($file->getRealPath());
                $isLandscape = (!$imageSize || $imageSize[0] >= $imageSize[1]);
                $cols = $isLandscape ? 3 : 2;
                $rows = $isLandscape ? 2 : 3;

                Photo::create([
                    'image_path' => $path,
                    'thumbnail_path' => $thumbPath,
                    'category_id' => $request->input('category_id'),
                    'span_cols' => $cols,
                    'span_rows' => $rows,
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
            'span_cols' => 'required|integer|min:1|max:12',
            'span_rows' => 'required|integer|min:1|max:12',
            'col_start' => 'nullable|integer|min:1|max:12',
            'row_start' => 'nullable|integer|min:1',
            'display_order' => 'required|integer',
            'is_highlighted' => 'sometimes|boolean',
        ]);

        $photo->update($validated);

        return back();
    }

    /**
     * Bulk action on standalone photos
     */
     public function bulkAction(Request $request)
     {
         $validated = $request->validate([
             'ids' => 'required|array',
             'ids.*' => 'integer|exists:photos,id',
             'action' => 'required|string|in:set_format,set_category,set_instagram_tag,set_highlight,remove_highlight',
             'span_cols' => 'nullable|integer',
             'span_rows' => 'nullable|integer',
             'category_id' => 'nullable|exists:categories,id',
             'instagram_tag' => 'nullable|string|max:255',
         ]);

         $query = Photo::standalone()->whereIn('id', $validated['ids']);

         switch ($validated['action']) {
             case 'set_format':
                 if (isset($validated['span_cols']) && isset($validated['span_rows'])) {
                     $query->update([
                         'span_cols' => $validated['span_cols'],
                         'span_rows' => $validated['span_rows'],
                     ]);
                 }
                 break;
             case 'set_category':
                 $query->update(['category_id' => $validated['category_id'] ?? null]);
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
     * Bulk delete standalone photos
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:photos,id',
        ]);

        $photos = Photo::standalone()->whereIn('id', $validated['ids'])->get();

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
     * Bulk update display order and optional grid positions
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:photos,id',
            'order.*.display_order' => 'required|integer',
            'order.*.col_start' => 'nullable|integer',
            'order.*.row_start' => 'nullable|integer',
        ]);

        foreach ($request->input('order') as $item) {
            $updateData = ['display_order' => $item['display_order']];
            if (array_key_exists('col_start', $item)) {
                $updateData['col_start'] = $item['col_start'];
            }
            if (array_key_exists('row_start', $item)) {
                $updateData['row_start'] = $item['row_start'];
            }
            Photo::where('id', $item['id'])->update($updateData);
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
