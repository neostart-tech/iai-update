<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProspectRequest;
use App\Models\Prospect;
use Illuminate\Http\JsonResponse;

class PublicProspectController extends Controller
{
    public function store(ProspectRequest $request): JsonResponse
    {
        Prospect::create([
            ...$request->validated(),
            'status' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Prospect enregistré avec succès.'
        ], 201);
    }
}
