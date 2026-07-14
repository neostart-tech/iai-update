<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProspectRequest;
use App\Models\Prospect;
use Illuminate\Http\JsonResponse;

class PublicProspectController extends Controller
{
    public function store(ProspectRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        $prospect = Prospect::where('email', $data['email'])->first();
        
        $baseFormation = trim($data['formation_visee'] ?? 'Brochure Web');
        
        if ($prospect) {
            $existingFormationsStr = $prospect->formation_visee ?? '';
            $existingFormations = array_filter(array_map('trim', explode('|', $existingFormationsStr)));
            
            // Nettoyer les anciennes dates dans les noms existants
            $cleanedFormations = [];
            foreach ($existingFormations as $ef) {
                // Supprime tout ce qui est entre parenthèses à la fin de la chaîne
                $cleanEf = trim(preg_replace('/\(.*?\)$/', '', $ef));
                if (!empty($cleanEf)) {
                    $cleanedFormations[] = $cleanEf;
                }
            }
            
            // Construire une liste unique (insensible à la casse)
            $uniqueFormations = [];
            foreach ($cleanedFormations as $cf) {
                $found = false;
                foreach ($uniqueFormations as $uf) {
                    if (strtolower($uf) === strtolower($cf)) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $uniqueFormations[] = $cf;
                }
            }
            
            // Ajouter la nouvelle formation si elle n'est pas déjà dans la liste
            $isDuplicate = false;
            foreach ($uniqueFormations as $uf) {
                if (strtolower($uf) === strtolower($baseFormation)) {
                    $isDuplicate = true;
                    break;
                }
            }
            
            if (!$isDuplicate) {
                $uniqueFormations[] = $baseFormation;
            }
            
            $finalString = implode(' | ', $uniqueFormations);
            
            $prospect->formation_visee = $finalString;
            
            if (!empty($data['tel'])) {
                $prospect->tel = $data['tel'];
            }
            if (!empty($data['nom'])) {
                $prospect->nom = $data['nom'];
            }
            
            // On met à jour la date de création pour faire remonter le prospect en haut de la liste
            $prospect->created_at = now();
            $prospect->save();
        } else {
            $data['formation_visee'] = $baseFormation;
            Prospect::create([
                ...$data,
                'status' => false
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Prospect enregistré avec succès.'
        ], 201);
    }
}
