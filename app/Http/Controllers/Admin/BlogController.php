<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogRequest;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use App\Traits\FileManagementTrait;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PublicationNotification;
use App\Models\User;
use App\Models\Etudiant;

class BlogController extends Controller
{
	use FileManagementTrait;

	public function index()
	{

		return BlogResource::collection(Blog::query()->orderByDesc('publication_date')->get());
		return view('admin.blogs.index')->with([
			'blogs' => Blog::query()->orderByDesc('publication_date')->get()
		]);
	}

	public function create(): View
	{
		return view('admin.blogs.create')->with(['blog' => new Blog()]);
	}

	public function store(BlogRequest $request)
	{
		$image = $this->storeFile($request, 'image', 'blogs');

		$blog = Blog::query()->create([
			...$request->validated(),
			'publication_date' => now(),
			'author_name' => auth()->user()->nom . ' ' . auth()->user()->prenom,
			...compact('image'),
		]);

		return new BlogResource($blog);
	}

	public function show(Blog $blog)
	{
		return new BlogResource($blog);
		return view('admin.blogs.show', compact('blog'));
	}

	public function edit(Blog $blog)
	{
		return view('admin.blogs.edit', compact('blog'));
	}

	public function update(BlogRequest $request, Blog $blog)
	{
		$image = $request->hasFile('image') ? $this->updateFile($request, 'image', 'blogs', $blog->getAttribute('image')) : $blog->getAttribute('image');
		$blog->update([
			...$request->validated(),
			...compact('image')
		]);
		return new BlogResource($blog);

		// return to_route('admin.blogs.index')->with(successMsg('Blog mis à jour avec succès'));
	}

	public function delete(Blog $blog)
	{
		$this->deleteFile($blog->getAttribute('image'));
		$blog->delete();
		return new BlogResource($blog);

		// return to_route('admin.blogs.index')->with(successMsg('Blog supprimé avec succès'));
	}

	// public function search(Request $request): View
	// {
	// 	return view('admin.blogs.index')->with([
	// 		'blogs' => Blog::query()
	// 			->orderBy('publication_date', $request->input('direction', 'desc'))
	// 			->get()
	// 	]);
	// }
public function publishedBlog(Blog $blog)
{
    $blog->update([
        'status' => $blog->status === 'published'
            ? 'draft'
            : 'published'
    ]);

    if ($blog->status === 'published') {
        Notification::send(User::all(), new PublicationNotification($blog));
        Notification::send(Etudiant::all(), new PublicationNotification($blog));
    }

    return new BlogResource($blog);
}

}
