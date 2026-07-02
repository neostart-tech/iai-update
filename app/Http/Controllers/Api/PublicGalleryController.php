<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use Illuminate\Http\Request;

class PublicGalleryController extends Controller
{
    /** Retourne l'URL publique d'un chemin storage. */
    private function storageUrl(?string $path): ?string
    {
        return $path ? asset('storage/' . $path) : null;
    }

    /** Couverture : cover_path de l'album, sinon première photo publiée. */
    private function resolveAlbumCover(GalleryAlbum $album): ?string
    {
        if ($album->cover_path) {
            return $this->storageUrl($album->cover_path);
        }
        $firstPhoto = $album->photos->first();
        return $firstPhoto?->file_path ? $this->storageUrl($firstPhoto->file_path) : null;
    }

    public function index(Request $request)
    {
        $albums = GalleryAlbum::query()
            ->where('is_published', true)
            ->with(['photos' => function ($q) {
                $q->where('is_published', true)
                  ->orderBy('position')
                  ->latest('id');
            }])
            ->withCount(['photos' => function ($q) {
                $q->where('is_published', true);
            }])
            ->orderBy('name')
            ->get();

        $mappedAlbums = $albums->map(function (GalleryAlbum $album) {
            return [
                'id'         => (string) $album->id,
                'slug'       => $album->slug,
                'title'      => $album->name,
                'subtitle'   => $album->event_date
                    ? \Carbon\Carbon::parse($album->event_date)->format('F Y')
                    : 'Événement',
                'photoCount' => $album->photos_count,
                'cover'      => $this->resolveAlbumCover($album),
                'cover_url'  => $this->resolveAlbumCover($album),
                'photos'     => $album->photos->map(function ($photo) {
                    return $photo->file_path ? $this->storageUrl($photo->file_path) : null;
                })->filter()->values(),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $mappedAlbums,
        ]);
    }

    public function show($id)
    {
        $albumQuery = GalleryAlbum::query()->where('is_published', true);

        if (is_numeric($id)) {
            $albumQuery->where('id', $id);
        } else {
            $albumQuery->where('slug', $id);
        }

        $album = $albumQuery->first();

        if (!$album) {
            return response()->json([
                'success' => false,
                'message' => 'Album introuvable',
            ], 404);
        }

        $album->load(['photos' => function ($q) {
            $q->where('is_published', true)
              ->orderBy('position')
              ->latest('id');
        }]);

        $latestAlbums = GalleryAlbum::query()
            ->where('is_published', true)
            ->where('id', '!=', $album->id)
            ->latest('id')
            ->withCount(['photos' => function ($q) {
                $q->where('is_published', true);
            }])
            ->with(['photos' => function ($q) {
                $q->where('is_published', true)->orderBy('position')->latest('id')->take(1);
            }])
            ->take(4)
            ->get();

        $mappedAlbum = [
            'id'         => (string) $album->id,
            'slug'       => $album->slug,
            'title'      => $album->name,
            'subtitle'   => $album->event_date
                ? \Carbon\Carbon::parse($album->event_date)->format('F Y')
                : 'Événement',
            'photoCount' => $album->photos->count(),
            'cover'      => $this->resolveAlbumCover($album),
            'cover_url'  => $this->resolveAlbumCover($album),
            'photos'     => $album->photos->map(function ($photo) {
                return $photo->file_path ? $this->storageUrl($photo->file_path) : null;
            })->filter()->values(),
        ];

        $mappedOtherAlbums = $latestAlbums->map(function (GalleryAlbum $other) {
            return [
                'id'         => (string) $other->id,
                'slug'       => $other->slug,
                'title'      => $other->name,
                'subtitle'   => $other->event_date
                    ? \Carbon\Carbon::parse($other->event_date)->format('F Y')
                    : 'Événement',
                'photoCount' => $other->photos_count,
                'cover'      => $this->resolveAlbumCover($other),
                'cover_url'  => $this->resolveAlbumCover($other),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'album'        => $mappedAlbum,
                'other_albums' => $mappedOtherAlbums,
            ],
        ]);
    }
}
