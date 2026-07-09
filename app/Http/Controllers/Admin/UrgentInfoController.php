<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UrgentInfoResource;
use App\Models\UrgentInfo;
use App\Notifications\ActualiteNotification;
use App\Models\User;
use App\Models\Etudiant;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class UrgentInfoController extends Controller
{
    public function index()
    {
        $items = UrgentInfo::query()->latest()->get();
        return UrgentInfoResource::collection($items);
        // return view('admin.urgent_infos.index', compact('items'));
    }

    public function show(UrgentInfo $urgent)
    {
        return new UrgentInfoResource($urgent);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'file_url' => ['nullable', 'url'],
            'file' => ['nullable', 'file', 'max:10240'], // Deprecated but kept for compatibility
            'image' => ['nullable', 'image', 'max:5120'], // 5MB
            'attachments.*' => ['nullable', 'file', 'max:20480'], // 20MB per file
            'target_audience' => ['required', 'in:all,students,teachers,administration,group'],
            'target_group_id' => ['nullable', 'exists:groups,id'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        // Le résumé est affiché en texte brut sur la page publique : on retire toute
        // balise HTML à l'enregistrement pour éviter qu'elle s'affiche littéralement
        // (et pour ne pas rouvrir de faille XSS si on passait un jour en rendu HTML).
        if (isset($data['summary'])) {
            $data['summary'] = trim(strip_tags($data['summary']));
        }

        // Existing single file handling
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('urgent-infos', 'public');
            $data['file_path'] = $path;
        }

        // New Image handling
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('actualites/images', 'public');
            $data['image'] = $path;
        }

        // Multiple attachments handling
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('actualites/attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ];
            }
            $data['attachments'] = $attachments;
        }

        $data['created_by'] = Auth::id();
        if (!empty($data['is_published'])) {
            $data['published_at'] = now();
        }

        $info = UrgentInfo::create($data);

        if ($info->is_published) {
            $this->sendNotifications($info);
        }

        return new UrgentInfoResource($info);
    }

    public function update(Request $request, UrgentInfo $urgent)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'file_url' => ['nullable', 'url'],
            'file' => ['nullable', 'file', 'max:10240'],
            'image' => ['nullable', 'image', 'max:5120'],
            'attachments.*' => ['nullable', 'file', 'max:20480'],
            'target_audience' => ['required', 'in:all,students,teachers,administration,group'],
            'target_group_id' => ['nullable', 'exists:groups,id'],
            'existing_attachments' => ['nullable', 'string'], // JSON string
        ]);

        if (isset($data['summary'])) {
            $data['summary'] = trim(strip_tags($data['summary']));
        }

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('urgent-infos', 'public');
            $data['file_path'] = $path;
        }

        if ($request->hasFile('image')) {
            if ($urgent->image) Storage::disk('public')->delete($urgent->image);
            $path = $request->file('image')->store('actualites/images', 'public');
            $data['image'] = $path;
        }

        // Gérer les pièces jointes : existantes + nouvelles
        $allAttachments = [];

        // 1. Conserver les pièces jointes existantes sélectionnées
        if ($request->has('existing_attachments')) {
            $existing = json_decode($request->input('existing_attachments'), true);
            if (is_array($existing)) {
                foreach ($existing as $att) {
                    // Retrouver le path original depuis les données existantes
                    $originalAttachments = $urgent->attachments ?? [];
                    foreach ($originalAttachments as $origAtt) {
                        if (isset($origAtt['name']) && isset($att['name']) && $origAtt['name'] === $att['name']) {
                            $allAttachments[] = $origAtt;
                            break;
                        }
                    }
                }
            }
        }

        // 2. Ajouter les nouveaux fichiers uploadés
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('actualites/attachments', 'public');
                $allAttachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ];
            }
        }

        // Mettre à jour seulement si on a des changements de PJ
        if ($request->has('existing_attachments') || $request->hasFile('attachments')) {
            $data['attachments'] = $allAttachments;
        }

        // Retirer existing_attachments du data (ce n'est pas un champ du modèle)
        unset($data['existing_attachments']);

        $urgent->update($data);
        return new UrgentInfoResource($urgent);
    }

    public function publish(UrgentInfo $urgent)
    {
        try {
            $urgent->is_published = true;
            $urgent->published_at = now();
            $urgent->save();

            $this->sendNotifications($urgent);

            return new UrgentInfoResource($urgent);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function unpublish(UrgentInfo $urgent)
    {
        $urgent->is_published = false;
        $urgent->published_at=null;
        $urgent->save();
        return new UrgentInfoResource($urgent);
        // return back()->with('success', 'Dépublication effectuée.');
    }

    public function destroy(UrgentInfo $urgent)
    {
        $urgent->delete();
        return new UrgentInfoResource($urgent);
    }

    protected function sendNotifications(UrgentInfo $actualite)
    {
        $notifiables = collect();

        switch ($actualite->target_audience) {
            case 'all':
                $notifiables = $notifiables->merge(User::all());
                $notifiables = $notifiables->merge(Etudiant::all());
                break;
            
            case 'students':
                $notifiables = Etudiant::all();
                break;
            
            case 'teachers':
                $notifiables = User::enseignants()->get();
                break;
            
            case 'administration':
                $notifiables = User::whereDoesntHave('roles', function($q) {
                    $q->whereIn('role_id', User::$enseignantRolesId);
                })->get();
                break;
            
            case 'group':
                if ($actualite->target_group_id) {
                    $group = Group::find($actualite->target_group_id);
                    if ($group) {
                        $notifiables = $group->etudiants;
                    }
                }
                break;
        }

        if ($notifiables->count() > 0) {
            Notification::send($notifiables, new ActualiteNotification($actualite));
        }
    }

    public function getGroups()
    {
        return Group::select('id', 'nom')->orderBy('nom')->get();
    }
}
