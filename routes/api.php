<?php

use App\Http\Controllers\Api\Admin\CandidaturePresenceController;
use App\Http\Controllers\Api\SemoaCallBackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('administration/candidature/presence', CandidaturePresenceController::class)->name('admin.candidatures.presence');

Route::any('semoa-callback-url',SemoaCallBackController::class);

require __DIR__ .'/api_admin_routes.php';

require __DIR__ .'/api_auth.php';

require __DIR__ .'/api_candidature.php';

require __DIR__ .'/api_comptable.php';

require __DIR__ .'/api_etudiant.php';

require __DIR__ .'/api_professeur.php';

