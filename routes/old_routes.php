<?php

use App\Http\Controllers\{BlogController, ContactController, EvenementController, GalleryPublicController};
use App\Models\{Announcement, Blog, Evenement, UrgentInfo};
use Illuminate\Support\Facades\Route;

// Route de la racine gérée dans routes/web.php (redirection vers /officiel).
// Ne pas la redéfinir ici : ce fichier est chargé après web.php et une
// redéfinition du même GET '/' écraserait silencieusement celle de web.php.

Route::get('officiel', fn() => view('welcome')
	->with(
		[
			'blogs' => Blog::query()
				->orderByDesc('publication_date')
				->limit(2)
				->get(),
			'events' => Evenement::query()->orderByDesc('id')->limit(4)->get(),
			// Bannière d'annonce sur l'accueil : la dernière information urgente publiée.
			'urgentInfo' => UrgentInfo::query()
				->where('is_published', true)
				->orderByDesc('published_at')
				->orderByDesc('created_at')
				->first(),
		]
	))
	->name('home');

Route::prefix('officiel')->group(function () {



	Route::view('a-propos', 'pages.about')->name('about');
	Route::view('formulaire', 'candidatures.create')->name('formulaire');
	Route::view('test-ecrit', 'pages.test')->name('test');
	Route::view('inscriptions', 'pages.inscription')->name('inscriptions');
	Route::view('dossiers', 'pages.dossier')->name('dossiers');
	Route::view('formations', 'pages.formation')->name('formations');

	Route::view('formations-alternances', 'pages.formation-alternance')->name('formations.alternance');
	Route::view('formations-modulaires/{tab?}', 'pages.formation-modulaire')
		->where('tab', 'alternance|certifiante|modulaire|continue')
		->name('formations.modulaire');
	Route::view('formations-certifiantes', 'pages.formation-certifiante')->name('formations.certifiante');
	Route::view('formations-continues', 'pages.formation-continue')->name('formations.continues');

	Route::view('admission', 'pages.admission')->name('admission');

	Route::controller(BlogController::class)->prefix('actualites')->name('blogs.')->group(function () {
		Route::get('', 'index')->name('index');
		Route::get('{blog}', 'show')->name('show');
		Route::post('{blog}/comment', 'storeComment')->name('comment');
	});

	Route::get('evenements/{evenement}', [EvenementController::class, 'show'])->name('officiel.events.show');

	Route::get('opportunités', function () {
		return view('pages.opportunities')->with([
			'announcements' => Announcement::query()->with('advertiser')->where('status', true)->get()
		]);
	})->name('opportunities');

	Route::get('opportunites/{announcement}', function (Announcement $announcement) {
		return view('pages.opportunities-detail')->with([
			'announcement' => $announcement
		]);
	})->name('opportunities.detail');

	Route::get('galerie', [GalleryPublicController::class, 'index'])->name('galerie');
	Route::get('galerie/albums/{album:slug}', [GalleryPublicController::class, 'show'])->name('galerie.album');

	Route::view('contact', 'pages.contact')->name('contact');

	// Anti-spam: limit contact form submissions to 3 per minute per IP
	Route::post('contact', [ContactController::class, 'store'])
		->middleware('throttle:3,1')
		->name('contact.store');

	Route::view('administration', 'pages.administration')->name('administration');

	Route::view('conditions-generales-d-utilisation', 'pages.cgu')->name('cgu');
});
