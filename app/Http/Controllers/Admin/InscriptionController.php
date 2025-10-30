<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inscription;
use Illuminate\Http\Request;

class InscriptionController extends Controller
{
    public function index(Request $request)
    {
        $q = Inscription::query()->latest();

        if ($request->filled('serie')) {
            $q->where('serie', $request->string('serie'));
        }
        if ($request->filled('annee_bac')) {
            $q->where('annee_bac', (int) $request->input('annee_bac'));
        }
        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }
        if ($request->filled('q')) {
            $term = '%' . trim($request->input('q')) . '%';
            $q->where(function($w) use ($term){
                $w->where('numero_table', 'like', $term)
                  ->orWhere('phone1', 'like', $term)
                  ->orWhere('phone2', 'like', $term)
                  ->orWhere('phone3', 'like', $term)
                  ->orWhere('email', 'like', $term);
            });
        }

        $inscriptions = $q->paginate(20)->appends($request->query());

        $metaData = [
            'title' => 'Liste des inscriptions',
            'description' => "Consultation des formulaires d'inscription soumis",
        ];

        return view('admin.inscriptions.index', [
            'inscriptions' => $inscriptions,
            'metaData' => $metaData,
            'viewContent' => null,
            'filters' => [
                'serie' => $request->input('serie'),
                'annee_bac' => $request->input('annee_bac'),
                'status' => $request->input('status'),
                'q' => $request->input('q'),
            ],
        ]);
    }

    public function show(Inscription $inscription)
    {
        $metaData = [
            'title' => 'Détail inscription',
            'description' => "Fiche détaillée d'une inscription",
        ];
        return view('admin.inscriptions.show', [
            'inscription' => $inscription,
            'metaData' => $metaData,
        ]);
    }
}
