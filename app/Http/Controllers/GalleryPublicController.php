<?php

namespace App\Http\Controllers;

use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use Illuminate\Http\Request;

class GalleryPublicController extends Controller
{
    public function index(Request $request)
    {
        $albums = GalleryAlbum::query()
            ->where('is_published', true)
            ->with(['photos' => function ($q) {
                $q->where('is_published', true)
                  ->orderBy('position')
                  ->latest('id');
            }])
            ->orderBy('name')
            ->get();

        $albumId = $request->query('album');
        $photos = GalleryPhoto::query()
            ->where('is_published', true)
            ->when($albumId, fn($q) => $q->where('gallery_album_id', $albumId))
            ->orderBy('position')
            ->latest('id')
            ->paginate(24)
            ->withQueryString();

        return view('pages.galerie', [
            'albums' => $albums,
            'photos' => $photos,
            'albumId' => $albumId,
        ]);
    }

    public function show(GalleryAlbum $album)
    {
        abort_unless($album->is_published, 404);

        $album->load(['photos' => function ($q) {
            $q->where('is_published', true)
              ->orderBy('position')
              ->latest('id');
        }]);

        $photos = $album->photos()->where('is_published', true)
            ->orderBy('position')
            ->latest('id')
            ->paginate(24);

        // Latest 4 published albums excluding current
        $latestAlbums = GalleryAlbum::query()
            ->where('is_published', true)
            ->where('id', '!=', $album->id)
            ->latest('id')
            ->withCount(['photos' => function ($q) {
                $q->where('is_published', true);
            }])
            ->take(4)
            ->get();

        return view('pages.galerie-album', [
            'album' => $album,
            'photos' => $photos,
            'latestAlbums' => $latestAlbums,
        ]);
    }
}
