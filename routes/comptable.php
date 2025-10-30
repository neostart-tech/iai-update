<?php

use App\Http\Controllers\enseignantAuth\AuthentificationSessionController;
use App\Http\Controllers\FraisScolariteController;
use App\Http\Controllers\TranchePaiementController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\ComptabiliteController;


Route::controller(AuthentificationSessionController::class)->prefix('comptables')->name('auth.')->middleware('guest:comptables')->group(function () {
	Route::get('/login-comptable', "logincompta")->name('logincompta');
	Route::post('/se-connecter', "storecompta")->name('storecompta');
	Route::delete('/se-déconnecter', "destroycompta")->name('logoutcompta');
});



Route::middleware(['auth'])->group(function () {

	Route::controller(ComptabiliteController::class)->group(function () {
		
    Route::get('dashboard',  'dashboard')->name('dashboard');
    
    Route::get('export/{year}/{format}',  'export')->whereIn('format', ['csv','xlsx'])->name('export');


	});




// Routes pour la création et la gestion des frais de scolarité
	Route::controller(FraisScolariteController::class)->prefix('frais')->name('frais.')->group(function () {
		Route::get('historique', 'historique')->name('historique');
		Route::get('payer', 'payer')->name('payer');
		Route::post('payer', 'store')->name('store');

		
		Route::get('index', 'index')->name('index');
		Route::get('/{id}/tranche', 'show')->name('show');
		Route::post('store', 'store')->name('store');
		Route::put('update/{id}', 'update')->name('update');
		Route::delete('destroy/{id}', 'destroy')->name('destroy');

		
	});



	// Routes pour la création et la gestion des frais de scolarité

	// Routes pour la création et la gestion des tranches de paiement
	Route::controller(TranchePaiementController::class)->prefix('Tranche')->name('tranche.')->group(function () {	
		Route::get('index', 'index')->name('index');
		Route::post('store', 'store')->name('store');
		Route::put('update/{id}', 'update')->name('update');
		Route::delete('destroy/{id}', 'destroy')->name('destroy');
	});
	// Routes pour la création et la gestion des tranches de paiement


	//Routes de paiement des frais de scolarités

Route::controller(PaiementController::class)->prefix('paiement')->name('paiement.')->group(function (){
Route::get('index', 'index')->name('index');
		Route::post('store', 'store')->name('store');
		Route::get('/get-tranches/{etudiant}', 'getTranches')->name('tranches');
		Route::post('annuler', 'annuler')->name('annuler');
		Route::put('valider/{paiement}', 'valider')->name('valider');


		//Route lié aux informations des frais de scolarité par etudiant
				Route::get('/historique/{etudiant}/paiement', 'getInformationPaiementEtudiant')->name('liste');
			    Route::get('/historique/paiement', 'getInformationPaiementEtudiants')->name('detail');
//Route lié aux informations des frais de scolarité par etudiant



	

});




	//Routes de paiement des frais de scolarités

	

});