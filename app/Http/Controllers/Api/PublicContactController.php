<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Models\User;
use App\Notifications\NewContactMessageNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;

class PublicContactController extends Controller
{
    public function store(ContactRequest $request): JsonResponse
    {
        $contact = Contact::query()->create([
            ...$request->validated(),
            'status' => 0
        ]);

        // Trouver les utilisateurs ayant les rôles concernés
        $responsables = User::whereHas('roles', function ($q) {
            $q->whereIn('slug', [
                'responsable-marketing', 
                'responsable-du-site', 
                'collaborateur-commercial'
            ])->orWhereIn('nom', [
                'Responsable Marketing', 
                'Responsable du site', 
                'Collaborateur Commercial'
            ]);
        })->get();

        // Envoyer la notification (email + db) s'il y a des responsables
        if ($responsables->count() > 0) {
            Notification::send($responsables, new NewContactMessageNotification($contact));
        }

        return response()->json([
            'success' => true,
            'message' => 'Votre message a été enregistré avec succès.'
        ], 201);
    }
}
