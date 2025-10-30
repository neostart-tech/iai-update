<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class NoteController extends Controller
{
	public function index(): View
	{

		return view('notes.index')->with([
			'notes' => request()
				->user()
				->notes()
				->whereRelation('evaluation', fn(Builder $builder) => $builder->whereNotNull('correction_submission_date'))
				->with(['evaluation.uniteValeur.uniteEnseignement'])->get()
		]);
	}
}
