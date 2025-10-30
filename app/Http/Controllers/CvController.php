<?php

namespace App\Http\Controllers;

use App\Jobs\CreatePdfCvJob;
use App\Traits\FileManagementTrait;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CvController extends Controller
{
	use FileManagementTrait;

	public function show(): View
	{
		return view('cv.show')->with(['content' => request()->user()->cv]);
	}

	public function edit(): View
	{
		return view('cv.edit')->with(['content' => request()->user()->cv]);
	}

	public function update(Request $request): RedirectResponse
	{
		$request->validate([
			'content' => ['required']
		], [
			'content.required' => 'Le contenu du CV est obligatoire'
		]);

		request()->user()->update(['cv' => $request->input('content')]);
		successMsg("Votre CV a été mis à jour avec succès");
		return to_route('etudiants.cv.show');
	}

	public function destroy(): RedirectResponse
	{
		$this->deleteFile(($album = request()->user()->album)->cv);
		$album->update(['cv' => null]);
		successMsg("Votre CV a été supprimé avec succès");
		return back();
	}

	public function convertToPDF(): RedirectResponse
	{
		CreatePdfCvJob::dispatch(request()->user());
		successMsg("CV en cours de conversion. Rendez-vous dans 'Mes fichiers' pour le voir");
		return back();
	}

	public function download(): StreamedResponse
	{
		return $this->downloadFile(request()->user()->album->cv);
	}
}
