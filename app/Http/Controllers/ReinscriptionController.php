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
            'advertiser_id' => 'nullable|exists:advertisers,id',
            'promotion' => 'nullable|string',
        ]);

        $activeAnnee = AnneeScolaire::where('active', true)->first();
        if (!$activeAnnee) {
            $msg = "Aucune année scolaire active configurée.";
            return $request->wantsJson() 
                ? response()->json(['message' => $msg], 422) 
                : back()->with('error', $msg);
        }

        // Vérifier si déjà réinscrit pour cette année
        $dejaReinscript = DB::table('etudiant_group')
            ->where('etudiant_id', $etudiant->id)
            ->where('annee_scolaire_id', $activeAnnee->id)
            ->exists();

        if ($dejaReinscript) {
            $msg = "Cet étudiant est déjà réinscrit pour l'année scolaire en cours.";
            return $request->wantsJson() 
                ? response()->json(['message' => $msg], 422) 
                : back()->with('error', $msg);
        }

        try {
            DB::beginTransaction();

            // 1. Mise à jour des infos de base de l'étudiant
            $etudiant->update([
                'advertiser_id' => $request->advertiser_id ?? $etudiant->advertiser_id,
                'promotion' => $request->promotion ?? $etudiant->promotion,
            ]);

            // 2. Affectation au nouveau groupe
            $groupId = $request->group_id;
            $filiereId = null;
            
            // Si pas de groupe fourni, on cherche s'il y a un défaut
            if (!$groupId) {
                // On récupère la filière actuelle de l'étudiant via son dernier groupe
                $lastGroupPivot = DB::table('etudiant_group')
                    ->where('etudiant_id', $etudiant->id)
                    ->orderBy('id', 'desc')
                    ->first();
                
                $filiereId = $lastGroupPivot ? $lastGroupPivot->filiere_id : null;

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
                // On récupère la filière liée au groupe sélectionné
                $groupMap = FiliereGroup::where('group_id', $groupId)->first();
                $filiereId = $groupMap ? $groupMap->filiere_id : null;
            }

            if (!$groupId) {
                throw new \Exception("Veuillez sélectionner un groupe pour ce niveau.");
            }

            $etudiant->groups()->attach($groupId, [
                'annee_scolaire_id' => $activeAnnee->id,
                'niveau_id' => $request->niveau_id,
                'filiere_id' => $filiereId,
            ]);

            // 3. Enregistrement des frais de réinscription (si nécessaire)
            $fraisInscription = FraisInscription::where('active', true)->first() ?: FraisInscription::latest()->first();
            
            if ($fraisInscription && $fraisInscription->montant > 0) {
                Paiement::create([
                    "etudiant_id" => $etudiant->id,
                    "montant" => $fraisInscription->montant,
                    "mode_paiement" => "caisse",
                    "reference" => "REINS-" . strtoupper(Str::random(8)),
                    "status" => "valide",
                    "date_paiement" => now(),
                    "payable_type" => FraisInscription::class,
                    "payable_id" => $fraisInscription->id,
                    "annee_scolaire_id" => $activeAnnee->id,
                ]);
            }

            DB::commit();
            $msg = "Réinscription effectuée avec succès.";
            return $request->wantsJson() 
                ? response()->json(['message' => $msg]) 
                : back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "Erreur lors de la réinscription : " . $e->getMessage();
            return $request->wantsJson() 
                ? response()->json(['message' => $msg], 500) 
                : back()->with('error', $msg);
        }
    }
}
