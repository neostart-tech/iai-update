<?php

use App\Http\Controllers\Admin\NegociationController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ReclamationController;
use App\Http\Controllers\Api\Admin\CandidaturePresenceController;
use App\Http\Controllers\Api\CommuniqueController;
use App\Http\Controllers\Api\Etudiant\MonParcoursController;
use App\Http\Controllers\Api\ExamComplexResponseController;
use App\Http\Controllers\Api\ExamPartController;
use App\Http\Controllers\Api\ExamQuestionController;
use App\Http\Controllers\Api\ExamQuestionOptionController;
use App\Http\Controllers\Api\ExamSessionController;
use App\Http\Controllers\Api\ExamSubmissionController;
use App\Http\Controllers\Api\SemoaCallBackController;
use App\Http\Controllers\Api\Support\CategoryController;
use App\Http\Controllers\Api\Support\TicketController as SupportTicketController;
use App\Http\Controllers\Api\Support\SupportMessageController;
use App\Http\Controllers\BourseController;
use App\Http\Controllers\BroadcastAuthController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DashboardPaiementController;
use App\Http\Controllers\EnseignantPresenceController;
use App\Http\Controllers\EnseignantPresenceExportController;
use App\Http\Controllers\EtudiantSituationController;
use App\Http\Controllers\messageController;
use App\Http\Controllers\MyCalendarController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\PaiementExportController;
use App\Http\Controllers\PaiementGlobalController;
use App\Http\Controllers\PlanPaiementController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\PresenceExportController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TranchePaiementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    $user = $request->user();
    
    // Charger les rôles normalement
    $user->load('roles');
    
    // Si c'est un étudiant et qu'il n'a pas de rôles (cas de la prod), on force le rôle 'etudiant'
    if ($user instanceof \App\Models\Etudiant) {
        if ($user->roles->isEmpty()) {
            $studentRole = new \App\Models\Role([
                'id' => 1,
                'nom' => 'Etudiant',
                'slug' => 'etudiant',
                'active' => 1
            ]);
            $user->setRelation('roles', collect([$studentRole]));
        } else {
            // Toujours s'assurer que le rôle etudiant est présent pour les etudiants
            $studentRole = new \App\Models\Role([
                'id' => 1,
                'nom' => 'Etudiant',
                'slug' => 'etudiant',
                'active' => 1
            ]);
            $user->setRelation('roles', collect([$studentRole]));
        }
    }
    
    return $user;
});

Route::middleware('auth:sanctum')->get('/user/fiscalite', function (Request $request) {
    $user = $request->user();
    return response()->json([
        'nationalite' => $user->nationalite,
        'nif' => $user->nif,
        'identity_document_url' => $user->identity_document_url,
        'nif_document_url' => $user->nif_document_url,
        'diploma_document_url' => $user->diploma_document_url,
        'cv_document_url' => $user->cv_document_url,
    ]);
});




Broadcast::routes(['middleware' => ['auth:sanctum']]);


Route::get('load-calendar', MyCalendarController::class)->middleware('auth:sanctum');




Route::post('administration/candidature/presence', CandidaturePresenceController::class)->name('admin.candidatures.presence');

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(ReclamationController::class)->prefix('reclamations')->group(function () {
        Route::post('{note}/enregistrer-une-reclamation', 'store');
        Route::get('{note}/detail-une-reclamation', 'getNoteReclation');
        Route::get('/mes-reclamations',  'mesReclamations');
        Route::delete('{reclamation}/annuler', 'annuler');
        Route::get('{reclamation}', 'show');
        Route::get('/get-count-reclamations', 'getCountReclamations');
    });

    Route::controller(NoteController::class)->prefix('mes-notes')->name('notes.')->group(function () {
        Route::get('/', 'index')->name('index');
    });

    Route::controller(AnnouncementController::class)->prefix('etudiant/annonces')->name('announcements.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::get('{announcement}/details', 'show')->name('show');
        Route::post('{announcement}/postuler-a-une-offre', 'applyToAnnouncement')->name('apply-to-announcement');
        Route::get('mes-depots', 'myApplications')->name('my-applications');
    });

    Route::prefix('support')->middleware('auth:sanctum')->group(function () {
    
    // Catégories (accessibles à tous les utilisateurs connectés)
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    
    // Tickets
    Route::get('tickets', [SupportTicketController::class, 'index']);
    Route::post('tickets', [SupportTicketController::class, 'store']);
    Route::get('tickets/{ticket}', [SupportTicketController::class, 'show']);
    
    // Actions sur tickets (réservées au staff support dans le contrôleur)
    Route::post('tickets/{ticket}/assign', [SupportTicketController::class, 'assign']);
    Route::put('tickets/{ticket}/status', [SupportTicketController::class, 'updateStatus']);
    
    // Évaluation (accessible par le créateur du ticket)
    Route::post('tickets/{ticket}/rate', [SupportTicketController::class, 'rate']);
    
    // Messages
    Route::get('tickets/{ticket}/messages', [SupportMessageController::class, 'index']);
    Route::post('tickets/{ticket}/messages', [SupportMessageController::class, 'store']);
    Route::put('messages/{message}', [SupportMessageController::class, 'update']);
    Route::delete('messages/{message}', [SupportMessageController::class, 'destroy']);
});



    // Route::controller(PresenceController::class)->prefix('presence')->group(function () {
    //     Route::get('/liste-des-cours', 'index');
    //     Route::get('/get-my-course', 'mesCours');
    //     Route::post('/save-student-presence', 'enregistrerAbsences');
    //     Route::post('/save-enseignant-presence', 'enregistrerPresenceEnseignant');
    // });


    //     Route::get('/presences/export/{emploiDuTempsId}', [PresenceExportController::class, 'exportByCours']);
    //     Route::post('/presences/export/filtered', [PresenceExportController::class, 'exportWithFilters']);

    // Route::prefix('etudiant')->group(function () {
    //     Route::get('/mes-communiques', [CommuniqueController::class, 'mesCommuniques']);
    //     Route::get('/communiques/{notification}', [CommuniqueController::class, 'show']);
    //     Route::post('/communiques/{notification}/lu', [CommuniqueController::class, 'marquerCommeLu']);
    //     Route::post('/communiques/tout/lu', [CommuniqueController::class, 'marquerToutLu']);
    // });

    // // Routes pour l'administration
    // Route::prefix('admin')->group(function () {
    //     Route::post('/communiques', [CommuniqueController::class, 'store']);
    //     Route::get('/communiques/statistiques', [CommuniqueController::class, 'statistiques']);
    //     Route::get('/communiques/options-ciblage', [CommuniqueController::class, 'optionsCiblage']);
    // });
    Route::controller(PresenceController::class)->prefix('presence')->group(function () {

        // ========================================
        // ROUTES EXISTANTES (conservées)
        // ========================================

        // Liste tous les cours
        Route::get('/liste-des-cours', 'index');

        // Cours de l'utilisateur connecté
        Route::get('/get-my-course', 'mesCours');

        // Enregistrer les présences étudiants
        Route::post('/save-student-presence', 'enregistrerAbsences');

        // Enregistrer présence enseignant
        Route::post('/save-enseignant-presence', 'enregistrerPresenceEnseignant');


        // ========================================
        // ROUTES POUR LES SÉANCES
        // ========================================

        // Liste des séances d'un cours
        Route::get('/cours/{coursId}/seances', 'listeSeances');

        // ⚠️ ROUTE IMPORTANTE POUR CHARGER LES ÉTUDIANTS AVEC DATE ⚠️
        Route::get('/cours/{coursId}/etudiants', 'getEtudiantsAvecPresences');

        // Étudiants d'une séance spécifique
        Route::get('/seance/{seanceId}/etudiants', 'getEtudiantsParSeance');

        // Valider une séance
        Route::post('/seance/{seanceId}/valider', 'validerSeance');

        // Annuler une séance
        Route::put('/seance/{seanceId}/annuler', 'annulerSeance');


        // ========================================
        // ROUTES POUR LE COMPORTEMENT
        // ========================================

        // Mettre à jour le comportement d'un étudiant pour une présence
        Route::put('/presence/{presenceId}/comportement', 'updateComportement');

        // Historique des comportements d'un étudiant
        Route::get('/etudiant/{etudiantId}/comportements', 'getComportementsEtudiant');


        // ========================================
        // ROUTES POUR LES STATISTIQUES
        // ========================================

        // Statistiques globales
        Route::get('/statistiques/globales', 'statistiquesGlobales');

        // Statistiques par cours
        Route::get('/statistiques/cours/{coursId}', 'statistiquesCours');

        // Statistiques par étudiant
        Route::get('/statistiques/etudiant/{etudiantId}', 'statistiquesEtudiant');

        // Statistiques par période (avec params: debut, fin, groupe_id)
        Route::get('/statistiques/periodiques', 'statistiquesPeriodiques');


        // ========================================
        // ROUTES POUR L'HISTORIQUE
        // ========================================

        // Historique complet d'un étudiant
        Route::get('/historique/etudiant/{etudiantId}', 'historiqueEtudiant');

        // Historique d'un cours
        Route::get('/historique/cours/{coursId}', 'historiqueCours');

        // Détail d'une séance
        Route::get('/historique/seance/{seanceId}', 'historiqueSeance');


        // ========================================
        // ROUTES POUR LES EXPORTS
        // ========================================

        // Export Excel des présences d'un cours
        Route::post('/export/cours/{coursId}', 'exportPresencesCours');

        // Export Excel des présences d'un étudiant
        Route::post('/export/etudiant/{etudiantId}', 'exportPresencesEtudiant');

        // Export Excel des présences d'une séance
        Route::post('/export/seance/{seanceId}', 'exportPresencesSeance');


        // ========================================
        // ROUTES POUR LES JUSTIFICATIFS
        // ========================================

        // Upload d'un justificatif pour une présence
        Route::post('/presence/{presenceId}/justificatif', 'uploadJustificatif');

        // Liste des justificatifs en attente de validation
        Route::get('/justificatifs/en-attente', 'justificatifsEnAttente');

        // Valider un justificatif
        Route::put('/justificatif/{justificatifId}/valider', 'validerJustificatif');

        // Refuser un justificatif
        Route::put('/justificatif/{justificatifId}/refuser', 'refuserJustificatif');


        // ========================================
        // ROUTES POUR LES ALERTES
        // ========================================

        // Liste des alertes
        Route::get('/alertes', 'getAlertes');

        // Traiter une alerte
        Route::put('/alerte/{alerteId}/traiter', 'traiterAlerte');

        // Étudiants à surveiller
        Route::get('/etudiants/a-surveiller', 'etudiantsASurveiller');


        // ========================================
        // ROUTES POUR LES CONSEILS DE CLASSE
        // ========================================

        // Fiche pour conseil de classe
        Route::get('/conseil/classe/{classeId}', 'getFicheConseil');

        // Fiche individuelle pour un étudiant
        Route::get('/conseil/etudiant/{etudiantId}', 'getFicheEtudiantConseil');

        // Générer synthèse PDF pour le conseil
        Route::post('/conseil/synthese', 'genererSyntheseConseil');
    });


    Route::get('/export/cours/{emploiDuTempsId}', [EnseignantPresenceExportController::class, 'exportForCours']);
    Route::post('/export/filtered', [EnseignantPresenceExportController::class, 'exportFiltered']);
    Route::get('/export/recap-uv/{enseignantId}/{uvId}', [EnseignantPresenceExportController::class, 'exportRecapUV']);

    Route::controller(BourseController::class)->prefix('bourse')->group(function () {
        Route::get('/liste', 'index')->name('bourse.index');
        Route::post('/store', 'store')->name('bourse.store');
        Route::get('/{bourse}', 'show')->name('bourse.show');
        Route::get('/{bourse}/etudiants', 'getEtudiantsBourse')->name('bourse.etudiants');
        Route::put('/{bourse}/update', 'update')->name('bourse.update');
        Route::delete('/{bourse}/delete', 'destroy')->name('bourse.delete');
        Route::post('/affecter', 'affecter')->name('bourse.affecter');
        Route::post('/retirer', 'retirer')->name('bourse.retirer');
        Route::get('{etudiant}/etudiant', 'getBoursesByEtudiant');
    });

    Route::controller(PlanPaiementController::class)->prefix('plan-de-paiement')->group(function () {
        Route::get('/liste', 'index');
        Route::post('/store', 'store');
        Route::get('/{plan}', 'show');
        // Route::get('/{plan}/etudiants', 'getEtudiantsBourse')->name('bourse.etudiants');
        Route::put('/{plan}/update', 'update');
        Route::delete('/{plan}/delete', 'destroy');
        // Route::post('/affecter', 'affecter')->name('bourse.affecter');
        // Route::post('/retirer', 'retirer')->name('bourse.retirer');
    });

    Route::controller(TranchePaiementController::class)->prefix('tranche-de-paiement')->group(function () {
        Route::get('/liste', 'index');
        Route::post('/store', 'store');
        Route::get('/{frais}', 'show');
        // Route::get('/{plan}/etudiants', 'getEtudiantsBourse')->name('bourse.etudiants');
        Route::put('/{tranche}/update', 'update');
        Route::delete('/{tranche}/delete', 'destroy');
        // Route::post('/affecter', 'affecter')->name('bourse.affecter');
        // Route::post('/retirer', 'retirer')->name('bourse.retirer');
    });





    // Routes pour l'admin
    Route::controller(NegociationController::class)->prefix('admin')->group(function () {
        Route::get('negociations/dashboard', 'dashboard');
        Route::get('negociations/etudiant/{etudiantId}', 'getByEtudiant');
        Route::resource('negociations', NegociationController::class);
        Route::post('negociations/{id}/ajouter-paiement', 'ajouterPaiement')->name('negociations.ajouter-paiement');
    });

    Route::controller(PaiementController::class)->prefix('paiements')->group(function () {
        // Recherche d'étudiants
        Route::get('/rechercher-etudiants', 'rechercherEtudiants');

        // Informations de paiement
        Route::get('/infos/{etudiantId?}', 'getInfos');

        // Récapitulatif
        Route::get('/recap/{etudiantId?}', 'getRecap');

        // Historique des paiements
        Route::get('/historique/{etudiantId?}', 'getHistorique');

        // Effectuer un paiement
        Route::post('/store', 'store');
        Route::post('/', 'store');
        
        // Modifier un paiement
        Route::post('/{id}/update', 'update');
    });
    // Route::controller(PaiementGlobalController::class)->prefix('paiements')->group(function () {
    //     Route::get('rechercher-etudiant', 'rechercherEtudiant');
    //     Route::get('infos-etudiant', 'getInfosEtudiant');
    //     Route::post('global', 'store');
    //     Route::get('historique/{etudiantId}', 'historique');
    //     Route::get('recap/{etudiantId}', 'recap');

    // });

    Route::prefix('paiements/exports')->group(function () {
        Route::get('/niveaux', [PaiementExportController::class, 'getNiveaux']);
        Route::get('/filieres', [PaiementExportController::class, 'getFilieres']);
        Route::post('/paiements', [PaiementExportController::class, 'exportPaiements']);
        Route::post('/paiements/pdf', [PaiementExportController::class, 'exportPaiements']);
        Route::post('/paiements-data', [PaiementExportController::class, 'getExportData']);
    });

    Route::prefix('etudiants')->group(function () {
        Route::get('/situation', [EtudiantSituationController::class, 'index']);
        Route::get('/situation/statistiques', [EtudiantSituationController::class, 'statistiques']);
        Route::get('/situation/{id}', [EtudiantSituationController::class, 'show']);
        Route::get('/situation/export/csv', [EtudiantSituationController::class, 'exportCSV']);
        Route::post('/situation/bulk-status', [EtudiantSituationController::class, 'bulkUpdateStatut']);
        Route::put('/situation/{id}/statut', [EtudiantSituationController::class, 'updateStatut']);
    });






    Route::prefix('dashboard/paiements')->group(function () {
        // Statistiques globales
        Route::get('/statistiques', [DashboardPaiementController::class, 'getStatistiques']);

        // Statistiques par filière
        Route::get('/filiere/{filiereId}/statistiques', [DashboardPaiementController::class, 'getStatistiquesFiliere']);

        // Statistiques par niveau
        Route::get('/niveau/{niveauId}/statistiques', [DashboardPaiementController::class, 'getStatistiquesNiveau']);

        // Liste des étudiants en retard
        Route::get('/etudiants-en-retard', [DashboardPaiementController::class, 'getEtudiantsEnRetard']);

        // Historique des paiements
        Route::get('/historique', [DashboardPaiementController::class, 'getHistoriquePaiements']);

        // Export du dashboard
        Route::post('/export', [DashboardPaiementController::class, 'exportDashboard']);
    });


    Route::prefix('etudiant/parcours')->group(function () {
        Route::get('/', [MonParcoursController::class, 'getParcours']);
        Route::get('/paiements/{anneeId}', [MonParcoursController::class, 'getPaiementsParAnnee']);
        Route::get('/annee/{anneeId}', [MonParcoursController::class, 'getDetailsAnnee']);
    });


    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);
    Route::put('/conversations/{conversation}', [ConversationController::class, 'update']);
    Route::delete('/conversations/{conversation}', [ConversationController::class, 'destroy']);

    // Messages d'une conversation
    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'index']);
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store']);
    Route::get('/conversations/{conversation}/messages/{message}', [MessageController::class, 'show']);
    Route::delete('/conversations/{conversation}/messages/{message}', [MessageController::class, 'destroy']);
    Route::put('/conversations/{conversation}/messages/{message}', [MessageController::class, 'update']);
    // Pièces jointes des messages
    Route::prefix('conversations/{conversation}/messages/{message}/attachments')->group(function () {
        // Télécharger une pièce jointe
        Route::get('/{attachment}/download', [MessageController::class, 'downloadAttachment'])
            ->name('messages.attachments.download');

        // Prévisualiser une pièce jointe (images, PDFs)
        Route::get('/{attachment}/preview', [MessageController::class, 'previewAttachment'])
            ->name('messages.attachments.preview');

        // Optionnel: Supprimer une pièce jointe spécifique
        Route::delete('/{attachment}', [MessageController::class, 'deleteAttachment'])
            ->name('messages.attachments.destroy');
    });

    // Participants d'une conversation
    Route::get('/conversations/{conversation}/participants', [ConversationController::class, 'participants']);
    Route::post('/conversations/{conversation}/participants', [ConversationController::class, 'addParticipant']);
    Route::delete('/conversations/{conversation}/participants/{user}', [ConversationController::class, 'removeParticipant']);



    Route::put('/conversations/{conversation}/participants/{user}/role', [ConversationController::class, 'changeParticipantRole']);

    // Activer/désactiver le mode "seuls les admins peuvent parler"
    Route::post('/conversations/{conversation}/toggle-admin-only', [ConversationController::class, 'toggleAdminOnly']);

    // Vérifier si un utilisateur peut envoyer un message
    Route::get('/conversations/{conversation}/can-send-message', [ConversationController::class, 'canSendMessage']);

    Route::prefix('evaluations/{evaluationId}')->group(function () {
        Route::get('/parts', [ExamPartController::class, 'index']);
    });
    Route::apiResource('exam-parts', ExamPartController::class);
    Route::post('exam-parts/reorder', [ExamPartController::class, 'reorder']);

    // ==================== QUESTIONS ====================
    Route::prefix('exam-parts/{partId}')->group(function () {
        Route::get('/questions', [ExamQuestionController::class, 'index']);
    });
    Route::apiResource('exam-questions', ExamQuestionController::class);
    Route::post('exam-questions/reorder', [ExamQuestionController::class, 'reorder']);
    Route::patch('exam-questions/{id}/toggle-active', [ExamQuestionController::class, 'toggleActive']);

    // ==================== OPTIONS ====================
    Route::prefix('exam-questions/{questionId}')->group(function () {
        Route::get('/options', [ExamQuestionOptionController::class, 'index']);
    });
    Route::apiResource('exam-question-options', ExamQuestionOptionController::class);
    Route::post('exam-question-options/reorder', [ExamQuestionOptionController::class, 'reorder']);
    Route::patch('exam-question-options/{id}/mark-correct', [ExamQuestionOptionController::class, 'markCorrect']);

    // ==================== IA QUESTIONS ====================
    Route::post('exam-questions/generate-ai', [ExamQuestionController::class, 'aiGenerate']);
    Route::post('exam-questions/refine-ai', [ExamQuestionController::class, 'aiRefine']);

    // ==================== SESSIONS & SOUMISSIONS (ÉTUDIANTS) ====================
    Route::prefix('exam/{evaluationId}')->group(function () {
        // Sessions
        Route::post('/start', [ExamSessionController::class, 'start']);
        Route::get('/progress', [ExamSessionController::class, 'progress']);

        // Soumissions
        Route::post('/save', [ExamSubmissionController::class, 'save']);
        Route::post('/submit-question', [ExamSubmissionController::class, 'submitQuestion']);
        Route::post('/submit-all', [ExamSubmissionController::class, 'submitAll']);

        // Récupération des soumissions d'un étudiant
        Route::get('/student/{etudiantId}/submissions', [ExamSubmissionController::class, 'index']);

        // Statistiques
        Route::get('/statistics', [ExamSubmissionController::class, 'statistics']);
    });

    Route::get('/exam/{evaluationId}/submissions/all', [ExamSubmissionController::class, 'allSubmissions']);
    Route::get('/exam/{evaluationId}/all-submissions', [ExamSubmissionController::class, 'allSubmissions']);
    Route::get('/exam/{evaluationId}/submissions/submitted', [ExamSubmissionController::class, 'submittedOnlySubmissions']);
    Route::post('/exam/{evaluationId}/finalize-grade/{etudiantId}', [ExamSubmissionController::class, 'finalizeEtudiantGrade']);

    // ==================== SESSIONS (GESTION) ====================
    Route::get('/evaluations/{evaluationId}/sessions', [ExamSessionController::class, 'examSessions']);
    Route::apiResource('exam-sessions', ExamSessionController::class)->except(['index', 'store']);
    Route::post('/exam-sessions/{id}/ping', [ExamSessionController::class, 'ping']);

    // ==================== SOUMISSIONS (GESTION) ====================
    Route::get('/exam-submissions/{id}', [ExamSubmissionController::class, 'show']);
    Route::post('/exam-sessions/clean-duplicates', [ExamSessionController::class, 'cleanDuplicates']);

    Route::post('/exam-submissions/{id}/grade', [ExamSubmissionController::class, 'grade']);
    Route::post('/exam-submissions/{id}/suggest-grade', [ExamSubmissionController::class, 'suggestGrade']);

Route::post('/exam/{evaluationId}/submit-complex', [ExamSubmissionController::class, 'submitComplex']);
Route::get('/exam-submissions/{id}/details', [ExamSubmissionController::class, 'details']);


Route::prefix('exam')->group(function () {
    Route::post('/{evaluationId}/save-complex', [ExamComplexResponseController::class, 'saveComplex']);
});

Route::prefix('tickets')->group(function(){
  Route::get('/liste', [TicketController::class, 'index']);
    Route::post('/ajouter', [TicketController::class, 'store']);
    Route::delete('/{id}/supprimer', [TicketController::class, 'destroy']);
    Route::post('/{id}/fermer', [TicketController::class, 'close']);
});



    //    Route::get('/conversations', [ConversationController::class, 'index']);
    // Route::post('/conversations', [ConversationController::class, 'store']);
    // Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);
    // Route::post('/conversations/{conversation}/mark-all-read', [ConversationController::class, 'markAllAsRead']);

    // // Messages
    // Route::get('/conversations/{conversation}/messages', [MessageController::class, 'index']);
    // Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store']);
    // Route::get('/conversations/{conversation}/messages/{message}', [MessageController::class, 'show']);
    // Route::put('/conversations/{conversation}/messages/{message}', [MessageController::class, 'update']);
    // Route::delete('/conversations/{conversation}/messages/{message}', [MessageController::class, 'destroy']);

    // // Marquer comme lu
    // Route::post('/conversations/{conversation}/messages/read', [MessageController::class, 'markAsRead']);

    // // Pièces jointes
    // Route::get('/conversations/{conversation}/messages/{message}/attachments/{attachment}/download', 
    //           [MessageController::class, 'downloadAttachment']);
    Route::post('/change-password', [ChangePasswordController::class, 'update']);

    // Communications
    Route::controller(\App\Http\Controllers\Api\CommunicationController::class)->prefix('communications')->group(function () {
        Route::get('/', 'index');
        Route::get('/unread-count', 'getUnreadCount');
        Route::get('/{communication}', 'show');
        Route::post('/{communication}/mark-as-read', 'markAsRead');
    });

    // Cartes d'étudiants
    Route::controller(\App\Http\Controllers\Api\StudentCardController::class)->prefix('student-cards')->group(function () {
        Route::get('/etudiants', 'index');
        Route::post('/selected-data', 'getSelected');
        Route::get('/filters', 'getFilters');
    });

    // Syllabuses
    Route::controller(\App\Http\Controllers\SyllabusController::class)->prefix('syllabuses')->group(function () {
        Route::get('/{uvSlug}', 'show');
        Route::post('/{uvSlug}', 'store');
        Route::post('/{uvSlug}/upload-attachment', 'uploadFile');
    });

    // Enseignant Courses
    Route::get('/enseignant/mes-matieres', [\App\Http\Controllers\Api\Enseignant\TeacherCourseController::class, 'index']);
});

// ========================================
// PUBLIC ROUTES
// ========================================
Route::prefix('public')->group(function () {
    // Inscription routes (unauthenticated)
    Route::get('niveau/liste', [\App\Http\Controllers\NiveauController::class, 'index']);
    Route::get('filieres/liste', [\App\Http\Controllers\Admin\FiliereController::class, 'index']);
    Route::post('candidature/soumettre', [\App\Http\Controllers\CandidatureController::class, 'storeByAdmin']);

    Route::get('galeries', [\App\Http\Controllers\Api\PublicGalleryController::class, 'index']);
    Route::get('galeries/{id}', [\App\Http\Controllers\Api\PublicGalleryController::class, 'show']);
    Route::post('contact', [\App\Http\Controllers\Api\PublicContactController::class, 'store']);
    Route::post('prospects', [\App\Http\Controllers\Api\PublicProspectController::class, 'store']);
    
    // Configurations publiques
    Route::get('configurations', [\App\Http\Controllers\ConfigurationController::class, 'index']);
    
    // Blogs
    Route::get('blogs', [\App\Http\Controllers\Api\PublicBlogController::class, 'index']);
    Route::get('blogs/{idOrSlug}', [\App\Http\Controllers\Api\PublicBlogController::class, 'show']);
    Route::post('blogs/{idOrSlug}/comments', [\App\Http\Controllers\Api\PublicBlogController::class, 'addComment']);

    // Newsletter
    Route::post('newsletter/subscribe', [\App\Http\Controllers\Api\PublicNewsletterController::class, 'subscribe']);
});



Route::any('semoa-callback-url', SemoaCallBackController::class)->name('api.semoa.callback');
Route::post('semoa/initiate', [\App\Http\Controllers\SemoaPaymentController::class, 'initiate'])->middleware('auth:sanctum');

require __DIR__ . '/api_admin_routes.php';

require __DIR__ . '/api_auth.php';

require __DIR__ . '/api_candidature.php';

require __DIR__ . '/api_comptable.php';

require __DIR__ . '/api_etudiant.php';

// require __DIR__ . '/api_professeur.php';

Route::get('/test-calc/{slug}', function($slug) {
    $etudiant = \App\Models\Etudiant::where('slug', $slug)->first();
    $periode = \App\Models\Periode::where('nom', 'LIKE', '%Semestre 1%')->first() ?: \App\Models\Periode::first();
    
    if(!$etudiant) return response()->json(['error' => "Etudiant non trouvé pour le slug: $slug"]);
    
    $service = app(\App\Services\NoteCalculationService::class);
    
    try {
        $releve = $service->calculateAndSaveForStudent($etudiant, $periode->anneeScolaire, $periode);
        
        return response()->json([
            'success' => true,
            'etudiant' => $etudiant->nom . ' ' . $etudiant->prenom,
            'filiere_id' => $etudiant->etudiantGroups()->first()?->filiere_id,
            'periode' => $periode->nom,
            'releve_id' => $releve->id,
            'metadata' => $releve->metadata,
            'ue_validations_count' => $releve->ueValidations()->count(),
            'uv_validations_count' => $releve->uvValidations()->count(),
            'ue_list' => $releve->ueValidations->map(fn($v) => $v->uniteEnseignement->nom),
            'uv_list' => $releve->uvValidations->map(fn($v) => $v->uniteValeur->nom)
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});
