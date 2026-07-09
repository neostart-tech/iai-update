<?php

use App\Http\Controllers\{
	CandidatureController,
	CommitteeCahierTexteController,
	DocumentationController,
	EvenementController,
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

// Route::get('', fn() => to_route('home'));
// Route::get('', fn() =>  redirect()->intended(to_route('login')));
Route::get('/', fn() => redirect('/officiel'));


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
	// La route racine '/' est gérée une seule fois, en haut de ce fichier
	// (redirection vers /officiel). La redéfinir ici l'écrasait silencieusement
	// (même méthode + même URI => la dernière déclaration gagne), ce qui causait
	// en plus une erreur "Header may not contain..." quand un utilisateur déjà
	// connecté déclenchait redirect()->intended() sur une session corrompue.

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
	Route::get('dossier-depose', 'merci')->name('merci');
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

Route::controller(DocumentationController::class)->prefix('documentation')->name('documentation.')->group(function () {
	Route::get('liste', 'index')->name('liste');
	Route::post('store', 'store')->name('store');
	Route::put('{document}/edit', 'edit')->name('edit');
	Route::delete('{document}/delete', 'delete')->name('delete');
	Route::get('{document}/download', 'download')->name('download');
});

Route::controller(DocumentationController::class)
	->prefix('documentation')
	->name('documentation.')
	->group(function () {
		Route::get('mes-documents', 'userIndex')->name('mes-documents');
		Route::get('{document}/download', 'download')->name('download');
	});



// Routes de l'espace enseignant sont chargées via RouteServiceProvider (routes/professeur.php)










//Routes de la comptabilité

Route::controller(AuthentificationSessionController::class)->prefix('comptables')->name('auth.')->middleware('guest:comptables')->group(function () {

	Route::get('', "logincompta")->name('logincompta');
	Route::post('/se-connecter', "storecompta")->name('storecompta');
	Route::delete('/se-déconnecter', "destroycompta")->name('logoutcompta');
});


Route::get('/events/search', [EvenementController::class, 'search'])->name('events.search');
Route::get('/events/{evenement}', [EvenementController::class, 'show'])->name('events.show');
Route::post('/events/{evenement}/comment', [EvenementController::class, 'comment'])
	->middleware('throttle:5,1')
	->name('events.comment');

Route::middleware(['web'])->group(function () {
	Route::post('/professeur/cahier-texte/approuver', [EspaceProfesseurControleur::class, 'approuverCahierTexte'])->name('prof.cahier.approuver');
	Route::post('/comite/cahier-texte', [CommitteeCahierTexteController::class, 'store'])->name('comite.cahier.store');
	Route::post('/professeur/cahier-texte/incoherence', [EspaceProfesseurControleur::class, 'marquerIncoherenceCahier'])->name('prof.cahier.incoherence');
});





require __DIR__ . '/auth.php';

require __DIR__ . '/old_routes.php';

require __DIR__ . '/etudiant.php';


require __DIR__ . '/comptable.php';
