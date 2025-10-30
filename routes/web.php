<?php

use App\Http\Controllers\{
	CandidatureController,
	MyAccountController,
	MyCalendarController,
	MyDashboardController,
	NotificationReadingController
};
use App\Http\Controllers\Admin\{ProfileController};
use App\Http\Controllers\enseignantAuth\AuthentificationSessionController;
use App\Http\Controllers\EspaceProfesseurControleur;
use App\Http\Controllers\FraisScolariteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrgentInfoPublicController;

Route::get('', fn() => to_route('home'));

// Informations urgentes (page publique)
Route::get('/informations-urgentes', [UrgentInfoPublicController::class, 'index'])->name('urgent.info');

Route::get('dashboard', function () {
	return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
	Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
		Route::get('', 'edit')->name('edit');
		Route::patch('', 'update')->name('update');
		Route::delete('', 'destroy')->name('destroy');
	});

	Route::controller(NotificationReadingController::class)->prefix('notifications/reading')->name('notifications.reading.')->group(function () {
		Route::post('clean', 'clean')->name('clean');
		Route::post('read-all', 'readAll')->name('read-all');
	});
});

Route::middleware('auth')
	->prefix('administration')
	->name('admin.')
	->group(base_path('routes/admin_routes.php'));

Route::controller(CandidatureController::class)->prefix('candidatures')->name('candidatures.')->group(function () {
	Route::get('faire-mon-depot', 'create')->name('create');
	Route::post('faire-mon-depot', 'store')->name('store');
});

Route::middleware('auth:web,etudiants')->group(function () {
	Route::get('mon-emploi-du-temps', fn() => view('my-calendar'))->name('my-calendar');
	Route::get('load-calendar', MyCalendarController::class)->name('load-calendar');
	Route::get('mon-dashboard', MyDashboardController::class)->name('mon-dashboard');

	Route::controller(MyAccountController::class)->name('my-space.')->group(function () {
		Route::get('mon-compte', 'show')->name('my-account');
		Route::post('changer-mon-mot-de-passe', 'updatePassword')->name('update-password');
	});
});



// Routes de l'espace enseignant sont chargées via RouteServiceProvider (routes/professeur.php)










//Routes de la comptabilité

Route::controller(AuthentificationSessionController::class)->prefix('comptables')->name('auth.')->middleware('guest:comptables')->group(function () {

	Route::get('', "logincompta")->name('logincompta');
	Route::post('/se-connecter', "storecompta")->name('storecompta');
	Route::delete('/se-déconnecter', "destroycompta")->name('logoutcompta');

});


// Route::prefix('compta')->name('compta.')->middleware(['auth', 'comptables'])->group(function () {

// 	Route::controller(FraisScolariteController::class)->prefix('frais')->name('compta.frais.')->group(function () {
// 		Route::get('historique', 'historique')->name('historique');
// 		Route::get('payer', 'payer')->name('payer');
// 		Route::post('payer', 'store')->name('store');

// 		// Routes pour la création et la gestion des frais de scolarité
// 		Route::get('index', 'index')->name('index');
// 		Route::get('create', 'create')->name('create');
// 		Route::post('store', 'store')->name('store');
// 		Route::get('edit/{id}', 'edit')->name('edit');
// 		Route::put('update/{id}', 'update')->name('update');
// 		Route::delete('destroy/{id}', 'destroy')->name('destroy');



// 	});

// });



require __DIR__ . '/auth.php';

require __DIR__ . '/old_routes.php';

require __DIR__ . '/etudiant.php';

// Les routes de l'espace enseignant sont déjà chargées par le RouteServiceProvider


// Actualités (Evenement)
use App\Http\Controllers\EvenementController;
Route::get('/events/search', [EvenementController::class, 'search'])->name('events.search');
// Ensure direct /events/{evenement} resolves and takes precedence over the old '/officiel/evenements/{evenement}'
Route::get('/events/{evenement}', [EvenementController::class, 'show'])->name('events.show');
// Anti-spam: throttle event comments
Route::post('/events/{evenement}/comment', [EvenementController::class, 'comment'])
	->middleware('throttle:5,1')
	->name('events.comment');

require __DIR__ . '/comptable.php';

// Cahier de texte workflow
Route::middleware(['web'])->group(function() {
	Route::post('/professeur/cahier-texte/approuver', [\App\Http\Controllers\EspaceProfesseurControleur::class, 'approuverCahierTexte'])->name('prof.cahier.approuver');
	Route::post('/comite/cahier-texte', [\App\Http\Controllers\CommitteeCahierTexteController::class, 'store'])->name('comite.cahier.store');
	Route::post('/professeur/cahier-texte/incoherence', [\App\Http\Controllers\EspaceProfesseurControleur::class, 'marquerIncoherenceCahier'])->name('prof.cahier.incoherence');
});
