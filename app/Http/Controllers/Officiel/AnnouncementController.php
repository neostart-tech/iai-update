<?php

namespace App\Http\Controllers\Officiel;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
	public function index(): View
	{
	return view('pages.opportunities')->with([
			'announcements' => Announcement::query()->where('status')->get()
		]);
	}
}
