<?php

namespace App\Http\Controllers;

use App\Jobs\ApplyStudentToAnnouncementJob;
use App\Models\Announcement;
use App\Models\AnnouncementEtudiant;
use App\Traits\FileManagementTrait;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class AnnouncementController extends Controller
{
	use FileManagementTrait;

	public function index(): View
	{
		return view('announcements.index')->with([
			'announcements' => Announcement::query()
				->with('advertiser:id,nom,slug,ville')
				->where('status', true)
				->get()
		]);
	}

	public function myApplications(): View
	{
		return view('announcements.my-applications')->with([
			'announcements' => request()->user()->announcements
		]);
	}

	public function show(Announcement $announcement): View
	{
		return view('announcements.show', compact('announcement'))->with([
			'applied' => AnnouncementEtudiant::query()
				->where('announcement_id', $announcement->getAttribute('id'))
				->where('etudiant_id', request()->user()->id)
				->exists()
		]);
	}

	public function applyToAnnouncement(Request $request, Announcement $announcement): RedirectResponse
	{
		// Si l'étudiant a déjà postulé pour cette offre, on l'en empêche
		if ($announcement->announcementEtudiants()->where('etudiant_id', $request->user()->id)->get()->isNotEmpty()) {
			abort(403);
		}

		$request->validate([
			'announcement' => ['required', 'exists:' . Announcement::class . ',slug']
		], [
			'announcement.required' => 'L\'annonce est obligatoire',
			'announcement.exists' => 'L\'annonce choisie n\'est pas valide'
		]);

		$user = $request->user();

		$announcementEtudiant = AnnouncementEtudiant::query()->create([
			'etudiant_id' => $user->getAttribute('id'),
			'announcement_id' => $announcement->getAttribute('id')
		]);

		ApplyStudentToAnnouncementJob::dispatch($user, $announcement, $announcementEtudiant);
		successMsg("Dépôt de candidature en cours");
		return back();
	}
}
