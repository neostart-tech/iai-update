<?php

use App\Http\Controllers\CandidatureAuth\{
	AuthenticatedSessionController,
	ConfirmablePasswordController,
	EmailVerificationNotificationController,
	EmailVerificationPromptController,
	NewPasswordController,
	PasswordController,
	PasswordResetLinkController,
	VerifyEmailController
};
use App\Http\Controllers\Officiel\MySpaceController;
use Illuminate\Support\Facades\Route;

Route::name('candidature.')->group(function () {

	Route::controller(MySpaceController::class)->middleware('auth:sanctum')->name('my-space.')->prefix('mon-dossier')->group(function () {

		Route::get('', 'show')->name('show');
		Route::put('mes-informations', 'updateProfil')->name('profil.update');
		Route::post('soumettre-rectification', 'submitRectification')->name('submit.rectification');
		Route::get('mes-fichiers', 'myFiles')->name('files');
		Route::post('mes-fichiers', 'uploadFile')->name('files.upload');
		Route::get('constitution', 'constitution')->name('constitution');
		Route::get('mes-payements', 'myPayements')->name('payments');
		
		// Notifications
		Route::get('notifications', 'notifications')->name('notifications');
		Route::post('notifications/{id}/lu', 'markNotificationAsRead')->name('notifications.read');
		Route::post('notifications/supprimer-masse', 'deleteNotifications')->name('notifications.delete');
	});

	// Route::middleware('guest:web_candidatures')->group(function () {
	Route::group([],function () {

		Route::get('me-connecter', [AuthenticatedSessionController::class, 'create'])->name('login');

		Route::post('me-connecter', [AuthenticatedSessionController::class, 'store']);

		Route::get('mot-de-passe-oublie', [PasswordResetLinkController::class, 'create'])->name('password.request');

		Route::post('mot-de-passe-oublie', [PasswordResetLinkController::class, 'store'])->name('password.email');

		Route::get('mot-de-passe-oublié/{token}', [NewPasswordController::class, 'create'])->name('password.reset');

		Route::post('mot-de-passe-oublié', [NewPasswordController::class, 'store'])->name('password.store');
	});

	Route::middleware('auth:sanctum')->group(function () {

		Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');

		Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

		Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');

		Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');

		Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

		Route::put('password', [PasswordController::class, 'update'])->name('password.update');

		Route::post('me-deconnecter', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
	});
});
