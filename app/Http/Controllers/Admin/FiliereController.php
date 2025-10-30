<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FiliereRequest;
use App\Models\{AnneeScolaire, Filiere};
use App\Models\Grade;
use App\Traits\FileManagementTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class FiliereController extends Controller
{
	use FileManagementTrait;

	 public function __construct()
	 {
	 	$this->authorizeResource(Filiere::class, 'filiere');
	 }

	private const FOLDER = 'filieres';

	public function index(): View
	{
		return view('admin.filieres.index')->with([
			'filieres' => Filiere::query()->withCount('etudiants')->get()->reverse()
		]);
	}

	public function create(): View
	{
		return view('admin.filieres.create')->with([
			'filiere' => new Filiere(),
			'annee_scolaires' => AnneeScolaire::all()
		]);
	}

	public function store(FiliereRequest $request): RedirectResponse
	{
		$filePath = $request->hasFile('image') ? $this->storeFile($request, 'image', static::FOLDER) : config('images.filieres.default');

		$request->merge(['image' => $filePath]);
		Filiere::create($request->all());

		return to_route('admin.filieres.index')->with(successMsg('Filière ajoutée avec succès.'));
	}

	public function show(Filiere $filiere): View
	{
		return view('admin.filieres.show', compact('filiere'));
	}

	public function edit(Filiere $filiere): View
	{
		return view('admin.filieres.edit', compact('filiere'))->with([
			'annee_scolaires' => AnneeScolaire::all()
		]);
	}

	public function update(FiliereRequest $request, Filiere $filiere): RedirectResponse
	{
		$filePath = $request->hasFile('image') ? $this->updateFile($request, 'image', static::FOLDER, $filiere->getAttribute('image')) : $filiere->getAttribute('image');

		$filiere->update([
			... $request->all(),
			'image' => $filePath
		]);

		return to_route('admin.filieres.index')->with(successMsg('Filière mise à jour avec succès.'));
	}

	public function destroy(Request $request): RedirectResponse
	{
		$filiere=$request->idfil;

		 $grades=Grade::query()->where('filiere_id',$filiere)->get();
		
		if ($grades->isNotEmpty()) {
			return back()->with(cannotDeleteItemMessage('cette filière'));
		}
		$fil=Filiere::query()->where('id',$filiere)->first();
		$this->deleteFile($fil->getAttribute('image'));
		$fil->delete();
		$fil->delete();
		return to_route('admin.filieres.index')->with(successMsg('Filière supprimée avec succès.'));
	}
}
