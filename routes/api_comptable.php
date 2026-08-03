<?php

use App\Http\Controllers\enseignantAuth\AuthentificationSessionController;
use App\Http\Controllers\FraisScolariteController;
use App\Http\Controllers\TranchePaiementController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\ComptabiliteController;
use Illuminate\Support\Facades\Route;

// Route::controller(AuthentificationSessionController::class)->prefix('comptables')->name('auth.')->middleware('guest:comptables')->group(function () {
Route::controller(AuthentificationSessionController::class)->prefix('comptables')->name('auth.')->group(function () {

	Route::get('/login-comptable', "logincompta")->name('logincompta');
	Route::post('/se-connecter', "storecompta")->name('storecompta');
	Route::delete('/se-déconnecter', "destroycompta")->name('logoutcompta');
});



// Route::middleware(['auth'])->group(function () {
Route::middleware('auth:sanctum')->group(function(){
Route::group([],function () {

	Route::controller(ComptabiliteController::class)->group(function () {
		Route::get('dashboard-old',  'dashboard')->name('api.comptable.dashboard-old');
		Route::get('export/{year}/{format}',  'export')->whereIn('format', ['csv', 'xlsx'])->name('api.comptable.export');
	});

	// Nouvelles routes API pour la finance
	Route::prefix('finance')->group(function () {
		Route::get('/dashboard', [\App\Http\Controllers\Api\FinanceController::class, 'dashboard']);
		Route::get('/recouvrement', [\App\Http\Controllers\Api\FinanceController::class, 'suiviRecouvrement']);
		Route::get('/recouvrement/{slug}/detail', [\App\Http\Controllers\Api\FinanceController::class, 'detailRecouvrement']);
		Route::post('/recouvrement/{slug}/rappel', [\App\Http\Controllers\Api\FinanceController::class, 'envoyerRappel'])->middleware('can:send-rappel-recouvrement');
		Route::post('/recouvrement/{slug}/abandon-ui', [\App\Http\Controllers\Api\FinanceController::class, 'declarerAbandonUI'])->middleware('can:declare-abandon-etudiant');
		Route::get('/recouvrement-journalier', [\App\Http\Controllers\Api\FinanceController::class, 'recouvrementJournalier']);
		Route::get('/suivi-mensuel', [\App\Http\Controllers\Api\FinanceController::class, 'suiviMensuel']);
		Route::post('/{fraisEtudiantId}/abandon', [\App\Http\Controllers\Api\FinanceController::class, 'declarerAbandon'])->middleware('can:declare-abandon-etudiant');
		Route::get('/abandons', [\App\Http\Controllers\Api\FinanceController::class, 'listeAbandons']);
		Route::get('/export/{format}', [\App\Http\Controllers\Api\FinanceController::class, 'exportRecouvrement'])->whereIn('format', ['xlsx', 'csv']);

		// Gestion des dépenses
		Route::prefix('depenses')->group(function () {
			Route::get('/', [\App\Http\Controllers\Api\DepenseController::class, 'index']);
			Route::post('/', [\App\Http\Controllers\Api\DepenseController::class, 'store'])->middleware('can:create-depense');
			Route::delete('/{id}', [\App\Http\Controllers\Api\DepenseController::class, 'destroy'])->middleware('can:delete-depense');
			Route::get('/stats', [\App\Http\Controllers\Api\DepenseController::class, 'stats']);
		});
        
        Route::get('/diagnostic/anomalies', [\App\Http\Controllers\DiagnosticFinancierController::class, 'index']);
        Route::post('/diagnostic/changer-mode-formation', [\App\Http\Controllers\DiagnosticFinancierController::class, 'changerModeFormation']);
	});




	// Routes pour la création et la gestion des frais de scolarité
	Route::controller(FraisScolariteController::class)->prefix('frais')->name('frais.')->group(function () {
		Route::get('historique', 'historique')->name('historique');
		Route::get('payer', 'payer')->name('payer');
		Route::post('payer', 'store')->name('payer.store')->middleware('can:create-frais-scolarite');


		Route::get('index', 'index')->name('index');
		Route::get('/{id}/tranche', 'show')->name('show');
		Route::post('store', 'store')->name('store')->middleware('can:create-frais-scolarite');
		Route::post('duplicate', 'duplicate')->name('duplicate')->middleware('can:duplicate-frais-scolarite');
		Route::put('update/{id}', 'update')->name('update')->middleware('can:update-frais-scolarite');
		Route::delete('destroy/{id}', 'destroy')->name('destroy')->middleware('can:delete-frais-scolarite');
	});



	// Routes pour la création et la gestion des frais de scolarité

	// Routes pour la création et la gestion des tranches de paiement
	Route::controller(TranchePaiementController::class)->prefix('Tranche')->name('tranche.')->group(function () {
		Route::get('index', 'index')->name('index');
		Route::post('store', 'store')->name('store')->middleware('can:create-tranche-paiement');
		Route::put('update/{id}', 'update')->name('update')->middleware('can:update-tranche-paiement');
		Route::delete('destroy/{id}', 'destroy')->name('destroy')->middleware('can:delete-tranche-paiement');
	});
	// Routes pour la création et la gestion des tranches de paiement


	//Routes de paiement des frais de scolarités

	Route::controller(PaiementController::class)->prefix('paiement')->name('paiement.')->group(function () {
		Route::get('index', 'index')->name('index');
		Route::post('store', 'store')->name('store')->middleware('can:create-paiement');
		Route::get('/get-tranches/{etudiant}', 'getTranches')->name('tranches');
		Route::post('annuler', 'annuler')->name('annuler')->middleware('can:annuler-paiement');
		Route::put('valider/{paiement}', 'valider')->name('valider')->middleware('can:update-paiement');


		//Route lié aux informations des frais de scolarité par etudiant
		Route::get('/historique/{etudiant}/paiement', 'getInformationPaiementEtudiant')->name('liste');
		Route::get('/historique/paiement', 'getInformationPaiementEtudiants')->name('detail');
		//Route lié aux informations des frais de scolarité par etudiant





	});




	//Routes de paiement des frais de scolarités



});
});

