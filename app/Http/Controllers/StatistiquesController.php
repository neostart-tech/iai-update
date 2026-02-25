<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\EmploiDuTemp;
use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\Salle;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatistiquesController extends Controller
{
    /**
     * Compte le nombre de filières pour l'année scolaire active.
     *
     * @return int
     */
    public function NbreFilieres(): int
    {
        $anneeActiveId = AnneeScolaire::where('active', true)->value('id');

        // Compte le nombre de filières liées à l'année scolaire active
        return Filiere::whereHas('anneesScolaires', function ($query) use ($anneeActiveId) {
            $query->where('annee_filiere.annee_scolaire_id', $anneeActiveId);
        })->count();
    }

    /**
     * Compte le nombre d'étudiants par niveau (Licence/Master) pour l'année scolaire active.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function NbreEtudiants()
    {
        $anneeActiveId = AnneeScolaire::where('active', true)->value('id');

        // Liste des codes de niveau pour Licence et Master
        $licenceCodes = ['L1', 'L2', 'L3'];
        $masterCodes = ['M1', 'M2', 'EM'];

        // Récupérer les IDs des niveaux Licence et Master
        $licenceNiveauIds = Niveau::whereIn('code', $licenceCodes)->pluck('id');
        $masterNiveauIds = Niveau::whereIn('code', $masterCodes)->pluck('id');

        // Compter les étudiants en Licence pour l'année active
        $nbreLicence = Etudiant::whereHas('etudiantGroups', function ($query) use ($anneeActiveId, $licenceNiveauIds) {
            $query->where('annee_scolaire_id', $anneeActiveId)
                ->whereIn('niveau_id', $licenceNiveauIds);
        })->count();

        // Compter les étudiants en Master pour l'année active
        $nbreMaster = Etudiant::whereHas('etudiantGroups', function ($query) use ($anneeActiveId, $masterNiveauIds) {
            $query->where('annee_scolaire_id', $anneeActiveId)
                ->whereIn('niveau_id', $masterNiveauIds);
        })->count();

        return response()->json([
            'licence' => $nbreLicence,
            'master' => $nbreMaster,
        ]);
    }

    /**
     * Compte le nombre de salles utilisées maintenant.
     *
     * @param string|null $dateHeureDeb (format : 'YYYY-MM-DD HH:MM:SS')
     * @param string|null $dateHeureFin (format : 'YYYY-MM-DD HH:MM:SS')
     * @return int
     */
    public function NbreSallesUtilisees(string $dateHeureDeb = null, string $dateHeureFin = null): int
    {
        $now = Carbon::now();
        $start = $dateHeureDeb ? Carbon::parse($dateHeureDeb) : $now;
        $end = $dateHeureFin ? Carbon::parse($dateHeureFin) : $now->copy()->addHour();

        $anneeActiveId = AnneeScolaire::where('active', true)->value('id');

        // Récupère les IDs des salles utilisées dans la plage horaire
        $sallesUtiliseesIds = EmploiDuTemp::where('annee_scolaire_id', $anneeActiveId)
            ->where(function ($query) use ($start, $end) {
                // Chevauchement : (debut_edt <= $end) AND (fin_edt >= $start)
                $query->where(function ($q) use ($start, $end) {
                    $q->where('debut', '<=', $end)
                        ->where('fin', '>=', $start);
                });
            })
            ->pluck('salle_id')
            ->unique();

        return $sallesUtiliseesIds->count();
    }

    /**
     * Compte le nombre de salles disponibles maintenant.
     *
     * @param string|null $dateHeureDeb (format : 'YYYY-MM-DD HH:MM:SS')
     * @param string|null $dateHeureFin (format : 'YYYY-MM-DD HH:MM:SS')
     * @return int
     */
    public function NbreSallesDispos(string $dateHeureDeb = null, string $dateHeureFin = null): int
    {
        $anneeActiveId = AnneeScolaire::where('active', true)->value('id');
        $totalSalles = Salle::where('annee_scolaire_id', $anneeActiveId)->count();
        $sallesUtilisees = $this->NbreSallesUtilisees($dateHeureDeb, $dateHeureFin);

        return $totalSalles - $sallesUtilisees;
    }

    /**
     * Compte le nombre total d'étudiants pour une année scolaire donnée (par défaut : l'année active).
     *
     * @param int|null $anneeScolaireId (optionnel) ID de l'année scolaire. Si null, utilise l'année active.
     * @return int
     */
    public function NbreTotalEtudiants(int $anneeScolaireId = null): int
    {
        // Si aucun ID n'est fourni, utilise l'année scolaire active
        $anneeId = $anneeScolaireId ?? AnneeScolaire::where('active', true)->value('id');

        // Compte le nombre d'étudiants inscrits dans des groupes pour cette année scolaire
        return Etudiant::whereHas('etudiantGroups', function ($query) use ($anneeId) {
            $query->where('annee_scolaire_id', $anneeId);
        })->count();
    }


}
