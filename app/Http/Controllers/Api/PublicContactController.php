<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;

class PublicContactController extends Controller
{
    public function store(ContactRequest $request): JsonResponse
    {
        $contact = Contact::query()->create([
            ...$request->validated(),
            'status' => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Votre message a été enregistré avec succès.'
        ], 201);
    }
}
