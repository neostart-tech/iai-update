<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Evenement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicBlogController extends Controller
{
    public function index(Request $request)
    {
        $blogQuery = Blog::query()->where('status', 'published');
        $eventQuery = Evenement::query()
            ->where(function($q) {
                $q->where('type', 'public')
                  ->orWhereIn('destination', ['website', 'all']);
            });

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $blogQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
            $eventQuery->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('details', 'like', "%{$search}%");
            });
        }

        $blogs = $blogQuery->get()->map(function ($blog) {
            return [
                'id' => $blog->id,
                'title' => $blog->title,
                'excerpt' => mb_strimwidth(html_entity_decode(strip_tags($blog->content)), 0, 150, '...'),
                'author' => $blog->author_name ?? 'Admin',
                'date' => \Carbon\Carbon::parse($blog->publication_date)->format('d M Y'),
                'readTime' => ceil(str_word_count(strip_tags($blog->content)) / 200) . ' min',
                'featured' => false,
                'image' => $blog->image ? $blog->getFullPath() : "https://images.unsplash.com/photo-1498050108023-c5249f4df085",
                'slug' => $blog->slug,
                'sort_date' => \Carbon\Carbon::parse($blog->publication_date)->timestamp,
                'is_event' => false
            ];
        });

        $events = $eventQuery->get()->map(function ($event) {
            return [
                'id' => 'evt_' . $event->id, // Prevent ID collision
                'title' => $event->nom,
                'excerpt' => mb_strimwidth(html_entity_decode(strip_tags($event->details)), 0, 150, '...'),
                'author' => 'Administration', // Events usually don't have authors
                'date' => \Carbon\Carbon::parse($event->created_at)->format('d M Y'),
                'start_date' => $event->debut ? \Carbon\Carbon::parse($event->debut)->format('d M Y') : ($event->start_date ? \Carbon\Carbon::parse($event->start_date)->format('d M Y') : null),
                'end_date' => $event->fin ? \Carbon\Carbon::parse($event->fin)->format('d M Y') : ($event->end_date ? \Carbon\Carbon::parse($event->end_date)->format('d M Y') : null),
                'readTime' => ceil(str_word_count(strip_tags($event->details)) / 200) . ' min',
                'featured' => false,
                'image' => $event->image ? asset(Storage::url($event->image)) : "https://images.unsplash.com/photo-1498050108023-c5249f4df085",
                'slug' => $event->slug,
                'sort_date' => \Carbon\Carbon::parse($event->created_at)->timestamp,
                'is_event' => true
            ];
        });

        $merged = $blogs->concat($events)->sortByDesc('sort_date')->values();

        return response()->json($merged);
    }

    public function show($idOrSlug)
    {
        $query = Blog::query();
        
        if (is_numeric($idOrSlug)) {
            $blog = $query->where('id', $idOrSlug)->first();
        } else {
            $blog = $query->where('slug', $idOrSlug)->first();
        }

        if ($blog) {
            if ($blog->status !== 'published' && $blog->status !== 1 && $blog->status !== 'Publié') {
                return response()->json(['message' => 'Cet article n\'est pas encore publié.'], 403);
            }

            $comments = $blog->comments()->where('status', 'approved')->orderBy('created_at', 'desc')->get()->map(function($comment) {
                return [
                    'id' => $comment->id,
                    'author' => $comment->author_name,
                    'date' => $comment->created_at->format('d M Y'),
                    'content' => $comment->content,
                    'rating' => $comment->rating
                ];
            });

            return response()->json([
                'id' => $blog->id,
                'title' => $blog->title,
                'content' => $blog->content,
                'author' => $blog->author_name ?? 'Admin',
                'date' => \Carbon\Carbon::parse($blog->publication_date)->format('d M Y'),
                'readTime' => ceil(str_word_count(strip_tags($blog->content)) / 200) . ' min',
                'featured' => false,
                'image' => $blog->image ? $blog->getFullPath() : "https://images.unsplash.com/photo-1498050108023-c5249f4df085",
                'slug' => $blog->slug,
                'comments' => $comments,
                'is_event' => false
            ]);
        }
        
        // Fallback to Evenement
        $eventQuery = Evenement::query();
        if (is_numeric($idOrSlug)) {
            $event = $eventQuery->where('id', $idOrSlug)->firstOrFail();
        } else {
            $event = $eventQuery->where('slug', $idOrSlug)->firstOrFail();
        }
        
        return response()->json([
            'id' => 'evt_' . $event->id,
            'title' => $event->nom,
            'content' => $event->details,
            'author' => 'Administration',
            'date' => \Carbon\Carbon::parse($event->created_at)->format('d M Y'),
            'start_date' => $event->debut ? \Carbon\Carbon::parse($event->debut)->format('d M Y') : ($event->start_date ? \Carbon\Carbon::parse($event->start_date)->format('d M Y') : null),
            'end_date' => $event->fin ? \Carbon\Carbon::parse($event->fin)->format('d M Y') : ($event->end_date ? \Carbon\Carbon::parse($event->end_date)->format('d M Y') : null),
            'readTime' => ceil(str_word_count(strip_tags($event->details)) / 200) . ' min',
            'featured' => false,
            'image' => $event->image ? asset(Storage::url($event->image)) : "https://images.unsplash.com/photo-1498050108023-c5249f4df085",
            'slug' => $event->slug,
            'comments' => [], // Evenements don't have approved comments in this context
            'is_event' => true
        ]);
    }

    public function addComment(Request $request, $idOrSlug)
    {
        $request->validate([
            'author_name' => 'required|string|max:255',
            'author_email' => 'required|email|max:255',
            'content' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $query = Blog::query();
        if (is_numeric($idOrSlug)) {
            $blog = $query->where('id', $idOrSlug)->firstOrFail();
        } else {
            $blog = $query->where('slug', $idOrSlug)->firstOrFail();
        }

        $comment = $blog->comments()->create([
            'author_name' => $request->author_name,
            'author_email' => $request->author_email,
            'content' => $request->content,
            'rating' => $request->rating,
            'status' => 'approved' // Approuvé par défaut
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Votre commentaire a été soumis et est en attente d\'approbation.'
        ]);
    }
}
