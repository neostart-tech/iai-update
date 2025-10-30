<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GradeRequest;
use App\Models\Grade;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GradeController extends Controller
{
	public function index(): View
	{
		return view('grades.index')->with([
			'grades' => Grade::all()
		]);
	}

	public function create(): View
	{
		return view('filieres.create')->with(['grade' => new Grade()]);
	}

	public function store(GradeRequest $request): RedirectResponse
	{
		Grade::create([
			...$request->all(),
			... injectAnneeScolaireId()
		]);

		return to_route('grades.index')->with(successMsg('Grade ajoutée avec succès.'));
	}

	public function show(Grade $grade): View
	{
		return view('grades.show', compact('grade'));
	}

	public function edit(Grade $grade): View
	{
		return view('grades.edit', compact('grade'));
	}

	public function update(GradeRequest $request, Grade $grade): RedirectResponse
	{
		$grade->update($request->all());
		return to_route('grades.index')->with(successMsg('Grade mis à jours avec succès.'));
	}

	public function destroy(Grade $grade): RedirectResponse
	{
		if ($grade->emploiDuTemps->isNotEmpty()) {
			return back()->with(cannotDeleteItemMessage('ce grade'));
		}

		return to_route('grades.index')->with(successMsg('Grade supprimé avec succès.'));
	}
}
