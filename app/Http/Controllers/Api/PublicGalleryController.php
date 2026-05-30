<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use Illuminate\Http\Request;

class PublicGalleryController extends Controller
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
            ->withCount(['photos' => function ($q) {
                $q->where('is_published', true);
            }])
            ->orderBy('name')
            ->get();

        // On peut mapper les données pour correspondre exactement à ce que le frontend attend
        $mappedAlbums = $albums->map(function($album) {
            return [
                'id' => (string) $album->id,
                'slug' => $album->slug,
                'title' => $album->name,
                'subtitle' => $album->event_date ? \Carbon\Carbon::parse($album->event_date)->format('F Y') : 'Événement',
                'photoCount' => $album->photos_count,
                'cover' => $album->photos->first() ? asset('storage/' . $album->photos->first()->image_path) : null,
                'photos' => $album->photos->map(function($photo) {
                    return asset('storage/' . $photo->image_path);
                })
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $mappedAlbums
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
                'message' => 'Album introuvable'
            ], 404);
        }

        $album->load(['photos' => function ($q) {
            $q->where('is_published', true)
              ->orderBy('position')
              ->latest('id');
        }]);

        // Autres albums récents
        $latestAlbums = GalleryAlbum::query()
            ->where('is_published', true)
            ->where('id', '!=', $album->id)
            ->latest('id')
            ->withCount(['photos' => function ($q) {
                $q->where('is_published', true);
            }])
            ->with(['photos' => function ($q) {
                $q->where('is_published', true)->take(1);
            }])
            ->take(4)
            ->get();

        $mappedAlbum = [
            'id' => (string) $album->id,
            'slug' => $album->slug,
            'title' => $album->name,
            'subtitle' => $album->event_date ? \Carbon\Carbon::parse($album->event_date)->format('F Y') : 'Événement',
            'photoCount' => $album->photos->count(),
            'cover' => $album->photos->first() ? asset('storage/' . $album->photos->first()->image_path) : null,
            'photos' => $album->photos->map(function($photo) {
                return asset('storage/' . $photo->image_path);
            })
        ];

        $mappedOtherAlbums = $latestAlbums->map(function($other) {
            return [
                'id' => (string) $other->id,
                'slug' => $other->slug,
                'title' => $other->name,
                'subtitle' => $other->event_date ? \Carbon\Carbon::parse($other->event_date)->format('F Y') : 'Événement',
                'photoCount' => $other->photos_count,
                'cover' => $other->photos->first() ? asset('storage/' . $other->photos->first()->image_path) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'album' => $mappedAlbum,
                'other_albums' => $mappedOtherAlbums
            ]
        ]);
    }
}
