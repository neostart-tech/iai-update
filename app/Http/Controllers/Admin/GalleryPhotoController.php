<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryPhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $albumId = $request->query('album');
        $albums = GalleryAlbum::orderBy('name')->get();
        $photos = GalleryPhoto::query()
            ->when($albumId, fn($q) => $q->where('gallery_album_id', $albumId))
            ->with('album')
            ->orderBy('position')
            ->latest('id')
            ->paginate(40);

        return view('admin.gallery.photos.index', compact('photos','albums','albumId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $albums = GalleryAlbum::orderBy('name')->get();
        $preselectedAlbumId = $request->query('album');
        return view('admin.gallery.photos.create', compact('albums', 'preselectedAlbumId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'gallery_album_id' => ['required','exists:gallery_albums,id'],
            'title' => ['nullable','string','max:255'],
            'caption' => ['nullable','string'],
            'alt_text' => ['nullable','string','max:255'],
            'position' => ['nullable','integer','min:0'],
            'is_published' => ['nullable','boolean'],
            'taken_at' => ['nullable','date'],
            // Accept either a single file or multiple files
            'file' => ['required_without:files','image','max:10240'],
            'files' => ['required_without:file','array'],
            'files.*' => ['image','max:10240'], // 10MB per image
        ]);

        $createdCount = 0;
        $albumId = (int) $validated['gallery_album_id'];
        $commonData = [
            'gallery_album_id' => $albumId,
            'title' => $validated['title'] ?? null,
            'caption' => $validated['caption'] ?? null,
            'alt_text' => $validated['alt_text'] ?? null,
            'position' => $validated['position'] ?? 0,
            'is_published' => (bool)($validated['is_published'] ?? true),
            'taken_at' => $validated['taken_at'] ?? null,
            'published_at' => now(),
            'created_by' => optional($request->user())->id,
        ];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $uploadedFile) {
                if (!$uploadedFile) { continue; }
                $path = $uploadedFile->store('gallery/photos', 'public');
                GalleryPhoto::create(array_merge($commonData, [
                    'file_path' => $path,
                ]));
                $createdCount++;
            }
        } elseif ($request->hasFile('file')) {
            $path = $request->file('file')->store('gallery/photos', 'public');
            GalleryPhoto::create(array_merge($commonData, [
                'file_path' => $path,
            ]));
            $createdCount = 1;
        }

        return redirect()->route('admin.gallery.photos.index', ['album' => $albumId])
            ->with('success', $createdCount > 1 ? ($createdCount . ' photos ajoutées avec succès.') : 'Photo ajoutée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GalleryPhoto $galleryPhoto)
    {
        return view('admin.gallery.photos.show', ['photo' => $galleryPhoto->load('album')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GalleryPhoto $galleryPhoto)
    {
        $albums = GalleryAlbum::orderBy('name')->get();
        return view('admin.gallery.photos.edit', [
            'photo' => $galleryPhoto,
            'albums' => $albums,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GalleryPhoto $galleryPhoto)
    {
        $validated = $request->validate([
            'gallery_album_id' => ['required','exists:gallery_albums,id'],
            'title' => ['nullable','string','max:255'],
            'caption' => ['nullable','string'],
            'alt_text' => ['nullable','string','max:255'],
            'position' => ['nullable','integer','min:0'],
            'is_published' => ['nullable','boolean'],
            'taken_at' => ['nullable','date'],
            'file' => ['nullable','image','max:10240'],
        ]);

        $data = [
            'gallery_album_id' => $validated['gallery_album_id'],
            'title' => $validated['title'] ?? null,
            'caption' => $validated['caption'] ?? null,
            'alt_text' => $validated['alt_text'] ?? null,
            'position' => $validated['position'] ?? $galleryPhoto->position,
            'is_published' => (bool)($validated['is_published'] ?? $galleryPhoto->is_published),
            'taken_at' => $validated['taken_at'] ?? $galleryPhoto->taken_at,
        ];

        if ($request->hasFile('file')) {
            if ($galleryPhoto->file_path) {
                Storage::disk('public')->delete($galleryPhoto->file_path);
            }
            $data['file_path'] = $request->file('file')->store('gallery/photos', 'public');
        }

        if ($data['is_published'] && !$galleryPhoto->published_at) {
            $data['published_at'] = now();
        }

        $galleryPhoto->update($data);

        return redirect()->route('admin.gallery.photos.index', ['album' => $galleryPhoto->gallery_album_id])
            ->with('success', 'Photo mise à jour.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GalleryPhoto $galleryPhoto)
    {
        if ($galleryPhoto->file_path) {
            Storage::disk('public')->delete($galleryPhoto->file_path);
        }
        $albumId = $galleryPhoto->gallery_album_id;
        $galleryPhoto->delete();
        return redirect()->route('admin.gallery.photos.index', ['album' => $albumId])
            ->with('success', 'Photo supprimée.');
    }
}
