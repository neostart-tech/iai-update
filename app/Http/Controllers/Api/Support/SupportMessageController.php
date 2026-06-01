<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Controller;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportMessage;
use App\Models\Support\SupportAttachment;
use App\Http\Resources\Support\MessageResource;
use App\Events\Support\NewSupportMessage;
use App\Events\Support\SupportMessageUpdated;
use App\Events\Support\SupportMessageDeleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SupportMessageController extends Controller
{
    /**
     * Liste des messages d'un ticket
     */
    public function index(SupportTicket $ticket)
    {
        $messages = $ticket->messages()->with(['user', 'attachments'])->get();
        return MessageResource::collection($messages);
    }

    /**
     * Envoyer un message dans un ticket
     */
    public function store(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:5120', // 5MB max
        ]);

        $user = $request->user();

        try {
            DB::beginTransaction();

            $message = new SupportMessage();
            $message->ticket_id = $ticket->id;
            $message->user_id = $user->id;
            $message->message = $request->message;
            $message->type = 'user';
            $message->save();

            // Gestion des pièces jointes
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('support/attachments/' . $ticket->id, 'public');
                    
                    SupportAttachment::create([
                        'message_id' => $message->id,
                        'filename' => basename($path),
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();

            broadcast(new NewSupportMessage($ticket, $message->load(['user', 'attachments'])))->toOthers();

            // Mettre à jour le statut du ticket si nécessaire
            if ($ticket->status === 'resolved' || $ticket->status === 'closed') {
                $ticket->status = 'open';
                $ticket->save();
            }

            return new MessageResource($message->load(['user', 'attachments']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Modifier un message
     */
    public function update(Request $request, SupportMessage $message)
    {
        $user = $request->user();

        if ($message->user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $request->validate([
            'message' => 'required|string',
            'delete_attachments' => 'nullable|array',
            'delete_attachments.*' => 'exists:support_attachments,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $message->message = $request->message;
            $message->save();

            // Supprimer les pièces jointes demandées
            if ($request->has('delete_attachments')) {
                $attachmentsToDelete = SupportAttachment::whereIn('id', $request->delete_attachments)
                    ->where('message_id', $message->id)
                    ->get();

                foreach ($attachmentsToDelete as $att) {
                    Storage::disk('public')->delete($att->path);
                    $att->delete();
                }
            }

            // Ajouter les nouvelles pièces jointes
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('support/attachments/' . $message->ticket_id, 'public');
                    
                    SupportAttachment::create([
                        'message_id' => $message->id,
                        'filename' => basename($path),
                        'original_name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();

            broadcast(new SupportMessageUpdated($message->load(['user', 'attachments'])))->toOthers();

            return new MessageResource($message->load(['user', 'attachments']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Supprimer un message
     */
    public function destroy(SupportMessage $message)
    {
        $user = auth()->user();
        
        if ($message->user_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        // Supprimer les pièces jointes physiques
        foreach ($message->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->path);
        }

        $ticketId = $message->ticket_id;
        $messageId = $message->id;

        $message->delete();

        broadcast(new SupportMessageDeleted($ticketId, $messageId))->toOthers();

        return response()->json(['message' => 'Message supprimé']);
    }
}
