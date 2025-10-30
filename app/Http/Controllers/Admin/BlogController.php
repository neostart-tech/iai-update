<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogRequest;
use App\Models\Blog;
use App\Traits\FileManagementTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
	use FileManagementTrait;

	public function index(): View
	{
		return view('admin.blogs.index')->with([
			'blogs' => Blog::query()->orderByDesc('publication_date')->get()
		]);
	}

	public function create(): View
	{
		return view('admin.blogs.create')->with(['blog' => new Blog()]);
	}

	public function store(BlogRequest $request): RedirectResponse
	{
		$image = $this->storeFile($request, 'image', 'blogs');
		Blog::query()->create([
			...$request->validated(),
			'publication_date' => now(),
			...compact('image'),
		]);
		return to_route('admin.blogs.index')->with(successMsg('Blog ajouté avec succès'));
	}

	public function show(Blog $blog): View
	{
		return view('admin.blogs.show', compact('blog'));
	}

	public function edit(Blog $blog): View
	{
		return view('admin.blogs.edit', compact('blog'));
	}

	public function update(BlogRequest $request, Blog $blog): RedirectResponse
	{
		$image = $request->hasFile('image') ? $this->updateFile($request, 'image', 'blogs', $blog->getAttribute('image')) : $blog->getAttribute('image');
		$blog->update([
			...$request->validated(),
			...compact('image')
		]);
		return to_route('admin.blogs.index')->with(successMsg('Blog mis à jour avec succès'));
	}

	public function delete(Blog $blog): RedirectResponse
	{
		$this->deleteFile($blog->getAttribute('image'));
		$blog->delete();
		return to_route('admin.blogs.index')->with(successMsg('Blog supprimé avec succès'));
	}

	public function search(Request $request): View
	{
		return view('admin.blogs.index')->with([
			'blogs' => Blog::query()
				->orderBy('publication_date', $request->input('direction', 'desc'))
				->get()
		]);
	}
}
