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

// Vérification publique des relevés de notes via QR Code
use App\Http\Controllers\PublicReleveVerificationController;
Route::controller(PublicReleveVerificationController::class)->prefix('verifier-releve')->name('public.releve.')->group(function () {
    Route::get('/', 'index')->name('verify');
    Route::post('/verification', 'verify')->name('verify.form');
    Route::get('/verification/{hash}', 'verify')->name('verify.hash');
    Route::post('/api/verification', 'verifyApi')->name('verify.api');
});

// Système de connexion unifiée
use App\Http\Controllers\Auth\UnifiedLoginController;
Route::controller(UnifiedLoginController::class)->prefix('connexion')->name('unified.')->group(function () {
    Route::get('/', 'showLoginForm')->name('login');
    Route::post('/', 'login')->name('login.post');
    Route::post('/logout', 'logout')->name('logout');
    Route::post('/check-user-type', 'checkUserType')->name('check-user-type');
});

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

// Routes DAF (Directeur des Affaires Financières)
Route::middleware(['auth', App\Http\Middleware\CheckDAFRole::class])->prefix('daf')->name('daf.')->group(function () {
	Route::controller(App\Http\Controllers\DAFController::class)->group(function () {
		Route::get('frais-genre', 'configureFraisGenre')->name('frais-genre.index');
		Route::post('frais-genre', 'storeFraisGenre')->name('frais-genre.store');
		Route::get('frais-genre/rapport', 'rapportFraisGenre')->name('frais-genre.rapport');
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
