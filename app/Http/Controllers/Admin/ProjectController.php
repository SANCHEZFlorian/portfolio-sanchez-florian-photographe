<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = \App\Models\Project::with('category')
            ->withCount('photos')
            ->latest()
            ->get();

        return inertia('Admin/Project/Index', [
            'projects' => $projects,
        ]);
    }

    public function create()
    {
        return inertia('Admin/Project/Create', [
            'categories' => \App\Models\Category::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'instagram_url' => 'nullable|url|max:255',
            'date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'is_featured' => 'sometimes|boolean',
            'status' => 'sometimes|in:draft,published',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        // Ensure unique slug
        $base = $validated['slug'];
        $i = 1;
        while (\App\Models\Project::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $base . '-' . $i++;
        }

        $project = \App\Models\Project::create($validated);

        return redirect()->route('admin.projects.edit', $project->id);
    }

    public function show(string $id)
    {
        return redirect()->route('admin.projects.edit', $id);
    }

    public function edit(string $id)
    {
        $project = \App\Models\Project::with(['photos' => fn($q) => $q->orderBy('display_order')])->findOrFail($id);

        return inertia('Admin/Project/Edit', [
            'project' => $project,
            'categories' => \App\Models\Category::all()
        ]);
    }

    public function update(Request $request, string $id)
    {
        $project = \App\Models\Project::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'instagram_url' => 'nullable|url|max:255',
            'date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'is_featured' => 'sometimes|boolean',
            'status' => 'sometimes|in:draft,published',
        ]);

        $validated['slug'] = Str::slug($validated['title']);

        // Ensure unique slug (exclude self)
        $base = $validated['slug'];
        $i = 1;
        while (\App\Models\Project::where('slug', $validated['slug'])->where('id', '!=', $project->id)->exists()) {
            $validated['slug'] = $base . '-' . $i++;
        }

        $project->update($validated);

        return back()->with('success', 'Projet mis à jour.');
    }

    public function destroy(string $id)
    {
        $project = \App\Models\Project::findOrFail($id);

        // Delete the entire project directory (originals + thumbs)
        Storage::disk('public')->deleteDirectory('projects/' . $project->id);

        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Projet supprimé.');
    }
}
