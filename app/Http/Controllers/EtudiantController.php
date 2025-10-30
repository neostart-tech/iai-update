<?php

namespace App\Http\Controllers;

use App\Jobs\ApplyStudentToAnnouncementJob;
use App\Models\Album;
use App\Models\Announcement;
use App\Models\AnnouncementEtudiant;
use App\Models\Etudiant;
use App\Models\Group;
use App\Models\Paiement;
use App\Models\Candidature;
use App\Models\AnneeScolaire;
use App\Notifications\Etudiants\{EtudiantGroupeUpdateNotification};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class EtudiantController extends Controller
{

	public function index()
	{
		//
	}

	public function create()
	{
		//
	}

	public function store(Request $request)
	{
		//
	}

	public function show(Etudiant $etudiant): View
	{
		return view('admin.etudiants.show', compact('etudiant'));
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Etudiant $etudiant)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Etudiant $etudiant)
	{
		//
	}

	public function myFiles(): View
	{
		
		$album=Album::query()->where('owner_id', auth()->user()->id)->first();
	
		return view('etudiants.my-space.files')->with([
			'album' => $album
		]);
	}

	public function constitution(): View
	{
		return view('etudiants.my-space.constitution')->with([
			'candidature' => request()->user()
		]);
	}


	public function changeGroup(Request $request, Etudiant $etudiant): RedirectResponse
	{
		$request->validate([
			'group_id' => ['required', 'exists:groups,slug']
		]);

		$etudiant->group()->first()->pivot->update([
			'group_id' => ($group =
				Group::query()->firstWhere('slug', $request->input('group_id'))
			)->getAttribute('id')
		]);
		successMsg("Décision appliquée avec succès");

		$message = $etudiant->greeting();
		$message .= ". Vous avez été affecté dans le groupe " . $group->getAttribute('nom') . ".";

		$etudiant->notify(new EtudiantGroupeUpdateNotification($message));
		return back();
	}

	public function myAccount(): View
	{
		return view('etudiants.my-space.my-account');
	}

	public function myPayment(){
		 $etudiant = auth('etudiants')->user();
        $paiements = Paiement::where('etudiant_id', $etudiant->id)
        ->with(['tranchePaiement.anneeScolaire'])
		->where('annule', false) 
        ->orderByDesc('date_paiement')
        ->get();

    $paiementsParAnnee = $paiements->groupBy(function($paiement) {
        return $paiement->tranchePaiement && $paiement->tranchePaiement->anneeScolaire
            ? $paiement->tranchePaiement->anneeScolaire->nom
            : 'Année inconnue';
    });

	$candidature=Candidature::where('etudiant_id',$etudiant->id)->latest()->first();

	$fraisScolairite=$candidature->niveau->fraisScolarites[0]->montant;
	$paiementTotal= Paiement::where('etudiant_id', $candidature->etudiant_id)
		->where('annule', false) 
        ->get()->sum('montant');

	$Ajour=false;

	if($fraisScolairite == $paiementTotal){
		$Ajour=true;
	}
	
	

   

    return view('etudiants.my-space.my-payment', compact('paiements','paiementsParAnnee', 'etudiant','Ajour'));
	}
}
