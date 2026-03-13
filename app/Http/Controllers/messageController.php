<?php

namespace App\Http\Controllers;

use App\Events\MessageDeleted;
use App\Events\MessageSent;
use App\Events\MessageUpdated;
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
   /**
 * Version améliorée avec gestion des erreurs
 */
public function downloadAttachment(Conversation $conversation, Message $message, MessageAttachment $attachment)
{
    try {
        // Vérifier l'accès
        $user = auth()->user();
        if (!$this->checkAccess($user, $conversation)) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Vérifier l'appartenance
        if (!$this->checkOwnership($attachment, $message, $conversation)) {
            return response()->json(['error' => 'Fichier non trouvé'], 404);
        }

        // Vérifier l'existence du fichier
        $filePath = storage_path('app/public/' . $attachment->file_path);
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Fichier non trouvé sur le serveur'], 404);
        }

        // Déterminer le nom de fichier correct
        $fileName = $this->getCorrectFileName($attachment);
        
        // Déterminer le type MIME
        $mimeType = $this->getMimeType($attachment, $filePath);

        // Log pour déboguer
       

        // Retourner le fichier avec les bons en-têtes
        return response()->download(
            $filePath,
            $fileName,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Content-Transfer-Encoding' => 'binary',
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => 'private',
                'Pragma' => 'private',
                'Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT',
            ]
        );

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors du téléchargement',
            'message' => $e->getMessage()
        ], 500);
    }
}

/**
 * Méthodes privées utilitaires
 */
private function checkAccess($user, $conversation)
{
    return DB::table('conversation_users')
        ->where('conversation_id', $conversation->id)
        ->where('participant_id', $user->id)
        ->where('participant_type', get_class($user))
        ->exists();
}

private function checkOwnership($attachment, $message, $conversation)
{
    return $attachment->message_id === $message->id 
        && $message->conversation_id === $conversation->id;
}

private function getCorrectFileName($attachment)
{
    $fileName = $attachment->file_name;
    $extension = $attachment->file_extension;
    
    // Vérifier si le nom a déjà une extension
    $hasExtension = !empty(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Si pas d'extension mais qu'on a l'extension en base, l'ajouter
    if (!$hasExtension && !empty($extension)) {
        $fileName = $fileName . '.' . $extension;
    }
    
    // Nettoyer le nom de fichier (enlever les caractères spéciaux)
    $fileName = preg_replace('/[^\w\-\.\s]/u', '_', $fileName);
    
    return $fileName;
}

private function getMimeType($attachment, $filePath)
{
    // Utiliser le mime_type enregistré
    if (!empty($attachment->mime_type)) {
        return $attachment->mime_type;
    }
    
    // Détecter par l'extension
    $extension = $attachment->file_extension ?? pathinfo($attachment->file_name, PATHINFO_EXTENSION);
    
    $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'zip' => 'application/zip',
        'txt' => 'text/plain',
    ];
    
    if (isset($mimeTypes[strtolower($extension)])) {
        return $mimeTypes[strtolower($extension)];
    }
    
    // Détection par le contenu du fichier
    return mime_content_type($filePath) ?? 'application/octet-stream';
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

        broadcast(new MessageDeleted($message))->toOthers();


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
                broadcast(new MessageDeleted($message))->toOthers();

            $attachment->delete();

            DB::commit();

            return response()->json(['message' => 'Pièce jointe supprimée avec succès']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Conversation $conversation, Message $message)
    {
        $user = auth()->user();

        // Vérifier que l'utilisateur est le propriétaire du message
        if ($message->sender_id !== $user->id || $message->sender_type !== get_class($user)) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Vérifier que le message appartient bien à la conversation
        if ($message->conversation_id !== $conversation->id) {
            return response()->json(['error' => 'Message non trouvé'], 404);
        }

        $request->validate([
            'body' => 'required|string'
        ]);

        $message->body = $request->body;
        $message->is_edited = true;
        $message->edited_at = now();
        $message->save();

        // Recharger les relations
        $message->load('sender', 'attachments');

        // Optionnel : broadcaster la modification
        broadcast(new MessageUpdated($message))->toOthers();

        return response()->json($message, 200);
    }
}
