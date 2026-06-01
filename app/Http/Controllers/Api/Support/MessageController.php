<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Controller;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportMessage;
use App\Models\Support\SupportAttachment;
use App\Http\Resources\Support\MessageResource;
use App\Events\Support\NewSupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    /**
     * Liste des messages d'un ticket
     */
    public function index(SupportTicket $ticket)
    {
        try {
            $user = request()->user();
            
            // Vérifier si l'utilisateur a le droit de voir ce ticket
            if (!$ticket->canView($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à voir ces messages.'
                ], 403);
            }
            
            // Récupérer les messages avec leurs relations
            $messages = $ticket->messages()
                ->with(['user', 'attachments'])
                ->orderBy('created_at', 'asc')
                ->get();
            
            // Marquer les messages comme lus (pour les destinataires)
            if (!$user->isInformaticien()) {
                // L'utilisateur normal marque comme lus les messages des informaticiens
                $ticket->messages()
                    ->where('type', 'informaticien')
                    ->where('is_read', false)
                    ->update(['is_read' => true, 'read_at' => now()]);
            } else {
                // L'informaticien marque comme lus les messages des utilisateurs
                $ticket->messages()
                    ->where('type', 'user')
                    ->where('is_read', false)
                    ->update(['is_read' => true, 'read_at' => now()]);
            }
            
            return response()->json([
                'success' => true,
                'data' => MessageResource::collection($messages),
                'meta' => [
                    'total' => $messages->count(),
                    'unread_count' => $ticket->messages()->where('is_read', false)->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des messages.'
            ], 500);
        }
    }
    
    /**
     * Ajouter un message à un ticket
     */
    public function store(Request $request, SupportTicket $ticket)
    {
        try {
            $user = $request->user();
            
            // Vérifier si l'utilisateur a le droit de voir ce ticket
            if (!$ticket->canView($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à répondre à ce ticket.'
                ], 403);
            }
            
            // Vérifier si le ticket n'est pas fermé
            if ($ticket->status === 'closed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce ticket est fermé. Vous ne pouvez plus répondre.'
                ], 422);
            }
            
            // Validation des données
            $validated = $request->validate([
                'message' => 'required|string|min:1|max:5000',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
            ], [
                'message.required' => 'Le message est obligatoire.',
                'message.min' => 'Le message doit contenir au moins 1 caractère.',
                'message.max' => 'Le message ne peut pas dépasser 5000 caractères.',
                'attachments.*.mimes' => 'Le fichier doit être de type: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX.',
                'attachments.*.max' => 'Le fichier ne doit pas dépasser 10 Mo.',
            ]);
            
            DB::beginTransaction();
            
            try {
                // Créer le message
                $message = new SupportMessage();
                $message->ticket_id = $ticket->id;
                $message->user_id = $user->id;
                $message->message = $validated['message'];
                $message->type = $user->isInformaticien() ? 'informaticien' : 'user';
                $message->is_read = false;
                $message->save();
                
                // Gérer les pièces jointes
                $attachments = [];
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        // Générer un nom unique pour le fichier
                        $originalName = $file->getClientOriginalName();
                        $extension = $file->getClientOriginalExtension();
                        $filename = time() . '_' . uniqid() . '.' . $extension;
                        $path = $file->storeAs('support/attachments', $filename, 'public');
                        
                        $attachment = new SupportAttachment();
                        $attachment->message_id = $message->id;
                        $attachment->filename = $filename;
                        $attachment->original_name = $originalName;
                        $attachment->path = $path;
                        $attachment->mime_type = $file->getMimeType();
                        $attachment->size = $file->getSize();
                        $attachment->save();
                        
                        $attachments[] = $attachment;
                    }
                }
                
                // Mettre à jour le statut du ticket si nécessaire
                $oldStatus = $ticket->status;
                
                if ($ticket->status === 'open' && $user->isInformaticien()) {
                    // Si un informaticien répond, le ticket passe en cours
                    $ticket->status = 'in_progress';
                    $ticket->save();
                } elseif ($ticket->status === 'waiting') {
                    // Si quelqu'un répond, le ticket repasse en cours
                    $ticket->status = 'in_progress';
                    $ticket->save();
                }
                
                DB::commit();
                
                // Charger les relations pour la réponse
                $message->load(['user', 'attachments']);
                
                // Déclencher l'événement pour les notifications temps réel
                try {
                    event(new NewSupportMessage($ticket, $message));
                } catch (\Exception $e) {
                    Log::warning('Erreur lors de l\'envoi de l\'événement: ' . $e->getMessage());
                    // Ne pas bloquer la réponse si l'événement échoue
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Message envoyé avec succès.',
                    'data' => new MessageResource($message),
                    'meta' => [
                        'ticket_status' => $ticket->status,
                        'ticket_status_changed' => $oldStatus !== $ticket->status
                    ]
                ], 201);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur lors de la création du message: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi du message. Veuillez réessayer.'
                ], 500);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur inattendue: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer.'
            ], 500);
        }
    }
    
    /**
     * Supprimer un message
     */
    public function destroy(SupportMessage $message)
    {
        try {
            $user = request()->user();
            
            // Vérifier si l'utilisateur a le droit de supprimer ce message
            if ($message->user_id !== $user->id && !$user->isInformaticien()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à supprimer ce message.'
                ], 403);
            }
            
            // Ne pas permettre de supprimer le premier message du ticket
            $firstMessage = $message->ticket->messages()->oldest()->first();
            if ($firstMessage && $firstMessage->id === $message->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le message original du ticket ne peut pas être supprimé.'
                ], 422);
            }
            
            DB::beginTransaction();
            
            try {
                // Supprimer les pièces jointes
                foreach ($message->attachments as $attachment) {
                    if (Storage::disk('public')->exists($attachment->path)) {
                        Storage::disk('public')->delete($attachment->path);
                    }
                    $attachment->delete();
                }
                
                // Sauvegarder les infos pour le log
                $ticketId = $message->ticket_id;
                $messageId = $message->id;
                
                // Supprimer le message
                $message->delete();
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Message supprimé avec succès.',
                    'meta' => [
                        'ticket_id' => $ticketId,
                        'deleted_message_id' => $messageId
                    ]
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur lors de la suppression du message: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression du message.'
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur inattendue: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer.'
            ], 500);
        }
    }
    
    /**
     * Marquer un message comme lu
     */
    public function markAsRead(SupportMessage $message)
    {
        try {
            $user = request()->user();
            
            // Vérifier si l'utilisateur a le droit de voir ce message
            if (!$message->ticket->canView($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé.'
                ], 403);
            }
            
            // Marquer comme lu
            $message->is_read = true;
            $message->read_at = now();
            $message->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Message marqué comme lu.',
                'data' => [
                    'id' => $message->id,
                    'is_read' => true,
                    'read_at' => $message->read_at
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage du message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du marquage du message.'
            ], 500);
        }
    }
    
    /**
     * Marquer tous les messages d'un ticket comme lus
     */
    public function markAllAsRead(SupportTicket $ticket)
    {
        try {
            $user = request()->user();
            
            if (!$ticket->canView($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé.'
                ], 403);
            }
            
            // Marquer les messages non lus comme lus
            $count = $ticket->messages()
                ->where('is_read', false)
                ->where('user_id', '!=', $user->id)
                ->update(['is_read' => true, 'read_at' => now()]);
            
            return response()->json([
                'success' => true,
                'message' => "{$count} message(s) marqué(s) comme lu(s).",
                'data' => [
                    'marked_count' => $count
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage des messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du marquage des messages.'
            ], 500);
        }
    }
    
    /**
     * Récupérer les messages non lus pour l'utilisateur connecté
     */
    public function unread(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user->isInformaticien()) {
                // L'informaticien voit tous les messages non lus de tous les tickets
                $unreadMessages = SupportMessage::where('type', 'user')
                    ->where('is_read', false)
                    ->with(['ticket', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
                $grouped = $unreadMessages->groupBy('ticket_id')->map(function($messages, $ticketId) {
                    $ticket = $messages->first()->ticket;
                    return [
                        'ticket' => [
                            'id' => $ticket->id,
                            'reference' => $ticket->reference,
                            'title' => $ticket->title,
                        ],
                        'unread_count' => $messages->count(),
                        'last_message' => $messages->first()->message,
                        'last_message_at' => $messages->first()->created_at,
                    ];
                })->values();
                
                return response()->json([
                    'success' => true,
                    'data' => $grouped,
                    'total_unread' => $unreadMessages->count()
                ]);
            } else {
                // L'utilisateur normal voit les messages des informaticiens non lus
                $unreadMessages = SupportMessage::where('ticketable_type', get_class($user))
                    ->where('ticketable_id', $user->id)
                    ->where('type', 'informaticien')
                    ->where('is_read', false)
                    ->with(['ticket', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
                return response()->json([
                    'success' => true,
                    'data' => MessageResource::collection($unreadMessages),
                    'total_unread' => $unreadMessages->count()
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des messages non lus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des messages non lus.'
            ], 500);
        }
    }
}