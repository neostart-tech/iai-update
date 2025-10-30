<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use Illuminate\Http\Request;
use Illuminate\View\View;


class EvenementController extends Controller
{
	/**
	 * Ajouter un commentaire à une actualité
	 */
	public function comment(Request $request, Evenement $evenement)
	{
		$request->validate([
			'content' => 'required|string|max:500',
		]);

		// Supposons qu'il existe un modèle Comment et une relation comments sur Evenement
		$payload = [
			'content' => $request->input('content'),
		];
		if (auth()->check()) {
			$payload['user_id'] = auth()->id();
		}

		$evenement->comments()->create($payload);

		return redirect()->route('events.show', $evenement->id)->with('success', 'Commentaire ajouté !');
	}

	/**
	 * Rechercher des actualités
	 */
	public function search(Request $request)
	{
		$q = $request->input('q');
		$results = Evenement::where('nom', 'like', "%$q%")
			->orWhere('details', 'like', "%$q%")
			->orderByDesc('publication_date')
			->take(10)
			->get();

		return view('pages.events.search', [
			'results' => $results,
			'q' => $q
		]);
	}
	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		//
	}

	public function show($id): View
    {
        $evenement = Evenement::query()->findOrFail($id);
        // Récupérer l'auteur (supposons qu'il y a une relation 'user')
        $auteur = $evenement->user ?? null;
		// Date de publication (toujours Carbon)
		$datePublication = $evenement->publication_date ? \Carbon\Carbon::parse($evenement->publication_date) : \Carbon\Carbon::parse($evenement->created_at);
		// Récupérer les commentaires (supposons relation 'comments')
		$commentaires = $evenement->comments ?? collect();
		$nombreCommentaires = $commentaires->count();
		// Actualités récentes (5 dernières)
		$recentEvents = Evenement::orderByDesc('created_at')->take(5)->get();

		        return view('pages.events.show')->with([
            'event' => $evenement,
            'auteur' => $auteur,
            'datePublication' => $datePublication,
            'commentaires' => $commentaires,
            'nombreCommentaires' => $nombreCommentaires,
            'recentEvents' => $recentEvents
        ]);
    }

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Evenement $evenement)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Evenement $evenement)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Evenement $evenement)
	{
		//
	}
}
