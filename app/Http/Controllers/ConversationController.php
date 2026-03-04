<?php

namespace App\Http\Controllers;

use App\Events\NewConversationCreated;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
   public function index()
    {
        try {
            $user = auth()->user();
            
            $conversations = Conversation::whereHas('participants', function ($query) use ($user) {
                $query->where('participant_id', $user->id)
                      ->where('participant_type', get_class($user));
            })
            ->with(['participants', 'lastMessage'])
            ->orderBy('updated_at', 'desc')
            ->get();

            return response()->json($conversations);
        } catch (\Exception $e) {
            // Log::error('Erreur conversations: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // public function show(Conversation $conversation)
    // {
    //     $user = auth()->user();
        
    //     // Vérifier l'accès
    //     $hasAccess = DB::table('conversation_users')
    //         ->where('conversation_id', $conversation->id)
    //         ->where('participant_id', $user->id)
    //         ->where('participant_type', get_class($user))
    //         ->exists();

    //     if (!$hasAccess) {
    //         return response()->json(['error' => 'Non autorisé'], 403);
    //     }

    //     return response()->json($conversation->load('participants'));
    // }
    public function show(Conversation $conversation)
    {
        try {
            $user = auth()->user();
            
            // Vérifier que l'utilisateur est participant
            $isParticipant = DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->where('participant_id', $user->id)
                ->where('participant_type', get_class($user))
                ->exists();

            if (!$isParticipant) {
                return response()->json(['error' => 'Non autorisé'], 403);
            }

            $conversation->load('participants');

            return response()->json($conversation);
        } catch (\Exception $e) {
            // Log::error('Erreur affichage conversation: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // public function store(Request $request)
    // {
    //     $user = auth()->user();
        
    //     $request->validate([
    //         'type' => 'required|in:private,group,channel',
    //         'name' => 'required_if:type,group,channel|string|nullable',
    //         'participants' => 'required|array|min:1',
    //         'participants.*.id' => 'required|integer',
    //         'participants.*.type' => 'required|string|in:User,Etudiant,Enseignant',
    //     ]);

    //     // Créer la conversation
    //     $conversation = Conversation::create([
    //         'nom' => $request->name,
    //         'type' => $request->type,
    //         'created_by' => $user->id,
    //     ]);

    //     // Ajouter le créateur comme admin
    //     DB::table('conversation_users')->insert([
    //         'conversation_id' => $conversation->id,
    //         'participant_id' => $user->id,
    //         'participant_type' => get_class($user),
    //         'role' => 'admin',
    //         'joined_at' => now(),
    //     ]);

    //     // Ajouter les autres participants
    //     foreach ($request->participants as $participant) {
    //         $type= $participant['type']==="Enseignant" || $participant['type']==="User" ? "User" :"Etudiant";
    //         $modelClass = "App\\Models\\" . $type;
            
    //         DB::table('conversation_users')->insert([
    //             'conversation_id' => $conversation->id,
    //             'participant_id' => $participant['id'],
    //             'participant_type' => $modelClass,
    //             'role' => 'member',
    //             'joined_at' => now(),
    //         ]);
    //     }

    //     return response()->json($conversation->load('participants'), 201);
    // }

     public function store(Request $request)
    {
        try {
            $user = auth()->user();

            $request->validate([
                'type' => 'required|in:private,group',
                'name' => 'nullable|string|max:255',
                'participants' => 'required|array|min:1',
                'participants.*.id' => 'required|integer',
                'participants.*.type' => 'required|string|in:User,Etudiant,Enseignant'
            ]);

            DB::beginTransaction();

            // Créer la conversation
            $conversation = Conversation::create([
                'nom' => $request->name ?? null,
                'type' => $request->type,
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Mapping des types
            $typeMapping = [
                'User' => 'App\\Models\\User',
                'Etudiant' => 'App\\Models\\Etudiant',
                'Enseignant' => 'App\\Models\\User',
            ];

            // Ajouter tous les participants
            foreach ($request->participants as $participant) {
                $participantType = $typeMapping[$participant['type']] ?? 'App\\Models\\User';
                
                DB::table('conversation_users')->insert([
                    'conversation_id' => $conversation->id,
                    'participant_id' => $participant['id'],
                    'participant_type' => $participantType,
                    'role' => $participant['id'] === $user->id ? 'admin' : 'member',
                    'joined_at' => now(),
                ]);
            }

            DB::commit();

            // Charger les participants
            $conversation->load('participants');

            // Diffuser la nouvelle conversation à tous les participants SAUF le créateur
            foreach ($request->participants as $participant) {
                if ($participant['id'] !== $user->id) {
                    broadcast(new NewConversationCreated($conversation, $participant['id']))->toOthers();
                }
            }

            return response()->json($conversation, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Erreur création conversation: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function participants(Conversation $conversation)
    {
        $user = auth()->user();
        
        // Vérifier l'accès
        $hasAccess = DB::table('conversation_users')
            ->where('conversation_id', $conversation->id)
            ->where('participant_id', $user->id)
            ->where('participant_type', get_class($user))
            ->exists();

        if (!$hasAccess) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $participants = DB::table('conversation_users')
            ->where('conversation_id', $conversation->id)
            ->get();

        return response()->json($participants);
    }
}