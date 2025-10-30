<?php

namespace App\Http\Controllers\Admin;

use App\Enums\{TypeAnnonceEnum, TypeContratEnum};
use App\Http\Controllers\Controller;
use App\Http\Requests\AnnouncementRequest;
use App\Models\{Advertiser, Announcement};
use App\Models\AnnouncementEtudiant;
use App\Traits\FileManagementTrait;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\{RedirectResponse, Response};
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Illuminate\View\View;
use Throwable;

class AnnouncementController extends Controller
{
	use FileManagementTrait;

	const FOLDER_NAME = 'announcements';

	public function index(): View
	{
		return view('admin.announcements.index')->with([
			'announcements' => Announcement::query()
				->with('advertiser:id,nom,slug,ville')
				->orderByDesc('created_at')
				->get()
		]);
	}

	public function create(): View
	{
		return view('admin.announcements.create')->with([
			'announcement' => new Announcement,
			'advertisers' => Advertiser::all(),
			'typeAnnonces' => TypeAnnonceEnum::cases(),
			'typeContrats' => TypeContratEnum::cases(),
		]);
	}

	public function store(AnnouncementRequest $request): RedirectResponse
	{
		$file_path = null;
		if ($request->hasFile('file_path')) {
			$file_path = $this->storeFile($request, 'file_path', static::FOLDER_NAME, $request->str('title')->slug(language: 'fr'));
		}
		Announcement::query()->create([
			...$request->only([
				'advertiser_id',
				'type_contrat',
				'type_annonce',
				'title',
				'ville',
				'content',
				'duration'
			]),
			...injectAnneeScolaireId(),
			'status' => false,
			'file_path' => $file_path,
		]);

		successMsg('Opportunité ajoutée avec succès');
		return to_route('admin.announcements.index');
	}

	public function show(Announcement $announcement): View
	{
		return view('admin.announcements.show', compact('announcement'));
	}

	public function edit(Announcement $announcement): View
	{
		return view('admin.announcements.edit', compact('announcement'))->with([
			'advertisers' => Advertiser::all(),
			'typeAnnonces' => TypeAnnonceEnum::cases(),
			'typeContrats' => TypeContratEnum::cases(),
		]);
	}

	public function update(AnnouncementRequest $request, Announcement $announcement): RedirectResponse
	{
		$file_path = $request->hasFile('file_path') ?
			$this->updateFile($request, 'file_path', static::FOLDER_NAME, $announcement->getAttribute('file_path'))
			:
			$announcement->getAttribute('file_path');

		$announcement->update([
			...$request->only([
				'advertiser_id',
				'type_contrat',
				'type_annonce',
				'title',
				'ville',
				'content',
				'duration'
			]),
			'file_path' => $file_path,
		]);

		successMsg('Opportunité mise à jour  avec succès');
		return to_route('admin.announcements.index');
	}

	public function destroy(Request $request): RedirectResponse
	{
		
		$announcementId= (int) $request->annonceId;
		$announcement=AnnouncementEtudiant::query()->where('announcement_id',$announcementId)->get();
		if ($announcement->isNotEmpty()) {
			warningMsg('Impossible de supprimer cette annonce, d\'autres informations dépendent de son existence');
			return back();
		}
		Announcement::where('id',$announcementId)->first()->delete();
		successMsg('Opportunité supprimé avec succès');

		return to_route('admin.announcements.index');
	}

	public function publish(string $slug): Application|Response|ResponseFactory
	{
		$announcement = Announcement::query()->firstWhere('slug', $slug);

		if (!$announcement)
			return __404();

		try {
			$announcement->update(['status' => ($status = !$announcement->getAttribute('status'))]);
		} catch (Throwable $exception) {
			return __500($exception->getMessage());
		}

		return __200('Status mis à jour avec succès', compact('status'));
	}

	public function etudiants(Announcement $announcement): View
	{
		return view('admin.etudiants.postulants', compact('announcement'))->with([
			'etudiants' => $announcement->etudiants
		]);
	}
}
