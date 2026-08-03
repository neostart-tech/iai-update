<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Etudiant;
use App\Models\FiliereGroup;
use App\Models\FraisInscription;
use App\Models\Paiement;
use App\Models\Niveau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReinscriptionController extends Controller
{
    public function store(Request $request, Etudiant $etudiant)
    {
        $request->validate([
            'niveau_id' => 'required|exists:niveaux,id',
            'group_id' => 'nullable|exists:groups,id',
            'filiere_id' => 'nullable|exists:filieres,id',
            'mode_formation' => 'nullable|string',
            'annee_scolaire_id' => 'nullable|exists:annee_scolaires,id',
            'type_decision' => 'nullable|string|in:promotion,redoublement,reorientation',
            'payer_frais_inscription' => 'nullable|boolean',
            'reaffecter_bourse' => 'nullable|boolean',
            'bourse_id' => 'nullable|exists:bourses,id',
            'mode_paiement' => 'nullable|string',
            'frais_retrait' => 'nullable|numeric',
            'reference' => 'nullable|string',
            'montant_frais_inscription' => 'nullable|numeric',
            'advertiser_id' => 'nullable|exists:advertisers,id',
            'promotion' => 'nullable|string',
        ]);

        $anneeScolaireId = $request->annee_scolaire_id;
        if ($anneeScolaireId) {
            $targetAnnee = AnneeScolaire::find($anneeScolaireId);
        } else {
            $targetAnnee = AnneeScolaire::where('active', true)->first() ?: AnneeScolaire::latest()->first();
        }

        if (!$targetAnnee) {
            $msg = "Aucune année scolaire configurée.";
            return $request->wantsJson() 
                ? response()->json(['message' => $msg], 422) 
                : back()->with('error', $msg);
        }

        $anneeLibelle = $targetAnnee->nom ?? $targetAnnee->code ?? $targetAnnee->libelle ?? ($targetAnnee->annee_debut && $targetAnnee->annee_fin ? $targetAnnee->annee_debut . '-' . $targetAnnee->annee_fin : null);

        // RÈGLE : Un étudiant ne peut être réinscrit qu'une SEULE fois par année scolaire
        $dejaReinscript = DB::table('etudiant_group')
            ->where('etudiant_id', $etudiant->id)
            ->where('annee_scolaire_id', $targetAnnee->id)
            ->exists();

        if ($dejaReinscript) {
            $msg = "Cet étudiant est déjà réinscrit pour l'année scolaire (" . ($anneeLibelle ?? $targetAnnee->id) . ").";
            return $request->wantsJson() 
                ? response()->json(['message' => $msg], 422) 
                : back()->with('error', $msg);
        }

        try {
            DB::beginTransaction();

            $decisionType = $request->input('type_decision', 'promotion');

            // 1. Mise à jour des infos de base de l'étudiant
            $updateData = [
                'advertiser_id' => $request->advertiser_id ?? $etudiant->advertiser_id,
                'promotion' => $request->promotion ?? $anneeLibelle ?? $etudiant->promotion,
            ];

            if ($request->filled('mode_formation')) {
                $updateData['mode_formation'] = $request->mode_formation;
            }

            $etudiant->update($updateData);

            // 2. Affectation au groupe et à la filière
            $groupId = $request->group_id;
            $filiereId = $request->filiere_id;
            
            if (!$groupId) {
                $lastGroupPivot = DB::table('etudiant_group')
                    ->where('etudiant_id', $etudiant->id)
                    ->orderBy('id', 'desc')
                    ->first();
                
                if (!$filiereId) {
                    $filiereId = $lastGroupPivot ? $lastGroupPivot->filiere_id : null;
                }

                if ($filiereId) {
                    $availableGroups = FiliereGroup::where('filiere_id', $filiereId)
                        ->join('groups', 'filiere_group.group_id', '=', 'groups.id')
                        ->where('groups.niveau_id', $request->niveau_id)
                        ->select('groups.*')
                        ->get();

                    if ($availableGroups->count() === 1) {
                        $groupId = $availableGroups->first()->id;
                    }
                }
            } else {
                if (!$filiereId) {
                    $groupMap = FiliereGroup::where('group_id', $groupId)->first();
                    $filiereId = $groupMap ? $groupMap->filiere_id : null;
                }
            }

            if (!$groupId) {
                throw new \Exception("Veuillez sélectionner un groupe / classe de destination.");
            }

            $etudiant->groups()->attach($groupId, [
                'annee_scolaire_id' => $targetAnnee->id,
                'niveau_id' => $request->niveau_id,
                'filiere_id' => $filiereId,
            ]);

            // 3. Enregistrement des frais de réinscription si paiement sélectionné
            $payerFrais = filter_var($request->input('payer_frais_inscription'), FILTER_VALIDATE_BOOLEAN);
            if ($payerFrais) {
                $fraisInscription = FraisInscription::where('active', true)->first() ?: FraisInscription::latest()->first();
                $montantPaiement = $request->filled('montant_frais_inscription') 
                    ? floatval($request->montant_frais_inscription) 
                    : ($fraisInscription ? floatval($fraisInscription->montant) : 0);
                
                if ($montantPaiement > 0) {
                    $ref = $request->filled('reference') 
                        ? $request->input('reference') 
                        : "REINS-" . strtoupper(Str::random(8));

                    Paiement::create([
                        "etudiant_id" => $etudiant->id,
                        "montant" => $montantPaiement,
                        "frais_retrait" => $request->filled('frais_retrait') ? floatval($request->frais_retrait) : 0,
                        "mode_paiement" => $request->input('mode_paiement', 'especes'),
                        "reference" => $ref,
                        "status" => "valide",
                        "date_paiement" => now(),
                        "payable_type" => FraisInscription::class,
                        "payable_id" => $fraisInscription ? $fraisInscription->id : null,
                        "annee_scolaire_id" => $targetAnnee->id,
                    ]);
                }
            }

            // 4. Reconduction / Attribution de la Bourse
            $bourseId = $request->input('bourse_id');
            $reaffecterBourse = filter_var($request->input('reaffecter_bourse'), FILTER_VALIDATE_BOOLEAN);

            if ($bourseId) {
                DB::table('bourse_etudiants')->updateOrInsert(
                    [
                        'etudiant_id' => $etudiant->id,
                        'annee_scolaire_id' => $targetAnnee->id,
                    ],
                    [
                        'bourse_id' => $bourseId,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            } elseif ($reaffecterBourse) {
                $lastBourse = DB::table('bourse_etudiants')
                    ->where('etudiant_id', $etudiant->id)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($lastBourse) {
                    DB::table('bourse_etudiants')->updateOrInsert(
                        [
                            'etudiant_id' => $etudiant->id,
                            'annee_scolaire_id' => $targetAnnee->id,
                        ],
                        [
                            'bourse_id' => $lastBourse->bourse_id,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }

            DB::commit();

            if ($decisionType === 'reorientation') {
                $actionWord = "Réorientation & Réinscription enregistrées";
            } elseif ($decisionType === 'redoublement') {
                $actionWord = "Redoublement enregistré";
            } else {
                $actionWord = "Réinscription & Promotion effectuées";
            }

            $msg = "{$actionWord} avec succès pour l'année scolaire " . ($anneeLibelle ?? '');
            return $request->wantsJson() 
                ? response()->json(['message' => $msg]) 
                : back()->with('error', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "Erreur lors de la réinscription : " . $e->getMessage();
            return $request->wantsJson() 
                ? response()->json(['message' => $msg], 500) 
                : back()->with('error', $msg);
        }
    }
}
