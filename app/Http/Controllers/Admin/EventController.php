<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EvenementRequest;
use App\Http\Resources\EvenementResource;
use App\Models\Evenement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EventController extends Controller
{
	public function index()
	{

		return EvenementResource::collection(Evenement::query()
			->orderByDesc('created_at')
			->get());
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

	public function store(EvenementRequest $request)
	{
		$data = $request->validated();
		
		if ($request->hasFile('image')) {
			$data['image'] = $request->file('image')->store('events/images', 'public');
		}

		$event =	Evenement::create([
			...$data,
			...injectAnneeScolaireId()
		]);
		return new EvenementResource($event);
	}

	public function show(Evenement $event)
	{
		return new EvenementResource($event);
		// return view('admin.events.show', compact('event'));
	}

	public function edit(Evenement $event): View
	{
		return view('admin.events.edit', compact('event'));
	}

	public function update(EvenementRequest $request, Evenement $event)
	{
		$data = $request->validated();
		
		if ($request->hasFile('image')) {
			// Delete old image if exists
			if ($event->image) {
				Storage::disk('public')->delete($event->image);
			}
			$data['image'] = $request->file('image')->store('events/images', 'public');
		}

		$event->update($data);

		return new EvenementResource($event);
	}

	public function delete(Evenement $event)
	{
		$event->delete();
		return new EvenementResource($event);
		// return to_route('admin.events.index')->with(successMsg('Événement supprimé avec succès.'));
	}
}
