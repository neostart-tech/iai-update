<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Evenement;
use App\Models\BlogComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->string('q')->toString();
        $blogsQuery = Blog::query();
        if ($q !== '') {
            $blogsQuery->where(function ($query) use ($q) {
                $query->where('title', 'like', "%$q%")
                      ->orWhere('content', 'like', "%$q%");
            });
        }
        $blogs = $blogsQuery->orderByDesc('publication_date')->paginate(4)->withQueryString();

        return view('pages.blogs.index')->with([
            'blogs' => $blogs,
            'events' => Evenement::query()->orderByDesc('id')->get(),
        ]);
    }

    public function show(Blog $blog): View
    {
        $blog->loadCount('comments');
        $blog->load(['comments' => function ($q) {
            $q->latest();
        }]);

        $recentBlogs = Blog::query()->orderByDesc('publication_date')->limit(5)->get();

        return view('pages.blogs.show', [
            'blog' => $blog,
            'recentBlogs' => $recentBlogs,
        ]);
    }

    public function storeComment(Request $request, Blog $blog): RedirectResponse
    {
        $data = $request->validate([
            'author_name' => ['required','string','max:255'],
            'author_email' => ['nullable','email','max:255'],
            'content' => ['required','string','max:500'],
        ]);

        if ($request->filled('_hp')) {
            return back()->with(['warning' => 'Soumission détectée comme spam.']);
        }

        $blog->comments()->create($data);

        return back()->with(['success' => 'Commentaire ajouté avec succès.']);
    }
}
