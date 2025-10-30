<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Inscription;
use Illuminate\Support\Facades\Validator;

class InscriptionController extends Controller
{
    public function create()
    {
        // Page du formulaire d'inscription
        return view('pages.inscription');
    }

    public function store(Request $request)
    {
        $series = [
            'C', 'D'
        ];

        $validator = Validator::make($request->all(), [
            'numero_table' => ['required', 'string', 'max:50'],
            'annee_bac' => ['required', 'integer', 'min:1990', 'max:' . date('Y')],
            'lettre_motivation' => ['required', 'string', 'min:50'],
            'serie' => ['required', 'in:' . implode(',', $series)],

            'certificat_medical' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'email' => ['nullable', 'email', 'max:255'],

            'phone1' => ['required', 'string', 'min:7', 'max:20'],
            'phone2' => ['required', 'string', 'min:7', 'max:20'],
            'phone3' => ['nullable', 'string', 'min:7', 'max:20'],

            'tuteur_lieu' => ['required', 'string', 'max:150'],
            // Checkbox must be checked
            'accepte' => ['accepted'],

            // Trois groupes de bulletins, minimum 2 par niveau
            'bulletins_seconde' => ['required', 'array', 'min:2'],
            'bulletins_seconde.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'bulletins_premiere' => ['required', 'array', 'min:2'],
            'bulletins_premiere.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'bulletins_terminale' => ['required', 'array', 'min:2'],
            'bulletins_terminale.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'releve_bac1' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'releve_bac2' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);

        $validated = $validator->validate();

        // Dossier de stockage
        $folder = 'inscriptions/' . Str::slug($validated['numero_table'] . '-' . $validated['annee_bac']) . '-' . time();

        // Enregistrer les fichiers
        $paths = [];
        if ($request->hasFile('certificat_medical')) {
            $paths['certificat_medical'] = $request->file('certificat_medical')->store($folder, 'public');
        }

        // Stocker et fusionner les bulletins de chaque niveau
        $paths['bulletins_lycee'] = [];
        foreach (['bulletins_seconde','bulletins_premiere','bulletins_terminale'] as $group) {
            foreach ($request->file($group, []) as $file) {
                $paths['bulletins_lycee'][] = $file->store($folder, 'public');
            }
        }

        $paths['releve_bac1'] = $request->file('releve_bac1')->store($folder, 'public');
        $paths['releve_bac2'] = $request->file('releve_bac2')->store($folder, 'public');

        // Persister en base de données
        $isAccepted = $request->boolean('accepte');
        Inscription::create([
            'numero_table' => $validated['numero_table'],
            'annee_bac' => $validated['annee_bac'],
            'lettre_motivation' => $validated['lettre_motivation'],
            'serie' => $validated['serie'],
            'email' => $validated['email'] ?? null,
            'phone1' => $validated['phone1'],
            'phone2' => $validated['phone2'],
            'phone3' => $validated['phone3'] ?? null,
            'tuteur_lieu' => $validated['tuteur_lieu'],
            'accepte' => $isAccepted ? 'accepte' : 'refuse',
            'certificat_medical_path' => $paths['certificat_medical'] ?? null,
            'bulletins_lycee_paths' => $paths['bulletins_lycee'],
            'releve_bac1_path' => $paths['releve_bac1'],
            'releve_bac2_path' => $paths['releve_bac2'],
            'status' => 'pending',
        ]);

        return back()->with('success', "Votre inscription a été soumise avec succès. Nous vous contacterons prochainement.");
    }
}
