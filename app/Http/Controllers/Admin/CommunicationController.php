<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Communication;
use App\Models\CommunicationAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommunicationController extends Controller
{
    public function index()
    {
        $communications = Communication::with(['author', 'attachments'])
            ->withCount('readers')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($communications);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|string',
            'target_type' => 'required|string',
            'target_data' => 'nullable|array',
            'is_published' => 'boolean',
            'expires_at' => 'nullable|date',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max
        ]);

        $communication = Communication::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'target_type' => $validated['target_type'],
            'target_data' => $validated['target_data'],
            'is_published' => $validated['is_published'] ?? false,
            'published_at' => ($validated['is_published'] ?? false) ? now() : null,
            'expires_at' => $validated['expires_at'],
            'author_id' => auth()->id(),
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('communications/attachments', 'public');
                CommunicationAttachment::create([
                    'communication_id' => $communication->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }
        if ($communication->is_published) {
            $this->notifyUsers($communication);
        }

        return response()->json($communication->load('attachments'), 201);
    }

    public function show(Communication $communication)
    {
        $communication->load(['author', 'attachments']);
        
        $targetDetails = [];
        if ($communication->target_type === 'roles') {
            $targetDetails = \App\Models\Role::whereIn('id', $communication->target_data ?? [])->pluck('nom')->toArray();
        } elseif ($communication->target_type === 'specific_users') {
            $targetDetails = \App\Models\User::whereIn('id', $communication->target_data ?? [])->pluck('nom')->toArray(); // ou nom_complet si dispo
        }
        
        $communication->target_data_details = $targetDetails;
        
        return response()->json($communication);
    }

    public function update(Request $request, Communication $communication)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|string',
            'target_type' => 'required|string',
            'target_data' => 'nullable|array',
            'is_published' => 'boolean',
            'expires_at' => 'nullable|date',
        ]);

        $wasPublished = $communication->is_published;
        
        $communication->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'target_type' => $validated['target_type'],
            'target_data' => $validated['target_data'],
            'is_published' => $validated['is_published'],
            'published_at' => (!$wasPublished && $validated['is_published']) ? now() : $communication->published_at,
            'expires_at' => $validated['expires_at'],
        ]);

        if (!$wasPublished && $communication->is_published) {
            $this->notifyUsers($communication);
        }

        return response()->json($communication->load('attachments'));
    }

    private function notifyUsers(Communication $communication)
    {
        $users = collect();

        if ($communication->target_type === 'all') {
            $users = \App\Models\User::all();
        } elseif ($communication->target_type === 'roles') {
            $roleIds = $communication->target_data ?? [];
            $users = \App\Models\User::whereHas('roles', function ($query) use ($roleIds) {
                $query->whereIn('role_id', $roleIds);
            })->get();
        } elseif ($communication->target_type === 'specific_users') {
            $userIds = $communication->target_data ?? [];
            $users = \App\Models\User::whereIn('id', $userIds)->get();
        }

        \Illuminate\Support\Facades\Notification::send($users, new \App\Notifications\CommunicationPublishedNotification($communication));
    }

    public function destroy(Communication $communication)
    {
        $communication->delete();
        return response()->json(['message' => 'Communication supprimée']);
    }

    public function uploadAttachments(Request $request, Communication $communication)
    {
        $request->validate([
            'files.*' => 'required|file|max:10240',
        ]);

        $attachments = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('communications/attachments', 'public');
                $attachments[] = CommunicationAttachment::create([
                    'communication_id' => $communication->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        return response()->json($attachments);
    }

    public function deleteAttachment(CommunicationAttachment $attachment)
    {
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();
        return response()->json(['message' => 'Fichier supprimé']);
    }
}
