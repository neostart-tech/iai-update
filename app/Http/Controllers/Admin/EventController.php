<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EvenementRequest;
use App\Models\Evenement;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EventController extends Controller
{
	public function index(): View
	{
		return view('admin.events.index')->with([
			'events' => Evenement::query()
				->orderByDesc('created_at')
				->get()
				->each(function (Evenement $evenement) {
					$evenement->setAttribute('createdAt', \Carbon\Carbon::parse($evenement->getAttribute('created_at'))->translatedFormat('d F Y'));
					$evenement->setAttribute('_start_date', \Carbon\Carbon::parse($evenement->getAttribute('start_date'))->translatedFormat('d F Y'));
					$evenement->setAttribute('_end_date', $evenement->getAttribute('end_date') ? \Carbon\Carbon::parse($evenement->getAttribute('end_date'))->translatedFormat('d F Y') : null);
				})
		]);
	}

	public function create(): View
	{
		return view('admin.events.create')->with([
			'event' => new Evenement([
				'start_date' => today(),
			])
		]);
	}

	public function store(EvenementRequest $request): RedirectResponse
	{
		Evenement::create([
			...$request->validated(),
			...injectAnneeScolaireId()
		]);
		return to_route('admin.events.index')->with(successMsg('Événement créé avec succès.'));
	}

	public function show(Evenement $event): View
	{
		return view('admin.events.show', compact('event'));
	}

	public function edit(Evenement $event): View
	{
		return view('admin.events.edit', compact('event'));
	}

	public function update(EvenementRequest $request, Evenement $event): RedirectResponse
	{
		$event->update($request->validated());
		return to_route('admin.events.index')->with(successMsg('Événement mis à jour avec succès.'));
	}

	public function delete(Evenement $event): RedirectResponse
	{
		$event->delete();
		return to_route('admin.events.index')->with(successMsg('Événement supprimé avec succès.'));
	}
}
