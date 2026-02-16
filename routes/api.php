<?php

use App\Http\Controllers\Admin\ReclamationController;
use App\Http\Controllers\Api\Admin\CandidaturePresenceController;
use App\Http\Controllers\Api\SemoaCallBackController;
use App\Http\Controllers\MyCalendarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('load-calendar', MyCalendarController::class)->middleware('auth:sanctum');


Route::post('administration/candidature/presence', CandidaturePresenceController::class)->name('admin.candidatures.presence');
Route::controller(ReclamationController::class)->prefix('reclamations')->group(function () {
	Route::post('{note}/enregistrer-une-reclamation', 'store');
	Route::get('/mes-reclamations',  'mesReclamations');
	Route::delete('/reclamations/{reclamation}/annuler', 'annuler');
})->middleware('auth:sanctum');;
Route::any('semoa-callback-url',SemoaCallBackController::class);

require __DIR__ .'/api_admin_routes.php';

require __DIR__ .'/api_auth.php';

require __DIR__ .'/api_candidature.php';

require __DIR__ .'/api_comptable.php';

require __DIR__ .'/api_etudiant.php';

require __DIR__ .'/api_professeur.php';

