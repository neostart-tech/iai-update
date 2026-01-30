<?php

namespace App\Http\Controllers;

use App\Models\Documentation;
use App\Models\DocumentationAccess;
use App\Models\Filiere;
use App\Models\Group;
use App\Models\Niveau;
use App\Models\Role;
use App\Services\DocumentAccessService as ServicesDocumentAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentationController extends Controller
{

    public function userIndex(Request $request, ServicesDocumentAccessService $service)
    {
        $auth = auth()->user() ?? auth()->guard('etudiants')->user();

        if (!$auth) {
            abort(403); // ou redirect()->route('login')
        }


        // On récupère tous les documents accessibles à l'utilisateur
        $query = $service->getDocumentsFor($auth);

        // Comme getDocumentsFor() renvoie une collection, on convertit en query builder
        $query = Documentation::whereIn('id', $query->pluck('id'));

        // Recherche
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filtre accès
        if ($request->filled('access')) {
            if ($request->access === 'public') {
                $query->doesntHave('accesses');
            } elseif ($request->access === 'private') {
                $query->has('accesses');
            }
        }

        // Pagination et ordre
        $documents = $query->latest()->paginate(12)->withQueryString();

        return view('documentations.user._index', compact('documents'));
    }



    public function index()
    {
        // Récupère tous les documents, triés par date de création décroissante
        $documents = Documentation::with('accesses')->latest()->get();

        // Les rôles, groupes, filières et niveaux restent pour le formulaire
        $roles = Role::all()->reverse();
        $groupes = Group::with('niveau')->get()->reverse();
        $filieres = Filiere::all()->reverse();
        $niveaux = Niveau::all()->reverse();

        return view('documentations.index', compact('documents', 'roles', 'groupes', 'filieres', 'niveaux'));
    }



    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            $file = $request->file('file');

            // Nom propre du fichier
            $filename = Str::slug($request->title)
                . '_' . now()->format('Ymd_His')
                . '.' . $file->getClientOriginalExtension();

            // Stockage
            $path = $file->storeAs('documents', $filename);

            $doc = Documentation::create([
                'title' => $request->title,
                'path'  => $path,
            ]);

            foreach ($request->roles ?? [] as $id) {
                DocumentationAccess::create([
                    'documentation_id' => $doc->id,
                    'access_type' => DocumentationAccess::ROLE,
                    'access_id' => $id,
                ]);
            }

            foreach ($request->groupes ?? [] as $id) {
                DocumentationAccess::create([
                    'documentation_id' => $doc->id,
                    'access_type' => DocumentationAccess::GROUPE,
                    'access_id' => $id,
                ]);
            }

            foreach ($request->filieres ?? [] as $id) {
                DocumentationAccess::create([
                    'documentation_id' => $doc->id,
                    'access_type' => DocumentationAccess::FILIERE,
                    'access_id' => $id,
                ]);
            }

            foreach ($request->niveaux ?? [] as $id) {
                DocumentationAccess::create([
                    'documentation_id' => $doc->id,
                    'access_type' => DocumentationAccess::NIVEAU,
                    'access_id' => $id,
                ]);
            }
        });

        return redirect()->route('documentation.liste');
    }


    public function update(Request $request, Documentation $documentation)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file'  => 'nullable|file|max:10240',
        ]);

        DB::transaction(function () use ($request, $documentation) {
            $documentation->update([
                'title' => $request->title,
            ]);

            if ($request->hasFile('file')) {

                if (Storage::exists($documentation->path)) {
                    Storage::delete($documentation->path);
                }

                $file = $request->file('file');
                $filename = Str::slug($request->title)
                    . '_' . now()->format('Ymd_His')
                    . '.' . $file->getClientOriginalExtension();

                $path = $file->storeAs('documents', $filename);

                $documentation->update([
                    'path' => $path,
                ]);
            }

            DocumentationAccess::where('documentation_id', $documentation->id)->delete();

            foreach ($request->roles ?? [] as $id) {
                DocumentationAccess::create([
                    'documentation_id' => $documentation->id,
                    'access_type' => DocumentationAccess::ROLE,
                    'access_id' => $id,
                ]);
            }

            foreach ($request->groupes ?? [] as $id) {
                DocumentationAccess::create([
                    'documentation_id' => $documentation->id,
                    'access_type' => DocumentationAccess::GROUPE,
                    'access_id' => $id,
                ]);
            }

            foreach ($request->filieres ?? [] as $id) {
                DocumentationAccess::create([
                    'documentation_id' => $documentation->id,
                    'access_type' => DocumentationAccess::FILIERE,
                    'access_id' => $id,
                ]);
            }

            foreach ($request->niveaux ?? [] as $id) {
                DocumentationAccess::create([
                    'documentation_id' => $documentation->id,
                    'access_type' => DocumentationAccess::NIVEAU,
                    'access_id' => $id,
                ]);
            }
        });
        return response()->json(['success' => true, 'message' => 'Document modifié avec succès']);

        // return redirect()
        //     ->route('documentation.liste')
        //     ->with('success', 'Document modifié avec succès');
    }

    public function destroy(Documentation $documentation)
    {
        DB::transaction(function () use ($documentation) {

            if (Storage::exists($documentation->path)) {
                Storage::delete($documentation->path);
            }

            DocumentationAccess::where('documentation_id', $documentation->id)->delete();

            $documentation->delete();
        });

        // return redirect()
        //     ->route('documentation.liste')
        //     ->with('success', 'Document supprimé avec succès');
        return response()->json(['success' => true, 'message' => 'Document supprimé avec succès']);
    }


    public function download(Documentation $document)
    {
        // Sécurité : vérifier que le fichier existe
        if (!Storage::exists($document->path)) {
            abort(404, 'Fichier introuvable');
        }

        // Nom original du fichier (optionnel mais recommandé)
        $filename = basename($document->path);

        return Storage::download($document->path, $filename);
    }
}
