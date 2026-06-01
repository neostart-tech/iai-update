<?php

namespace Database\Seeders;

use App\Models\Niveau;
use App\Models\Periode;
use Illuminate\Database\Seeder;

class NiveauPeriodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Définition précise des associations demandées
        $associations = [
            'Licence 1' => ['Semestre 1', 'Semestre 2'],
            'Licence 2' => ['Semestre 3', 'Semestre 4'],
            'Licence 3' => ['Semestre 5', 'Semestre 6'],
            'Master 1'  => ['Semestre 1', 'Semestre 2'],
            'Master 2'  => ['Semestre 3', 'Semestre 4'],
        ];

        foreach ($associations as $niveauLibelle => $periodesNoms) {
            // On cherche le niveau (ex: Licence 1)
            $niveau = Niveau::withoutGlobalScope('active')
                ->where('libelle', 'like', "%$niveauLibelle%")
                ->first();

            if ($niveau) {
                // On cherche les périodes qui correspondent aux noms (ex: Semestre 1, Semestre 2)
                // Note : On utilise whereIn pour les noms exacts et on filtre pour s'assurer de ne pas prendre
                // les semestres d'autres années si plusieurs existent (le scope global de Periode devrait aider)
                $periodeIds = Periode::whereIn('nom', $periodesNoms)->pluck('id');

                if ($periodeIds->isNotEmpty()) {
                    // On attache les périodes au niveau (sync pour éviter les doublons)
                    $niveau->periodes()->sync($periodeIds);
                    $this->command->info("Succès : Associé [" . implode(', ', $periodesNoms) . "] au niveau : $niveauLibelle");
                } else {
                    $this->command->warn("Attention : Aucun semestre trouvé en base pour les noms [" . implode(', ', $periodesNoms) . "]");
                }
            } else {
                $this->command->warn("Erreur : Le niveau '$niveauLibelle' n'existe pas dans la table niveaux.");
            }
        }
    }
}
