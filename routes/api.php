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
