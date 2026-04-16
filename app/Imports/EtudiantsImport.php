<?php

namespace App\Imports;

use App\Models\AnneeScolaire;
use App\Models\Etudiant;
use App\Models\Niveau;
use App\Models\Filiere;
use App\Models\EtudiantGroup;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;

use Carbon\Carbon;
use Str;
use Illuminate\Support\Facades\Log;
use App\Services\FraisEtudiantService;

class EtudiantsImport implements
    ToCollection,
    WithHeadingRow,
    WithChunkReading,
    WithBatchInserts,
    WithEvents,
    ShouldQueue
{
    use Importable, RegistersEventListeners;

    private int $importedCount = 0;
    private int $skippedCount = 0;
    private int $updatedCount = 0;
    private array $errors = [];

    private array $filiereCache = [];
    private array $niveauCache = [];
    private array $groupCache = [];

    private array $existingMatricules = [];
    private array $existingEmails = [];

    private int $anneeScolaireId;
    private int $roleEtudiantId;
    private FraisEtudiantService $fraisService;





    /**
     * Constructor - Initialisation
     */
    public function __construct()
    {
        $this->anneeScolaireId = AnneeScolaire::where('active', true)->first()->getAttribute('id');
        $this->roleEtudiantId = DB::table('roles')
            ->where('slug', 'etudiant')
            ->value('id');
        $this->fraisService = new FraisEtudiantService();
    }
    /**
     * Traitement par collection (plus rapide que ToModel)
     */
    public function collection(Collection $rows)
    {
        $this->loadExistingStudentsIfNeeded();
        // Préparer les données pour insertion batch
        $etudiantsBatch = [];
        $etudiantGroupsBatch = [];
        $now = now();

        foreach ($rows as $index => $row) {
            try {
                // Vérifier si la ligne est vide
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                // Extraire les données
                $nom = $this->cleanString($row['nom'] ?? '');
                $prenom = $this->cleanString($row['prenoms'] ?? '');
                $matricule = $this->cleanString($row['numero_matricule'] ?? $row['numero matricule'] ?? '');
                $filiereNom = $this->cleanString($row['nom_de_la_filiere'] ?? $row['nom de la filiere'] ?? '');
                $niveauNom = $this->cleanString($row['niveau'] ?? '');

                // Validation des champs obligatoires
                if (!$nom || !$prenom || !$matricule || !$filiereNom || !$niveauNom) {
                    $this->skippedCount++;
                    $this->addError($index + 2, 'Champs obligatoires manquants', $matricule);
                    continue;
                }

                // Vérifier doublon matricule
                // if (in_array($matricule, $this->existingMatricules)) {
                //     $this->skippedCount++;
                //     $this->addError($index + 2, 'Matricule déjà existant', $matricule);
                //     continue;
                // }

                // Le matricule existe peut-être déjà, mais on continue pour permettre l'UPSERT et la mise à jour des frais

                // Gérer la filière (avec cache)
                $filiereId = $this->getFiliereId($filiereNom);
                if (!$filiereId) {
                    $this->skippedCount++;
                    $this->addError($index + 2, "Filière '$filiereNom' non trouvée/créée", $matricule);
                    continue;
                }

                // Gérer le niveau (avec cache)
                $niveauId = $this->getNiveauId($niveauNom);
                if (!$niveauId) {
                    $this->skippedCount++;
                    $this->addError($index + 2, "Niveau '$niveauNom' non trouvé/créé", $matricule);
                    continue;
                }

                // Chercher le group_id (avec cache)
                $groupId = $this->getGroupId($filiereId, $niveauId);
                if (!$groupId) {
                    $this->skippedCount++;
                    $this->addError($index + 2, "Aucun groupe pour la filière", $matricule);
                    continue;
                }

                // Gérer le sexe
                $sexeExcel = $this->cleanString($row['sexe'] ?? '');
                $genre = $this->determineGenre($sexeExcel);

                // Gérer la date de naissance
                $dateNaissance = $this->parseDate($row['date_de_naissance'] ?? $row['date de naissance'] ?? '');

                // Gérer les autres champs
                $tel = $this->cleanString($row['contact'] ?? '');
                $nation = $this->cleanString($row['nationalite'] ?? '');
                $cni = $this->cleanString($row['numero_de_cni'] ?? $row['numero de cni'] ?? '');

                // Générer email unique
                // $email = $this->generateUniqueEmail($prenom, $nom, $matricule);
                $emailFromExcel = $this->cleanString($row['email'] ?? '');

                // Si email fourni dans Excel → on l’utilise
                if (!empty($emailFromExcel)) {
                    $email = $emailFromExcel;
                } else {
                    // Sinon on génère automatiquement
                    $email = $this->generateCustomEmail($prenom, $nom, $matricule);
                }

                // Créer le slug
                $slug = Str::uuid();

                // Préparer l'étudiant pour insertion batch
                $etudiantsBatch[] = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'tel' => $tel,
                    'matricule' => $matricule,
                    'genre' => $genre,
                    'date_naissance' => $dateNaissance,
                    'nationalite' => $nation,
                    'annee_admission' => now()->year,
                    'email' => $email,
                    'slug' => $slug,
                    'password' => Hash::make('password'),
                    // 'created_at' => $now,
                    // 'updated_at' => $now,
                ];

                // Stocker l'association pour etudiant_group
                // $etudiantGroupsBatch[] = [
                //     'filiere_id' => $filiereId,
                //     'niveau_id' => $niveauId,
                //     'group_id' => $groupId,
                //     'annee_scolaire_id' => AnneeScolaire::where('active', true)->first()->getAttribute('id'),
                //     'matricule_temp' => $matricule, // Pour liaison après insertion
                //     'created_at' => $now,
                //     'updated_at' => $now,
                // ];

                // Stocker l'association pour etudiant_group avec le matricule comme clé
                $etudiantGroupsBatch[$matricule] = [
                    'filiere_id' => $filiereId,
                    'niveau_id' => $niveauId,
                    'group_id' => $groupId,
                    'annee_scolaire_id' => $this->anneeScolaireId, // Utilisez la propriété déjà définie
                    'matricule' => $matricule, // Changé de matricule_temp à matricule
                    // 'created_at' => $now,
                    // 'updated_at' => $now,
                ];



                // Mettre à jour les caches
                $this->existingMatricules[] = $matricule;
                $this->existingEmails[] = $email;

                $this->importedCount++;

                // Insertion par batch pour éviter trop de requêtes
                if (count($etudiantsBatch) >= 120) {
                    $this->insertBatch($etudiantsBatch, $etudiantGroupsBatch);
                    $etudiantsBatch = [];
                    $etudiantGroupsBatch = [];
                }
            } catch (\Exception $e) {
                $this->skippedCount++;
                $this->addError($index + 2, 'Erreur: ' . $e->getMessage(), $matricule ?? 'N/A');
                continue;
            }
        }

        // Insérer les derniers éléments
        if (!empty($etudiantsBatch)) {
            $this->insertBatch($etudiantsBatch, $etudiantGroupsBatch);
        }
    }

    private function loadExistingStudentsIfNeeded()
    {
        if (!empty($this->existingMatricules)) {
            return;
        }

        Etudiant::select('matricule', 'email')
            ->chunk(1000, function ($etudiants) {
                foreach ($etudiants as $e) {
                    $this->existingMatricules[] = $e->matricule;
                    $this->existingEmails[] = $e->email;
                }
            });
    }


    /**
     * Insérer un batch d'étudiants
     */
    // private function insertBatch(array &$etudiantsBatch, array &$etudiantGroupsBatch)
    // {
    //     DB::transaction(function () use (&$etudiantsBatch, &$etudiantGroupsBatch) {
    //         // Insertion des étudiants
    //         $insertedIds = [];
    //         foreach (array_chunk($etudiantsBatch, 50) as $chunk) {
    //             DB::table('etudiants')->insert($chunk);

    //             // Récupérer les IDs insérés
    //             $matricules = array_column($chunk, 'matricule');
    //             $etudiants = Etudiant::whereIn('matricule', $matricules)
    //                 ->get(['id', 'matricule']);

    //             foreach ($etudiants as $etudiant) {
    //                 $insertedIds[$etudiant->matricule] = $etudiant->id;
    //             }
    //         }

    //         // Préparer et insérer les etudiant_groups
    //         $etudiantGroupsFinal = [];
    //         foreach ($etudiantGroupsBatch as $group) {
    //             if (isset($insertedIds[$group['matricule_temp']])) {
    //                 $etudiantGroupsFinal[] = [
    //                     'etudiant_id' => $insertedIds[$group['matricule_temp']],
    //                     'group_id' => $group['group_id'],
    //                     'filiere_id' => $group['filiere_id'],
    //                     'niveau_id' => $group['niveau_id'],
    //                     'annee_scolaire_id' => AnneeScolaire::where('active', true)->first()->getAttribute('id'),

    //                 ];
    //             }
    //         }

    //         if (!empty($etudiantGroupsFinal)) {
    //             foreach (array_chunk($etudiantGroupsFinal, 50) as $chunk) {
    //                 DB::table('etudiant_group')->insert($chunk);
    //             }
    //         }

    //         $rolesUsersBatch = [];
    //         foreach ($insertedIds as $etudiantId) {
    //             $rolesUsersBatch[] = [
    //                 'user_id'   => $etudiantId,
    //                 'user_type' => 'App\\Models\\Etudiant',
    //                 'role_id'   => $this->roleEtudiantId,
    //             ];
    //         }

    //         if (!empty($rolesUsersBatch)) {
    //             DB::table('role_user')->insert($rolesUsersBatch);
    //         }


    //         // Vider les batches
    //         $etudiantsBatch = [];
    //         $etudiantGroupsBatch = [];
    //     });
    // }

    /**
     * NOUVELLE MÉTHODE: Traite un batch avec UPSERT
     */
    /**
     * Traite un batch avec UPSERT
     */
    private function insertBatch(array &$etudiantsBatch, array &$etudiantGroupsBatch)
    {
        DB::transaction(function () use (&$etudiantsBatch, &$etudiantGroupsBatch) {

            // 1. UPSERT des étudiants
            $etudiantsData = array_values($etudiantsBatch);

            Etudiant::upsert(
                $etudiantsData,
                ['matricule'],
                [
                    'nom',
                    'prenom',
                    'tel',
                    'genre',
                    'date_naissance',
                    'nationalite',
                    'annee_admission',
                    'email',
                    'slug',
                    'password',
                    // 'updated_at'
                ]
            );

            // 2. Récupérer les IDs des étudiants
            $matricules = array_column($etudiantsBatch, 'matricule');
            $etudiants = Etudiant::whereIn('matricule', $matricules)
                ->get(['id', 'matricule', 'genre'])
                ->keyBy('matricule');

            // 3. Compter les mises à jour
            foreach ($matricules as $matricule) {
                if (in_array($matricule, $this->existingMatricules)) {
                    $this->updatedCount++;
                }
            }

            // 4. Préparer et insérer les etudiant_groups
            $etudiantGroupsFinal = [];
            foreach ($etudiantGroupsBatch as $group) { // ICI: $group directement, pas de clé
                if (isset($etudiants[$group['matricule']])) { // Utilisez matricule au lieu de matricule_temp
                    $existing = DB::table('etudiant_group')
                        ->where('etudiant_id', $etudiants[$group['matricule']]->id)
                        ->where('annee_scolaire_id', $this->anneeScolaireId)
                        ->first();

                    $groupData = [
                        'etudiant_id' => $etudiants[$group['matricule']]->id,
                        'group_id' => $group['group_id'],
                        'filiere_id' => $group['filiere_id'],
                        'niveau_id' => $group['niveau_id'],
                        'annee_scolaire_id' => $this->anneeScolaireId,
                        // 'updated_at' => now(),
                    ];

                    if ($existing) {
                        DB::table('etudiant_group')
                            ->where('id', $existing->id)
                            ->update($groupData);
                    } else {
                        // $groupData['created_at'] = now();
                        $etudiantGroupsFinal[] = $groupData;
                    }
                }
            }

            // Insérer les nouveaux etudiant_groups
            if (!empty($etudiantGroupsFinal)) {
                foreach (array_chunk($etudiantGroupsFinal, 50) as $chunk) {
                    DB::table('etudiant_group')->insert($chunk);
                }
            }

            // 5. Gérer les rôles
            $nouveauxEtudiantsIds = [];
            foreach ($matricules as $matricule) {
                if (!in_array($matricule, $this->existingMatricules)) {
                    $nouveauxEtudiantsIds[] = $etudiants[$matricule]->id;
                }
            }

            if (!empty($nouveauxEtudiantsIds)) {
                $rolesUsersBatch = [];
                foreach ($nouveauxEtudiantsIds as $etudiantId) {
                    $roleExists = DB::table('role_user')
                        ->where('user_id', $etudiantId)
                        ->where('user_type', 'App\\Models\\Etudiant')
                        ->where('role_id', $this->roleEtudiantId)
                        ->exists();

                    if (!$roleExists) {
                        $rolesUsersBatch[] = [
                            'user_id' => $etudiantId,
                            'user_type' => 'App\\Models\\Etudiant',
                            'role_id' => $this->roleEtudiantId,
                        ];
                    }
                }

                if (!empty($rolesUsersBatch)) {
                    DB::table('role_user')->insert($rolesUsersBatch);
                }
            }

            // 6. Assigner les FRAIS par défaut (avec rechargement complet pour éviter les champs manquants)
            foreach ($etudiants as $etudiant) {
                try {
                    // Recharger l'étudiant avec toutes ses relations pour garantir que le genre et les groupes sont disponibles
                    $etudiantComplet = Etudiant::with(['etudiantGroups' => function($q) {
                        $q->where('annee_scolaire_id', $this->anneeScolaireId);
                    }])->find($etudiant->id);

                    if ($etudiantComplet) {
                        $this->fraisService->assignDefaultFrais($etudiantComplet, $this->anneeScolaireId);
                    }
                } catch (\Exception $e) {
                    Log::warning("Import: Impossible d'assigner les frais à l'étudiant ID={$etudiant->id}: " . $e->getMessage());
                }
            }

            // 7. Enregistrer le paiement des frais d'inscription
            // On récupère le frais d'inscription dont le statut est actif et qui correspond à l'année active
            $fraisInscriptionActif = \App\Models\FraisInscription::where('active', true)
                ->where('annee_scolaire_id', $this->anneeScolaireId)
                ->first();
            
            if ($fraisInscriptionActif) {
                $paiementsBatch = [];
                $now = now();
                
                foreach ($etudiants as $etudiant) {
                    // Vérification de sécurité contre les doublons
                    // On vérifie que l'étudiant n'a pas déjà payé ce frais d'inscription spécifique (qui inclut l'année)
                    $paiementExiste = \Illuminate\Support\Facades\DB::table('paiements')
                        ->where('etudiant_id', $etudiant->id)
                        ->where('payable_type', 'App\\Models\\FraisInscription')
                        ->where('payable_id', $fraisInscriptionActif->id)
                        ->exists();

                    if (!$paiementExiste) {
                        $paiementsBatch[] = [
                            'etudiant_id'   => $etudiant->id,
                            'montant'       => $fraisInscriptionActif->montant,
                            'mode_paiement' => 'especes', // Mode par défaut
                            'nature_paiement' => 'inscription',
                            'reference'     => 'INS-' . $etudiant->matricule . '-' . time(),
                            'status'        => 'valide', // Statut validé demandé
                            'payable_type'  => 'App\\Models\\FraisInscription',
                            'payable_id'    => $fraisInscriptionActif->id,
                            'date_paiement' => $now,      // Champ obligatoirement requis par la base de données
                            'created_at'    => $now,
                            'updated_at'    => $now,
                        ];
                    }
                }

                if (!empty($paiementsBatch)) {
                    foreach (array_chunk($paiementsBatch, 50) as $chunk) {
                        \Illuminate\Support\Facades\DB::table('paiements')->insert($chunk);
                    }
                }
            }

            // Mettre à jour les caches
            foreach ($matricules as $matricule) {
                if (!in_array($matricule, $this->existingMatricules)) {
                    $this->existingMatricules[] = $matricule;
                }
            }

            // Vider les batches
            $etudiantsBatch = [];
            $etudiantGroupsBatch = [];
        });
    }
    /**
     * Vérifier si une ligne est vide
     */
    private function isEmptyRow($row)
    {
        $requiredFields = ['nom', 'prenoms', 'numero_matricule', 'numero matricule'];

        foreach ($requiredFields as $field) {
            if (isset($row[$field]) && trim($row[$field]) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Nettoyer une chaîne
     */
    private function cleanString($value)
    {
        if (is_null($value) || $value === '') {
            return '';
        }

        return trim(strval($value));
    }

    /**
     * Obtenir l'ID de la filière (avec cache)
     */
    private function getFiliereId($filiereNom)
    {
        if (empty($filiereNom)) {
            return null;
        }

        $key = md5(strtolower($filiereNom));

        if (!isset($this->filiereCache[$key])) {
            $filiere = Filiere::firstOrCreate(['nom' => $filiereNom]);
            $this->filiereCache[$key] = $filiere->id;
        }

        return $this->filiereCache[$key];
    }

    /**
     * Obtenir l'ID du niveau (avec cache)
     */
    private function getNiveauId($niveauNom)
    {
        if (empty($niveauNom)) {
            return null;
        }

        $key = md5(strtolower($niveauNom));

        if (!isset($this->niveauCache[$key])) {
            $niveau = Niveau::firstOrCreate(['libelle' => $niveauNom]);
            $this->niveauCache[$key] = $niveau->id;
        }

        return $this->niveauCache[$key];
    }

    /**
     * Obtenir l'ID du groupe (avec cache)
     */
    private function getGroupId(int $filiereId, int $niveauId)
    {
        if (!$filiereId || !$niveauId) {
            return null;
        }

        $cacheKey = $filiereId . '_' . $niveauId;

        if (!isset($this->groupCache[$cacheKey])) {

            $group = DB::table('groups')
                ->join('filiere_group', 'groups.id', '=', 'filiere_group.group_id')
                ->where('filiere_group.filiere_id', $filiereId)
                ->where('groups.niveau_id', $niveauId)
                ->select('groups.id')
                ->first();

            $this->groupCache[$cacheKey] = $group ? $group->id : null;
        }

        return $this->groupCache[$cacheKey];
    }


    /**
     * Déterminer le genre
     */
    private function determineGenre($sexe)
    {
        if (empty($sexe)) {
            return 'Féminin';
        }

        $sexeLower = strtolower($sexe);

        if (
            str_contains($sexeLower, 'masc') ||
            $sexeLower === 'm' ||
            $sexeLower === 'homme' ||
            $sexeLower === 'h' ||
            $sexeLower === 'masculin'

        ) {
            return 'Masculin';
        }

        return 'Féminin';
    }

    /**
     * Parser une date
     */
    private function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            if (is_numeric($dateString) && $dateString > 25569) {
                // Date Excel
                return Carbon::createFromTimestamp(($dateString - 25569) * 86400);
            }

            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Générer un email unique
     */
    private function generateUniqueEmail($prenom, $nom, $matricule)
    {
        $prenomClean = strtolower(preg_replace('/[^a-z0-9]/', '.', iconv('UTF-8', 'ASCII//TRANSLIT', $prenom)));
        $nomClean = strtolower(preg_replace('/[^a-z0-9]/', '.', iconv('UTF-8', 'ASCII//TRANSLIT', $nom)));

        $emailBase = $prenomClean . '.' . $nomClean . '@etudiant.exemple.com';

        // Si l'email n'existe pas déjà
        if (!in_array($emailBase, $this->existingEmails)) {
            return $emailBase;
        }

        // Ajouter le matricule
        $emailWithMatricule = $prenomClean . '.' . $nomClean . '.' . $matricule . '@etudiant.exemple.com';

        // Si encore existe, ajouter un timestamp
        if (in_array($emailWithMatricule, $this->existingEmails)) {
            return $prenomClean . '.' . $nomClean . '.' . $matricule . '.' . time() . '@etudiant.exemple.com';
        }

        return $emailWithMatricule;
    }

    /**
     * Récupérer l'ID de l'année scolaire
     */
    private function getAnneeScolaireId()
    {
        if (function_exists('injectAnneeScolaireId')) {
            return injectAnneeScolaireId();
        }

        $anneeScolaire = DB::table('annee_scolaires')
            ->where('actif', true)
            ->first();

        if ($anneeScolaire) {
            return $anneeScolaire->id;
        }

        // Créer une année scolaire par défaut
        $currentYear = now()->year;
        $nextYear = $currentYear + 1;

        return DB::table('annee_scolaires')->insertGetId([
            'libelle' => $currentYear . '-' . $nextYear,
            'debut' => now()->startOfYear(),
            'fin' => now()->addYear()->endOfYear(),
            'actif' => true,
            // 'created_at' => now(),
            // 'updated_at' => now(),
        ]);
    }

    /**
     * Ajouter une erreur
     */
    private function addError($line, $reason, $matricule = null)
    {
        $this->errors[] = [
            'ligne' => $line,
            'raison' => $reason,
            'matricule' => $matricule ?? 'N/A'
        ];

        // Limiter le nombre d'erreurs stockées pour éviter l'explosion mémoire
        if (count($this->errors) > 1000) {
            array_shift($this->errors);
        }
    }

    /**
     * Configuration du chunk reading
     */
    public function chunkSize(): int
    {
        return 500; // Lire 500 lignes à la fois
    }

    /**
     * Configuration du batch insert
     */
    public function batchSize(): int
    {
        return 120; // Insérer par lots de 120
    }

    /**
     * Événement après l'import
     */
    // public static function afterImport(AfterImport $event)
    // {
    //     $import = $event->getConcernable();

    //     Log::info('Import étudiants terminé', [
    //         'imported' => $import->importedCount,
    //         'skipped' => $import->skippedCount,
    //         'errors_count' => count($import->errors)
    //     ]);
    // }

    public static function afterImport(AfterImport $event)
    {
        $import = $event->getConcernable();

        Log::info('Import étudiants terminé', [
            'nouveaux' => $import->importedCount - $import->updatedCount,
            'mis_a_jour' => $import->updatedCount,
            'ignores' => $import->skippedCount,
            'errors_count' => count($import->errors)
        ]);
    }

    /**
     * Événement en cas d'échec
     */
    public static function importFailed(ImportFailed $event)
    {
        $e = $event->getException();
        Log::error('Import étudiants échoué', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    /**
     * Getters pour les résultats
     */
    // public function getResults()
    // {
    //     return [
    //         'imported' => $this->importedCount,
    //         'skipped' => $this->skippedCount,
    //         'errors' => $this->errors,
    //         'has_errors' => !empty($this->errors)
    //     ];
    // }

    public function getResults()
    {
        return [
            'nouveaux' => $this->importedCount - $this->updatedCount,
            'mis_a_jour' => $this->updatedCount,
            'ignores' => $this->skippedCount,
            'errors' => $this->errors,
            'has_errors' => !empty($this->errors)
        ];
    }

    /**
     * Getter pour les erreurs
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Getter pour le compte
     */
    public function getImportedCount()
    {
        return $this->importedCount;
    }

    /**
     * Générer email si colonne absente
     * Format : première lettre du nom + prénom
     */
    private function generateCustomEmail($prenom, $nom, $matricule)
    {
        $prenomClean = strtolower(preg_replace('/[^a-z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $prenom)));
        $nomClean = strtolower(preg_replace('/[^a-z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $nom)));

        // Première lettre du nom + prénom
        $emailBase = substr($nomClean, 0, 1) . $prenomClean . '@etudiant.exemple.com';

        // Vérifier unicité
        if (!in_array($emailBase, $this->existingEmails)) {
            return $emailBase;
        }

        // Si déjà existant → ajouter matricule
        $emailWithMatricule = substr($nomClean, 0, 1) . $prenomClean . $matricule . '@etudiant.exemple.com';

        if (!in_array($emailWithMatricule, $this->existingEmails)) {
            return $emailWithMatricule;
        }

        // Dernier fallback
        return substr($nomClean, 0, 1) . $prenomClean . $matricule . time() . '@etudiant.exemple.com';
    }
}
