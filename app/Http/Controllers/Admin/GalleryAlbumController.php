<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleryAlbumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $albums = GalleryAlbum::query()
            ->withCount('photos')
            ->latest('id')
            ->paginate(20);

        return view('admin.gallery.albums.index', compact('albums'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.gallery.albums.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255','unique:gallery_albums,slug'],
            'description' => ['nullable','string'],
            'is_published' => ['nullable','boolean'],
            'cover' => ['nullable','image','max:5120'], // 5MB
        ]);

        $data = [
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_published' => (bool)($validated['is_published'] ?? true),
            'published_at' => now(),
            'created_by' => optional($request->user())->id,
        ];

        if (!empty($validated['cover'])) {
            $data['cover_path'] = $request->file('cover')->store('gallery/covers', 'public');
        }

        $album = GalleryAlbum::create($data);

        return redirect()->route('admin.gallery.albums.index')
            ->with('success', 'Album créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GalleryAlbum $galleryAlbum)
    {
        $galleryAlbum->load(['photos' => function ($q) {
            $q->orderBy('position')->orderByDesc('id');
        }]);
        return view('admin.gallery.albums.show', ['album' => $galleryAlbum]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GalleryAlbum $galleryAlbum)
    {
        return view('admin.gallery.albums.edit', ['album' => $galleryAlbum]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GalleryAlbum $galleryAlbum)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255','unique:gallery_albums,slug,'.$galleryAlbum->id],
            'description' => ['nullable','string'],
            'is_published' => ['nullable','boolean'],
            'cover' => ['nullable','image','max:5120'],
        ]);

        $data = [
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? $galleryAlbum->slug,
            'description' => $validated['description'] ?? null,
            'is_published' => (bool)($validated['is_published'] ?? $galleryAlbum->is_published),
        ];

        // If slug not provided and name changed, regenerate from name
        if (empty($validated['slug']) && $galleryAlbum->getAttribute('name') !== $validated['name']) {
            $data['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('cover')) {
            if ($galleryAlbum->cover_path) {
                Storage::disk('public')->delete($galleryAlbum->cover_path);
            }
            $data['cover_path'] = $request->file('cover')->store('gallery/covers', 'public');
        }

        if ($data['is_published'] && !$galleryAlbum->published_at) {
            $data['published_at'] = now();
        }

        $galleryAlbum->update($data);

        return redirect()->route('admin.gallery.albums.index')
            ->with('success', 'Album mis à jour.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GalleryAlbum $galleryAlbum)
    {
        if ($galleryAlbum->cover_path) {
            Storage::disk('public')->delete($galleryAlbum->cover_path);
        }
        $galleryAlbum->delete();
        return redirect()->route('admin.gallery.albums.index')
            ->with('success', 'Album supprimé.');
    }
}
