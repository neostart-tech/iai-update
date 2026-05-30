<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class PublicNewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255'
        ]);

        $subscriber = NewsletterSubscriber::where('email', $request->email)->first();

        if ($subscriber) {
            if ($subscriber->status === 'unsubscribed') {
                $subscriber->update(['status' => 'active']);
                return response()->json([
                    'success' => true,
                    'message' => 'Heureux de vous revoir ! Vous êtes à nouveau abonné.'
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Cette adresse email est déjà inscrite.'
            ], 400);
        }

        NewsletterSubscriber::create([
            'email' => $request->email,
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Merci pour votre inscription à notre newsletter !'
        ]);
    }
}
