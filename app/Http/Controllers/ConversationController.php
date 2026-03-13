<?php

namespace App\Http\Controllers;

use App\Events\NewConversationCreated;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{
    /**
     * Afficher la liste des conversations de l'utilisateur connecté
     */
    public function index()
    {
        try {
            $user = auth()->user();
            
            $conversations = Conversation::whereHas('participants', function ($query) use ($user) {
                $query->where('participant_id', $user->id)
                      ->where('participant_type',"App\Models\User")
                      ->orWhere('participant_type',"App\Models\Etudiant");
            })
            ->with(['participants', 'lastMessage'])
            ->orderBy('updated_at', 'desc')
            ->get();

            return response()->json($conversations);
        } catch (\Exception $e) {
            Log::error('Erreur conversations: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Afficher une conversation spécifique
     */
    public function show(Conversation $conversation)
    {
        try {
            $user = auth()->user();
            
            // Vérifier que l'utilisateur est participant
            $isParticipant = DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->where('participant_id', $user->id)
                ->where('participant_type',"App\Models\User")
                ->orWhere('participant_type',"App\Models\Etudiant")
                ->exists();

            if (!$isParticipant) {
                return response()->json(['error' => 'Non autorisé'], 403);
            }

            $conversation->load('participants');

            return response()->json($conversation);
        } catch (\Exception $e) {
            Log::error('Erreur affichage conversation: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Créer une nouvelle conversation (version optimisée)
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            $request->validate([
                'type' => 'required|in:private,group',
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:500',
                'admin_only' => 'sometimes|boolean',
                'participants' => 'required|array|min:1',
                'participants.*.id' => 'required|integer',
                'participants.*.type' => 'required|string|in:User,Etudiant,Enseignant'
            ]);

            $participantsCount = count($request->participants);
            Log::info("Création d'une conversation avec {$participantsCount} participants");

            DB::beginTransaction();

            // Créer la conversation
            $conversation = Conversation::create([
                'nom' => $request->name ?? null,
                'description' => $request->description ?? null,
                'type' => $request->type,
                'admin_only' => $request->admin_only ?? false,
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

            // Préparer les données des participants
            $participantsData = [];
            foreach ($request->participants as $participant) {
                $participantType = $typeMapping[$participant['type']] ?? 'App\\Models\\User';
                
                $participantsData[] = [
                    'conversation_id' => $conversation->id,
                    'participant_id' => $participant['id'],
                    'participant_type' => $participantType,
                    'role' => $participant['id'] === $user->id ? 'admin' : 'member',
                    'joined_at' => now(),
                ];
            }

            // Insertion en masse
            $chunks = array_chunk($participantsData, 500);
            foreach ($chunks as $chunk) {
                DB::table('conversation_users')->insert($chunk);
            }

            // Vérification du nombre de participants insérés
            $insertedCount = DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->count();

            Log::info("Conversation #{$conversation->id} créée avec {$insertedCount} participants");

            DB::commit();

            // Charger les participants
            $conversation->load('participants');

            // Diffuser l'ID de la conversation
            $this->broadcastConversationCreation($conversation, $request->participants, $user->id);

            return response()->json($conversation, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création conversation: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur lors de la création de la conversation',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une conversation (nom, description, mode admin only)
     */
    public function update(Request $request, Conversation $conversation)
    {
        try {
            $user = auth()->user();
            
            // Vérifier que l'utilisateur est admin de la conversation
            $isAdmin = DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->where('participant_id', $user->id)
                ->where('participant_type', get_class($user))
                ->where('role', 'admin')
                ->exists();

            if (!$isAdmin) {
                return response()->json(['error' => 'Non autorisé - Vous devez être admin'], 403);
            }

            $request->validate([
                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:500',
                'type' => 'sometimes|in:private,group',
                'admin_only' => 'sometimes|boolean'
            ]);

            $conversation->update([
                'nom' => $request->name ?? $conversation->nom,
                'description' => $request->description ?? $conversation->description,
                'type' => $request->type ?? $conversation->type,
                'admin_only' => $request->admin_only ?? $conversation->admin_only,
                'updated_at' => now()
            ]);

            $conversation->load('participants');

            return response()->json([
                'message' => 'Conversation mise à jour avec succès',
                'conversation' => $conversation
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour conversation: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ✅ SUPPRIMER UNE CONVERSATION ET TOUT SON CONTENU
     * Seul le créateur ou un admin peut supprimer la conversation
     */
    public function destroy(Conversation $conversation)
    {
        try {
            $user = auth()->user();
            
            // Vérifier que l'utilisateur est admin de la conversation
            $isAdmin = DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->where('participant_id', $user->id)
                ->where('participant_type', get_class($user))
                ->where('role', 'admin')
                ->exists();

            // Ou vérifier que c'est le créateur
            $isCreator = $conversation->created_by === $user->id;

            if (!$isAdmin && !$isCreator) {
                return response()->json([
                    'error' => 'Non autorisé - Seuls les administrateurs ou le créateur peuvent supprimer cette conversation'
                ], 403);
            }

            DB::beginTransaction();

            // 1. Récupérer tous les messages de la conversation
            $messages = Message::where('conversation_id', $conversation->id)->get();
            $messageIds = $messages->pluck('id')->toArray();

            // 2. Récupérer toutes les pièces jointes
            $attachments = MessageAttachment::whereIn('message_id', $messageIds)->get();

            // 3. Supprimer les fichiers physiquement du storage
            foreach ($attachments as $attachment) {
                if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                    Log::info("Fichier supprimé: " . $attachment->file_path);
                }
            }

            // 4. Supprimer les pièces jointes de la BDD
            MessageAttachment::whereIn('message_id', $messageIds)->delete();

            // 5. Supprimer les messages
            Message::where('conversation_id', $conversation->id)->delete();

            // 6. Supprimer les participants
            DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->delete();

            // 7. Enfin, supprimer la conversation
            $conversation->delete();

            DB::commit();

            Log::info("Conversation #{$conversation->id} supprimée par l'utilisateur #{$user->id} avec " . 
                     count($messages) . " messages et " . count($attachments) . " pièces jointes");

            return response()->json([
                'message' => 'Conversation supprimée avec succès',
                'stats' => [
                    'messages' => count($messages),
                    'attachments' => count($attachments)
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression conversation: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur lors de la suppression',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les participants d'une conversation
     */
    public function participants(Conversation $conversation)
    {
        try {
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

            // Récupérer les participants avec plus de détails
            $participants = DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->get()
                ->map(function ($participant) {
                    // Charger les informations selon le type
                    if ($participant->participant_type === 'App\\Models\\User') {
                        $user = DB::table('users')->find($participant->participant_id);
                        $participant->nom = $user->nom ?? '';
                        $participant->prenom = $user->prenom ?? '';
                        $participant->email = $user->email ?? '';
                        $participant->type_label = 'Utilisateur';
                    } elseif ($participant->participant_type === 'App\\Models\\Etudiant') {
                        $etudiant = DB::table('etudiants')->find($participant->participant_id);
                        $participant->nom = $etudiant->nom ?? '';
                        $participant->prenom = $etudiant->prenom ?? '';
                        $participant->email = $etudiant->email ?? '';
                        $participant->type_label = 'Étudiant';
                    }
                    return $participant;
                });

            return response()->json($participants);

        } catch (\Exception $e) {
            Log::error('Erreur récupération participants: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Ajouter un participant à une conversation
     */
    public function addParticipant(Request $request, Conversation $conversation)
    {
        try {
            $user = auth()->user();
            
            // Vérifier que l'utilisateur est admin de la conversation
            $isAdmin = DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->where('participant_id', $user->id)
                ->where('participant_type', get_class($user))
                ->where('role', 'admin')
                ->exists();

            if (!$isAdmin) {
                return response()->json(['error' => 'Non autorisé - Vous devez être admin'], 403);
            }

            $request->validate([
                'user_id' => 'required|integer',
                'user_type' => 'required|string|in:User,Etudiant,Enseignant'
            ]);

            // Mapping des types
            $typeMapping = [
                'User' => 'App\\Models\\User',
                'Etudiant' => 'App\\Models\\Etudiant',
                'Enseignant' => 'App\\Models\\User',
            ];

            $participantType = $typeMapping[$request->user_type] ?? 'App\\Models\\User';

            // Vérifier si le participant existe déjà
            $exists = DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->where('participant_id', $request->user_id)
                ->where('participant_type', $participantType)
                ->exists();

            if ($exists) {
                return response()->json(['error' => 'Ce participant est déjà dans la conversation'], 400);
            }

            DB::table('conversation_users')->insert([
                'conversation_id' => $conversation->id,
                'participant_id' => $request->user_id,
                'participant_type' => $participantType,
                'role' => 'member',
                'joined_at' => now(),
            ]);

            // Recharger les participants
            $conversation->load('participants');

            return response()->json([
                'message' => 'Participant ajouté avec succès',
                'conversation' => $conversation
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur ajout participant: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Retirer un participant d'une conversation
     */
    public function removeParticipant(Conversation $conversation, $userId)
    {
        try {
            $user = auth()->user();
            
            // Vérifier que l'utilisateur est admin de la conversation
            $isAdmin = DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->where('participant_id', $user->id)
                ->where('participant_type', get_class($user))
                ->where('role', 'admin')
                ->exists();

            if (!$isAdmin && $user->id != $userId) {
                return response()->json(['error' => 'Non autorisé'], 403);
            }

            // Empêcher de retirer le dernier admin
            $adminCount = DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->where('role', 'admin')
                ->count();

            $targetIsAdmin = DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->where('participant_id', $userId)
                ->where('role', 'admin')
                ->exists();

            if ($targetIsAdmin && $adminCount <= 1) {
                return response()->json(['error' => 'Impossible de retirer le dernier admin'], 400);
            }

            DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->where('participant_id', $userId)
                ->delete();

            // Recharger les participants
            $conversation->load('participants');

            return response()->json([
                'message' => 'Participant retiré avec succès',
                'conversation' => $conversation
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur retrait participant: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ========== FONCTIONNALITÉS ADMIN ==========
     */

    /**
     * Changer le rôle d'un participant (admin/membre)
     */
    public function changeParticipantRole(Request $request, Conversation $conversation, $userId)
    {
        try {
            $user = auth()->user();
            
            // Vérifier que l'utilisateur est admin de la conversation
            $isAdmin = DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->where('participant_id', $user->id)
                ->where('participant_type', get_class($user))
                ->where('role', 'admin')
                ->exists();

            if (!$isAdmin) {
                return response()->json(['error' => 'Non autorisé - Vous devez être admin'], 403);
            }

            $request->validate([
                'role' => 'required|in:admin,member'
            ]);

            // Empêcher de changer le rôle du dernier admin
            if ($request->role === 'member') {
                $adminCount = DB::table('conversation_users')
                    ->where('conversation_id', $conversation->id)
                    ->where('role', 'admin')
                    ->count();

                $targetIsAdmin = DB::table('conversation_users')
                    ->where('conversation_id', $conversation->id)
                    ->where('participant_id', $userId)
                    ->where('role', 'admin')
                    ->exists();

                if ($targetIsAdmin && $adminCount <= 1) {
                    return response()->json(['error' => 'Impossible de rétrograder le dernier admin'], 400);
                }
            }

            DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->where('participant_id', $userId)
                ->update(['role' => $request->role]);

            // Recharger les participants
            $conversation->load('participants');

            return response()->json([
                'message' => 'Rôle modifié avec succès',
                'conversation' => $conversation
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur changement rôle: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Activer/désactiver le mode "seuls les admins peuvent parler"
     */
    public function toggleAdminOnly(Request $request, Conversation $conversation)
    {
        try {
            $user = auth()->user();
            
            // Vérifier que l'utilisateur est admin de la conversation
            $isAdmin = DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->where('participant_id', $user->id)
                ->where('participant_type', get_class($user))
                ->where('role', 'admin')
                ->exists();

            if (!$isAdmin) {
                return response()->json(['error' => 'Non autorisé - Vous devez être admin'], 403);
            }

            $request->validate([
                'admin_only' => 'required|boolean'
            ]);

            $conversation->update([
                'admin_only' => $request->admin_only,
                'updated_at' => now()
            ]);

            return response()->json([
                'message' => $request->admin_only ? 'Mode admin-only activé' : 'Mode admin-only désactivé',
                'admin_only' => $conversation->admin_only
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur toggle admin only: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Vérifier si l'utilisateur courant peut envoyer un message
     */
    public function canSendMessage(Conversation $conversation)
    {
        try {
            $user = auth()->user();
            
            // Vérifier que l'utilisateur est participant
            $participant = DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->where('participant_id', $user->id)
                ->where('participant_type', get_class($user))
                ->first();

            if (!$participant) {
                return response()->json([
                    'can_send' => false,
                    'reason' => 'Vous n\'êtes pas membre de cette conversation'
                ], 403);
            }

            // Vérifier si le mode admin-only est activé
            if ($conversation->admin_only && $participant->role !== 'admin') {
                return response()->json([
                    'can_send' => false,
                    'reason' => 'Seuls les administrateurs peuvent envoyer des messages dans cette conversation'
                ], 403);
            }

            return response()->json([
                'can_send' => true,
                'role' => $participant->role
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur vérification envoi message: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Récupérer les statistiques d'une conversation
     */
    public function stats(Conversation $conversation)
    {
        try {
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

            $messageCount = Message::where('conversation_id', $conversation->id)->count();
            $attachmentCount = MessageAttachment::whereIn('message_id', 
                Message::where('conversation_id', $conversation->id)->pluck('id')
            )->count();
            $participantCount = DB::table('conversation_users')
                ->where('conversation_id', $conversation->id)
                ->count();

            return response()->json([
                'conversation_id' => $conversation->id,
                'message_count' => $messageCount,
                'attachment_count' => $attachmentCount,
                'participant_count' => $participantCount,
                'created_at' => $conversation->created_at,
                'last_activity' => $conversation->updated_at
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur stats conversation: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Méthode privée pour diffuser la création de conversation
     */
    private function broadcastConversationCreation($conversation, $participants, $creatorId)
    {
        $otherParticipants = collect($participants)
            ->filter(fn($p) => $p['id'] !== $creatorId)
            ->values();

        $totalCount = $otherParticipants->count();
        
        Log::info("Diffusion de la conversation #{$conversation->id} à {$totalCount} participants");

        // Stratégie selon le nombre de participants
        if ($totalCount <= 100) {
            // Petits groupes : diffusion directe
            foreach ($otherParticipants as $participant) {
                broadcast(new NewConversationCreated($conversation->id, $participant['id']))->toOthers();
            }
            Log::info("Diffusion directe pour {$totalCount} participants");
        } 
        elseif ($totalCount <= 500) {
            // Groupes moyens : diffusion par lots de 50
            $chunks = $otherParticipants->chunk(50);
            foreach ($chunks as $index => $chunk) {
                foreach ($chunk as $participant) {
                    broadcast(new NewConversationCreated($conversation->id, $participant['id']))->toOthers();
                }
                // Pause entre les lots pour éviter la surcharge
                if ($index < $chunks->count() - 1) {
                    usleep(500000); // 0.5 seconde
                }
            }
            Log::info("Diffusion par lots pour {$totalCount} participants");
        } 
        else {
            // Très grands groupes : pas de diffusion
            Log::info("Grand groupe de {$totalCount} participants - pas de diffusion pour éviter la surcharge");
        }
    }
}