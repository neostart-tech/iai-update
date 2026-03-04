<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; 


class MessageController extends Controller 
{
    public function store(Request $request, Conversation $conversation)
    {
        $actor = auth()->user();

        // Validation adaptée selon qu'il y a du texte ou des fichiers
        $rules = [];
        
        if ($request->hasFile('attachments')) {
            $rules['attachments'] = 'array|max:10'; // Max 10 fichiers
            $rules['attachments.*'] = 'file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx,zip|max:10240'; // 10MB max
        }
        
        if ($request->has('body')) {
            $rules['body'] = 'string|nullable';
        }

        $request->validate($rules);

        // Déterminer le type de message
        $messageType = 'text';
        if ($request->hasFile('attachments') && !$request->filled('body')) {
            $messageType = 'file';
        } elseif ($request->hasFile('attachments') && $request->filled('body')) {
            $messageType = 'mixed';
        }

        DB::beginTransaction();

        try {
            // Créer le message
            $message = new Message([
                'body' => $request->body ?? '',
                'type' => $messageType
            ]);

            $message->conversation()->associate($conversation);
            $message->sender()->associate($actor);
            $message->save();

            // Traiter les fichiers joints
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('attachments/' . $conversation->id, 'public');
                    
                    $attachment = new MessageAttachment([
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'file_extension' => $file->getClientOriginalExtension()
                    ]);
                    
                    $attachment->message()->associate($message);
                    $attachment->save();
                }
            }

            DB::commit();

            // Charger les relations avant de broadcaster
            $message->load('sender', 'attachments');

            broadcast(new MessageSent($message))->toOthers();

            return response()->json($message->load('sender', 'attachments'), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Méthode pour télécharger un fichier
    public function downloadAttachment(Conversation $conversation, Message $message, MessageAttachment $attachment)
    {
        // Vérifier que l'utilisateur a accès à la conversation
        $user = auth()->user();
        
        $hasAccess = DB::table('conversation_users')
            ->where('conversation_id', $conversation->id)
            ->where('participant_id', $user->id)
            ->where('participant_type', get_class($user))
            ->exists();

        if (!$hasAccess) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Vérifier que la pièce jointe appartient bien au message et à la conversation
        if ($attachment->message_id !== $message->id || $message->conversation_id !== $conversation->id) {
            return response()->json(['error' => 'Fichier non trouvé'], 404);
        }

        // Vérifier que le fichier existe
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return response()->json(['error' => 'Fichier non trouvé sur le serveur'], 404);
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    // Méthode pour prévisualiser un fichier (images)
    public function previewAttachment(Conversation $conversation, Message $message, MessageAttachment $attachment)
    {
        // Mêmes vérifications que pour le téléchargement
        $user = auth()->user();
        
        $hasAccess = DB::table('conversation_users')
            ->where('conversation_id', $conversation->id)
            ->where('participant_id', $user->id)
            ->where('participant_type', get_class($user))
            ->exists();

        if (!$hasAccess) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        if ($attachment->message_id !== $message->id || $message->conversation_id !== $conversation->id) {
            return response()->json(['error' => 'Fichier non trouvé'], 404);
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return response()->json(['error' => 'Fichier non trouvé sur le serveur'], 404);
        }

        // Pour les images, on peut retourner le contenu directement
        if (strpos($attachment->mime_type, 'image/') === 0) {
            return response()->file(storage_path('app/public/' . $attachment->file_path));
        }

        // Sinon, rediriger vers le téléchargement
        return redirect()->route('messages.attachments.download', [
            'conversation' => $conversation->id,
            'message' => $message->id,
            'attachment' => $attachment->id
        ]);
    }

    // Le reste de vos méthodes existantes (index, show, destroy) restent inchangées
    public function index(Conversation $conversation)
    {
        // ... votre code existant
        $user = auth()->user();
        
        $hasAccess = DB::table('conversation_users')
            ->where('conversation_id', $conversation->id)
            ->where('participant_id', $user->id)
            ->where('participant_type', get_class($user))
            ->exists();

        if (!$hasAccess) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $messages = $conversation->messages()
            ->with('sender', 'attachments')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    public function show(Conversation $conversation, Message $message)
    {
        if ($message->conversation_id !== $conversation->id) {
            return response()->json(['error' => 'Message non trouvé'], 404);
        }

        return response()->json($message->load('sender', 'attachments'));
    }

    public function destroy(Conversation $conversation, Message $message)
    {
        $user = auth()->user();
        
        if ($message->sender_id !== $user->id || $message->sender_type !== get_class($user)) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Supprimer les fichiers associés
        foreach ($message->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $message->delete();
        
        return response()->json(['message' => 'Message supprimé']);
    }

    public function deleteAttachment(Conversation $conversation, Message $message, MessageAttachment $attachment)
{
    $user = auth()->user();
    
    // Vérifier que l'utilisateur est le propriétaire du message
    if ($message->sender_id !== $user->id || $message->sender_type !== get_class($user)) {
        return response()->json(['error' => 'Non autorisé'], 403);
    }

    // Vérifier que la pièce jointe appartient bien au message
    if ($attachment->message_id !== $message->id) {
        return response()->json(['error' => 'Pièce jointe non trouvée'], 404);
    }

    DB::beginTransaction();
    try {
        // Supprimer le fichier physiquement
        Storage::disk('public')->delete($attachment->file_path);
        
        // Supprimer l'enregistrement
        $attachment->delete();
        
        DB::commit();
        
        return response()->json(['message' => 'Pièce jointe supprimée avec succès']);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}