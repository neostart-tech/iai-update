<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnnonceResource;
use App\Jobs\ApplyStudentToAnnouncementJob;
use App\Models\Announcement;
use App\Models\AnnouncementEtudiant;
use App\Traits\FileManagementTrait;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class AnnouncementController extends Controller
{
	use FileManagementTrait;

	public function index()
	{

		return AnnonceResource::collection(Announcement::query()
			->with('advertiser:id,nom,slug,ville')
			->where('status', true)
			->get());


		return view('announcements.index')->with([
			'announcements' => Announcement::query()
				->with('advertiser:id,nom,slug,ville')
				->where('status', true)
				->get()
		]);
	}

	public function myApplications()
	{

		return new AnnonceResource(request()->user()->announcements);
		// return view('announcements.my-applications')->with([
		// 	'announcements' => request()->user()->announcements
		// ]);
	}

	public function show(Announcement $announcement)
	{

		return new AnnonceResource($announcement->load(['advertiser:id,nom,slug,ville','announcementEtudiants' => function($query){
			$query->where('etudiant_id', request()->user()->id);
		}]));
		return view('announcements.show', compact('announcement'))->with([
			'applied' => AnnouncementEtudiant::query()
				->where('announcement_id', $announcement->getAttribute('id'))
				->where('etudiant_id', request()->user()->id)
				->exists()
		]);
	}

	// public function applyToAnnouncement(Request $request, Announcement $announcement)
	// {
	// 	// Si l'étudiant a déjà postulé pour cette offre, on l'en empêche
	// 	if ($announcement->announcementEtudiants()->where('etudiant_id', $request->user()->id)->get()->isNotEmpty()) {
	// 		abort(403);
	// 	}

	// 	$request->validate([
	// 		'announcement' => ['required', 'exists:' . Announcement::class . ',slug']
	// 	], [
	// 		'announcement.required' => 'L\'annonce est obligatoire',
	// 		'announcement.exists' => 'L\'annonce choisie n\'est pas valide'
	// 	]);

	// 	$user = $request->user();

	// 	$announcementEtudiant = AnnouncementEtudiant::query()->create([
	// 		'etudiant_id' => $user->getAttribute('id'),
	// 		'announcement_id' => $announcement->getAttribute('id')
	// 	]);

	// 	ApplyStudentToAnnouncementJob::dispatch($user, $announcement, $announcementEtudiant);
	// 	return new AnnonceResource($announcement);
	// 	// successMsg("Dépôt de candidature en cours");
	// 	// return back();
	// }

	public function applyToAnnouncement(Request $request, Announcement $announcement)
	{

		// Vérifier si l'étudiant a déjà postulé
		if ($announcement->announcementEtudiants()->where('etudiant_id', $request->user()->id)->exists()) {
			abort(403, 'Vous avez déjà postulé à cette offre');
		}


		// Validation des fichiers
		try {
			$request->validate([
				'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
				'lettre_motivation' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
			]);
		} catch (\Exception $e) {
			throw $e;
		}

		$user = $request->user();

		// Upload du CV
		$cvPath = null;
		if ($request->hasFile('cv')) {
			$cvPath = $request->file('cv')->store("candidatures/{$user->id}/cv", 'public');
		}

		// Upload de la lettre de motivation
		$lettrePath = null;
		if ($request->hasFile('lettre_motivation')) {
			$lettrePath = $request->file('lettre_motivation')->store("candidatures/{$user->id}/lettres", 'public');
		}

		// Créer l'enregistrement
		$announcementEtudiant = AnnouncementEtudiant::query()->create([
			'etudiant_id' => $user->getAttribute('id'),
			'announcement_id' => $announcement->getAttribute('id'),
			'cv_path' => $cvPath,
			'lettre_path' => $lettrePath,
			'applied' => false
		]);


		// Lancer le job avec les chemins des fichiers uploadés
		ApplyStudentToAnnouncementJob::dispatch(
			$user,
			$announcement,
			$announcementEtudiant,
			$cvPath,      // Passage du CV uploadé
			$lettrePath    // Passage de la lettre uploadée
		);


		return new AnnonceResource($announcement);
	}
}
