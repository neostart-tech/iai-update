<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\EtudiantAuth\{AuthenticatedSessionController,
	EmailVerificationNotificationController,
	EmailVerificationPromptController,
	NewPasswordController,
	PasswordController,
	PasswordResetLinkController,
	VerifyEmailController
};
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\ReclamationController;
use App\Http\Controllers\ReleveController;
use App\Http\Controllers\SemoaCallBackController;
use Illuminate\Support\Facades\Route;

Route::prefix('espace-etudiant')->name('etudiants.')->group(function () {
	Route::middleware('guest:etudiants')->controller(AuthenticatedSessionController::class)->name('auth.')->prefix('me-connecter')->group(function () {
		Route::get('', 'create')->name('login');
		Route::post('', 'store')->name('store');
	});

	Route::middleware('auth:etudiants')->group(function () {
		Route::view('', 'etudiants.dashboard')->name('dashboard');
		Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('auth.logout');

		Route::controller(EtudiantController::class)->group(function () {
			Route::name('my-space.')->group(function () {
				Route::get('mon-dossier', 'constitution')->name('constitution');
				Route::get('mes-fichiers', 'myFiles')->name('my-files');
				Route::get('mon-compte', 'myAccount')->name('my-account');
				Route::get('mes-payements', 'myPayment')->name('my-payment');

			});
		});

		Route::controller(AnnouncementController::class)->prefix('annonces')->name('announcements.')->group(function () {
			Route::get('liste', 'index')->name('index');
			Route::get('{announcement}/details', 'show')->name('show');
			Route::post('{announcement}/postuler-a-une-offre', 'applyToAnnouncement')->name('apply-to-announcement');
			Route::get('mes-depots', 'myApplications')->name('my-applications');
		});

		Route::controller(CvController::class)->prefix('mon-cv')->name('cv.')->group(function () {
			Route::get('apercu', 'show')->name('show');
			Route::get('concevoir', 'edit')->name('edit');
			Route::put('enregistrer', 'update')->name('update');
			Route::delete('supprimer', 'destroy')->name('delete');
			Route::get('convertir-en-pdf', 'convertToPDF')->name('convert');
			Route::get('download', 'download')->name('download');
		});

		Route::middleware('paiement.ajour')->group(function(){
         Route::controller(NoteController::class)->prefix('mes-notes')->name('notes.')->group(function () {
			Route::get('/', 'index')->name('index');
		});
		Route::controller(ReleveController::class)->prefix('mes-releves')->name('releves.')->group(function () {
			Route::get('/view', 'showViewReleveForAuthStudent')->name('auth.view');
			Route::get('', 'showReleveForAuthStudent')->name('auth.index');
		});
				Route::controller(ReclamationController::class)->prefix('reclamations')->name('reclamations.')->group(function () {
					// Anti-spam: throttle to 3 reclamations per minute per student
					Route::post('{note}', 'store')->middleware('throttle:3,1')->name('store');
			// Route::get('', 'showReleveForAuthStudent')->name('auth.index');
		});

		});
		
		

		Route::prefix('semoa')->name('semoa.')->group(function () {
		
		Route::post('auth', [SemoaCallBackController::class, 'authentification'])->name('auth');
		Route::post('ping', [SemoaCallBackController::class, 'ping'])->name('ping');
		
	
		
		Route::get('orders', [SemoaCallBackController::class, 'orderList'])->name('orders.list');
		Route::get('orders/{reference}', [SemoaCallBackController::class, 'getOrder'])->name('orders.show');
	
		
		Route::get('form', [SemoaCallBackController::class, 'showPaymentForm'])->name('form');
		Route::post('payments/process', [SemoaCallBackController::class, 'processPayment'])->name('payments.process');
		Route::get('payment-status/{reference}', [SemoaCallBackController::class, 'paymentStatus'])->name('payment-status');
	});
		
	});

	Route::middleware('guest:etudiants')->group(function () {
		Route::get('mot-de-passe-oublie', [PasswordResetLinkController::class, 'create'])
			->name('password.request');

		Route::post('mot-de-passe-oublie', [PasswordResetLinkController::class, 'store'])
			->name('password.email');

		Route::get('reinitialiser-mon-mot-de-passe/{token}', [NewPasswordController::class, 'create'])
			->name('password.reset');

		Route::post('reinitialiser-mon-mot-de-passe', [NewPasswordController::class, 'store'])
			->name('password.store');
	});

	Route::middleware('auth:etudiants')->group(function () {
		Route::get('verify-email', EmailVerificationPromptController::class)
			->name('verification.notice');

		Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
			->middleware(['signed', 'throttle:6,1'])
			->name('verification.verify');

		Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
			->middleware('throttle:6,1')
			->name('verification.send');

		Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
			->name('password.confirm');

		Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

		Route::put('password', [PasswordController::class, 'update'])->name('password.update');
	});

});
