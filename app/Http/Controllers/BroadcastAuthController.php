<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BroadcastAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            Log::error('❌ Auth broadcasting: utilisateur non authentifié');
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $channelName = $request->channel_name;
        $socketId = $request->socket_id;

        Log::info('🔑 Auth broadcasting', [
            'user_id' => $user->id,
            'channel' => $channelName,
            'socket_id' => $socketId
        ]);

        // Reverb envoie parfois "private-" ou "private-conversation." ou juste le nom
        // Nettoyer le nom du canal
        $cleanChannel = str_replace(['private-', 'private-conversation.'], '', $channelName);
        
        // Si c'est un nombre, c'est l'ID de conversation
        if (is_numeric($cleanChannel)) {
            $conversationId = $cleanChannel;
        } else {
            // Sinon essayer d'extraire avec regex
            preg_match('/(\d+)$/', $cleanChannel, $matches);
            $conversationId = $matches[1] ?? null;
        }

        if (!$conversationId) {
            Log::error('❌ Impossible d\'extraire l\'ID de conversation', [
                'channel' => $channelName,
                'clean' => $cleanChannel
            ]);
            return response()->json(['error' => 'Invalid channel format'], 403);
        }

        Log::info('🔍 Vérification participation', [
            'user_id' => $user->id,
            'conversation_id' => $conversationId
        ]);

        // Vérifier que l'utilisateur est participant
        $isParticipant = DB::table('conversation_users')
            ->where('conversation_id', $conversationId)
            ->where('participant_id', $user->id)
            ->where('participant_type', get_class($user))
            ->exists();

        if (!$isParticipant) {
            Log::warning('❌ Utilisateur non autorisé', [
                'user_id' => $user->id,
                'conversation_id' => $conversationId
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Générer la signature d'auth pour Reverb
        $string = $socketId . ':' . $channelName;
        $secret = env('REVERB_APP_SECRET', 'jvs3oiyffu8ps54px9ii');
        $signature = hash_hmac('sha256', $string, $secret);
        
        $key = env('REVERB_APP_KEY', 'oseylu5sd3axnur0phhu');
        $auth = $key . ':' . $signature;

        Log::info('✅ Auth réussie', [
            'auth' => $auth,
            'channel' => $channelName
        ]);

        return response()->json(['auth' => $auth]);
    }
}