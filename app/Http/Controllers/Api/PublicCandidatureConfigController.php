<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ConfigHelper;
use App\Http\Controllers\Controller;
use App\Models\CandidatureFieldConfig;
use App\Models\MoyenConnaissance;
use App\Models\TypeDiplome;

class PublicCandidatureConfigController extends Controller
{
    /**
     * Configuration du formulaire de candidature de cette école : quels champs
     * sont obligatoires, et quels types de diplôme (+ leurs champs) proposer.
     * Consommée par les formulaires de dépôt (Blade "faire-mon-depot" et les
     * frontends Nuxt) pour afficher les bons astérisques/attributs required.
     */
    public function index()
    {
        return response()->json([
            // Seuls les champs affichés sont transmis : leur simple présence dans la
            // réponse suffit au frontend pour décider s'il faut rendre le bloc ou non.
            'champs' => CandidatureFieldConfig::where('afficher', true)->orderBy('label')->get(['champ_key', 'label', 'obligatoire']),
            'types_diplome' => TypeDiplome::actifs()->with('champs:id,type_diplome_id,champ_key,obligatoire')->get(['id', 'nom', 'ordre']),
            // Sigle de l'établissement, pour construire le label "Comment avez-vous
            // connu {sigle} ?" et les options du select associé.
            'sigle' => ConfigHelper::getSigle(),
            'moyens_connaissance' => MoyenConnaissance::actifs()->get(['id', 'libelle']),
        ]);
    }
}
