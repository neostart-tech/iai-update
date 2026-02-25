<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ReclamationController;
use App\Http\Controllers\Api\Admin\CandidaturePresenceController;
use App\Http\Controllers\Api\SemoaCallBackController;
use App\Http\Controllers\BourseController;
use App\Http\Controllers\MyCalendarController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PlanPaiementController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\PresenceExportController;
use App\Http\Controllers\TranchePaiementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('load-calendar', MyCalendarController::class)->middleware('auth:sanctum');


Route::post('administration/candidature/presence', CandidaturePresenceController::class)->name('admin.candidatures.presence');

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(ReclamationController::class)->prefix('reclamations')->group(function () {
        Route::post('{note}/enregistrer-une-reclamation', 'store');
        Route::get('{note}/detail-une-reclamation', 'getNoteReclation');
        Route::get('/mes-reclamations',  'mesReclamations');
        Route::delete('{reclamation}/annuler', 'annuler');
        Route::get('{reclamation}', 'show');
        Route::get('/get-count-reclamations', 'getCountReclamations');
    });

    Route::controller(NoteController::class)->prefix('mes-notes')->name('notes.')->group(function () {
        Route::get('/', 'index')->name('index');
    });

    Route::controller(AnnouncementController::class)->prefix('etudiant/annonces')->name('announcements.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::get('{announcement}/details', 'show')->name('show');
        Route::post('{announcement}/postuler-a-une-offre', 'applyToAnnouncement')->name('apply-to-announcement');
        Route::get('mes-depots', 'myApplications')->name('my-applications');
    });

    Route::controller(PresenceController::class)->prefix('presence')->group(function () {
        Route::get('/get-my-course', 'mesCours');
        Route::post('/save-student-presence', 'enregistrerAbsences');
    });

    Route::get('/presences/export/{emploiDuTempsId}', [PresenceExportController::class, 'exportByCours']);
    Route::post('/presences/export/filtered', [PresenceExportController::class, 'exportWithFilters']);


    Route::controller(BourseController::class)->prefix('bourse')->group(function () {
        Route::get('/liste', 'index')->name('bourse.index');
        Route::post('/store', 'store')->name('bourse.store');
        Route::get('/{bourse}', 'show')->name('bourse.show');
        Route::get('/{bourse}/etudiants', 'getEtudiantsBourse')->name('bourse.etudiants');
        Route::put('/{bourse}/update', 'update')->name('bourse.update');
        Route::delete('/{bourse}/delete', 'destroy')->name('bourse.delete');
        Route::post('/affecter', 'affecter')->name('bourse.affecter');
        Route::post('/retirer', 'retirer')->name('bourse.retirer');
    });

    Route::controller(PlanPaiementController::class)->prefix('plan-de-paiement')->group(function () {
        Route::get('/liste', 'index');
        Route::post('/store', 'store');
        Route::get('/{plan}', 'show');
        // Route::get('/{plan}/etudiants', 'getEtudiantsBourse')->name('bourse.etudiants');
        Route::put('/{plan}/update', 'update');
        Route::delete('/{plan}/delete', 'destroy');
        // Route::post('/affecter', 'affecter')->name('bourse.affecter');
        // Route::post('/retirer', 'retirer')->name('bourse.retirer');
    });

     Route::controller(TranchePaiementController::class)->prefix('tranche-de-paiement')->group(function () {
        Route::get('/liste', 'index');
        Route::post('/store', 'store');
        Route::get('/{frais}', 'show');
        // Route::get('/{plan}/etudiants', 'getEtudiantsBourse')->name('bourse.etudiants');
        Route::put('/{tranche}/update', 'update');
        Route::delete('/{tranche}/delete', 'destroy');
        // Route::post('/affecter', 'affecter')->name('bourse.affecter');
        // Route::post('/retirer', 'retirer')->name('bourse.retirer');
    });
});



Route::any('semoa-callback-url', SemoaCallBackController::class);

require __DIR__ . '/api_admin_routes.php';

require __DIR__ . '/api_auth.php';

require __DIR__ . '/api_candidature.php';

require __DIR__ . '/api_comptable.php';

require __DIR__ . '/api_etudiant.php';

require __DIR__ . '/api_professeur.php';
