<?php

namespace App\Http\Controllers;

use App\Http\Resources\NoteResource;
use Illuminate\Database\Eloquent\Builder;

class NoteController extends Controller
{
	public function index()
	{

		return NoteResource::collection(request()
			->user()
			->notes()
			->whereRelation('evaluation', fn(Builder $builder) => $builder->whereNotNull('correction_submission_date'))
			->with(['evaluation.uniteValeur.uniteEnseignement'])->get());

		return view('notes.index')->with([
			'notes' => request()
				->user()
				->notes()
				->whereRelation('evaluation', fn(Builder $builder) => $builder->whereNotNull('correction_submission_date'))
				->with(['evaluation.uniteValeur.uniteEnseignement'])->get()
		]);
	}
}
