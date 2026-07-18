<?php

namespace Database\Seeders;

use App\Models\CandidatureFieldConfig;
use App\Models\Configuration;
use App\Models\MoyenConnaissance;
use App\Models\TypeDiplome;
use Illuminate\Database\Seeder;

class CandidatureFieldConfigSeeder extends Seeder
{
    /**
     * Reproduit exactement le comportement actuel (rien ne change tant que
     * l'admin ne reconfigure rien) : les champs listés ici sont ceux qui ne
     * sont aujourd'hui PAS validés comme `required` dans CandidatureController,
     * donc initialisés à `obligatoire = false` — sauf nom/prénom du tuteur qui
     * sont de facto requis aujourd'hui (un tuteur sans nom est ignoré au dépôt).
     */
    public function run(): void
    {
        $champs = [
            ['champ_key' => 'nom_jeune_fille', 'label' => 'Nom de jeune fille', 'obligatoire' => false, 'afficher' => true],
            ['champ_key' => 'tel2', 'label' => 'Téléphone secondaire', 'obligatoire' => false, 'afficher' => true],
            ['champ_key' => 'tel3', 'label' => 'Téléphone supplémentaire', 'obligatoire' => false, 'afficher' => true],
            ['champ_key' => 'bp', 'label' => 'Boîte postale', 'obligatoire' => false, 'afficher' => true],
            ['champ_key' => 'fax', 'label' => 'Fax', 'obligatoire' => false, 'afficher' => true],
            ['champ_key' => 'adresse', 'label' => 'Adresse / Quartier', 'obligatoire' => false, 'afficher' => true],
            ['champ_key' => 'numero_bordereau', 'label' => 'Numéro de bordereau', 'obligatoire' => false, 'afficher' => false],
            ['champ_key' => 'tuteur_nom', 'label' => 'Nom du tuteur', 'obligatoire' => true, 'afficher' => true],
            ['champ_key' => 'tuteur_prenom', 'label' => 'Prénom du tuteur', 'obligatoire' => true, 'afficher' => true],
            ['champ_key' => 'tuteur_profession', 'label' => 'Profession du tuteur', 'obligatoire' => false, 'afficher' => true],
            ['champ_key' => 'tuteur_employeur', 'label' => 'Employeur du tuteur', 'obligatoire' => false, 'afficher' => true],
            ['champ_key' => 'tuteur_email', 'label' => 'Email du tuteur', 'obligatoire' => false, 'afficher' => true],
            ['champ_key' => 'tuteur_tel', 'label' => 'Téléphone du tuteur', 'obligatoire' => false, 'afficher' => true],
            ['champ_key' => 'tuteur_adresse', 'label' => 'Adresse du tuteur', 'obligatoire' => false, 'afficher' => true],
            ['champ_key' => 'comment_connu_ecole', 'label' => 'Comment avez-vous connu notre établissement ?', 'obligatoire' => false, 'afficher' => true],
        ];

        foreach ($champs as $champ) {
            CandidatureFieldConfig::firstOrCreate(['champ_key' => $champ['champ_key']], $champ);
        }

        // Moyens de connaissance de départ : liste indicative, gérée ensuite
        // librement par l'école dans Paramètres > Moyens de connaissance.
        $moyensParDefaut = ['Réseaux sociaux', 'Bouche à oreille', 'Site web', 'Publicité', 'Salon / Forum étudiant'];
        foreach ($moyensParDefaut as $ordre => $libelle) {
            MoyenConnaissance::firstOrCreate(['libelle' => $libelle], ['actif' => true, 'ordre' => $ordre]);
        }

        // Quel champ sert d'identifiant de dossier affiché dans le back-office :
        // 'code' (comportement actuel, par défaut) ou 'numero_bordereau'.
        Configuration::firstOrCreate(
            ['key' => 'identifiant_dossier_source'],
            [
                'value' => 'code',
                'type' => 'select',
                'valueKey' => 'Identifiant de dossier affiché',
                'options' => 'code|Code de convocation,numero_bordereau|Numéro de bordereau',
            ]
        );

        // Types de diplôme de départ : reproduisent le comportement actuel, où
        // mention/série/numéro de table/année sont exigés quel que soit le diplôme
        // (aucune conditionnalité n'existe encore dans le code actuel).
        $typesParDefaut = ['BAC', 'Licence', 'Master', 'BTS'];
        $champsParDefaut = ['mention_bac', 'serie', 'numero_table', 'annee_bac'];

        foreach ($typesParDefaut as $ordre => $nom) {
            $type = TypeDiplome::firstOrCreate(['nom' => $nom], ['actif' => true, 'ordre' => $ordre]);

            foreach ($champsParDefaut as $champKey) {
                $type->champs()->firstOrCreate(
                    ['champ_key' => $champKey],
                    ['obligatoire' => true]
                );
            }
        }
    }
}
