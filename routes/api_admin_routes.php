<?php

use App\Exports\EtudiantsExport;
use App\Http\Controllers\{
    Admin\AnnouncementController,
    Admin\BlogController,
    AdvertiserController,
    CandidatureController,
    ClubController,
    EtudiantController,
    EvaluationController,
    FraisInscriptionController,
    GroupController,
    ConfigurationController,
    NiveauController,
    NotificationController,
    ReclamationController,
    ReleveController,
    ReleveNoteController,
    ReinscriptionController,
    StatistiquesController,
};
use App\Http\Controllers\Admin\{
    ActivityLogController,
    AgendaController,
    AnonymousSheetController,
    ContactController,
    EmploiDuTempController,
    EtudiantController as AdminEtudiantController,
    EventController,
    FicheDePresenceController,
    FiliereController,
    GalleryAlbumController,
    GalleryPhotoController,
    NoteController,
    ReclamationController as AdminReclamationController,
    PeriodeController,
    RoleController,
    SalleController,
    SurveillantController,
    UniteEnseignementController,
    UniteValeurController,
    UserController,
    UrgentInfoController
};
use App\Http\Controllers\Admin\ClassCommitteeController;
use App\Http\Controllers\CarteEtudiantController;
use App\Http\Controllers\AnneeScolaireController;
use App\Http\Controllers\Admin\EvaluationRoomController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\AnneeScolaire;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

use function PHPUnit\Framework\assertEquals;

Route::controller(ConfigurationController::class)->prefix('parametre')->name('configuration.')->group(function () {
    Route::get('configuration', 'index')->name('index');
});

Route::apiResource('document-types', \App\Http\Controllers\DocumentTypeController::class)
    ->except(['store', 'update', 'destroy'])
    ->middleware('auth:sanctum');
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('document-types', [\App\Http\Controllers\DocumentTypeController::class, 'store'])
        ->name('document-types.store')->middleware('can:create-type-document');
    Route::put('document-types/{document_type}', [\App\Http\Controllers\DocumentTypeController::class, 'update'])
        ->name('document-types.update')->middleware('can:update-type-document');
    Route::patch('document-types/{document_type}', [\App\Http\Controllers\DocumentTypeController::class, 'update'])
        ->middleware('can:update-type-document');
    Route::delete('document-types/{document_type}', [\App\Http\Controllers\DocumentTypeController::class, 'destroy'])
        ->name('document-types.destroy')->middleware('can:delete-type-document');
});

// Configuration des champs obligatoires du formulaire de candidature (par école)
Route::middleware('auth:sanctum')->group(function () {
    Route::controller(\App\Http\Controllers\Api\Admin\CandidatureFieldConfigController::class)
        ->prefix('candidature-field-configs')->name('candidature-field-configs.')->group(function () {
            Route::get('', 'index')->name('index');
            Route::put('', 'update')->name('update')->middleware('can:update-candidature-field-config');
        });

    Route::controller(\App\Http\Controllers\Api\Admin\TypeDiplomeController::class)
        ->prefix('type-diplomes')->name('type-diplomes.')->group(function () {
            Route::get('', 'index')->name('index');
            Route::get('champs-disponibles', 'champsDisponibles')->name('champs-disponibles');
            Route::post('', 'store')->name('store')->middleware('can:create-type-diplome');
            Route::put('{id}', 'update')->name('update')->middleware('can:update-type-diplome');
            Route::delete('{id}', 'destroy')->name('destroy')->middleware('can:delete-type-diplome');
        });

    Route::controller(\App\Http\Controllers\Api\Admin\MoyenConnaissanceController::class)
        ->prefix('moyens-connaissance')->name('moyens-connaissance.')->group(function () {
            Route::get('', 'index')->name('index');
            Route::post('', 'store')->name('store')->middleware('can:create-moyen-connaissance');
            Route::put('{id}', 'update')->name('update')->middleware('can:update-moyen-connaissance');
            Route::delete('{id}', 'destroy')->name('destroy')->middleware('can:delete-moyen-connaissance');
        });
});

// Gestion des informations urgentes (PUBLIC)
Route::controller(UrgentInfoController::class)->prefix('informations-urgentes')->name('urgent_infos_public.')->group(function () {
    Route::get('liste', 'index')->name('index');
    Route::get('{urgent}/show', 'show')->name('show');
});

// Route publique pour la génération des cartes d'étudiants (Générateur Hybride)
Route::get('student-cards/generate-pdf', [CarteEtudiantController::class, 'genererCartesPdf'])
    ->name('student-cards.generate-pdf');

// Route publique pour la vérification des informations d'un étudiant par son matricule
Route::get('student-cards/verify/{matricule}', [CarteEtudiantController::class, 'verifyStudent'])
    ->name('student-cards.verify');

Route::middleware('auth:sanctum')->group(function () {


    Route::controller(FiliereController::class)->prefix('filieres')->name('filieres.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::get('ajouter-une-filiere', 'create')->name('create');
        Route::get('{filiere}/a-propos', 'show')->name('show');
        Route::get('{filiere}/modifier', 'edit')->name('edit');
        Route::get('{filiere}/programme', 'getProgramme')->name('programme');
        Route::post('ajouter-une-filiere', 'store')->name('store')->middleware('can:create-filiere');
        Route::put('{filiere}/modifier', 'update')->name('update')->middleware('can:update-filiere');
        Route::delete('{filiere}/supprimer', 'destroy')->name('delete')->middleware('can:delete-filiere');
    });



    // Gestion des unités d'enseignement par l'administration
    Route::controller(UniteEnseignementController::class)->prefix('unites-d-enseignement')->name('ues.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::get('ajouter-une-ue', 'create')->name('create');
        Route::get('{ue}/a-propos', 'show')->name('show');
        Route::get('{ue}/modifier', 'edit')->name('edit');
        Route::post('ajouter-une-ue', 'store')->name('store')->middleware('can:create-ue');
        Route::put('{ue}/modifier', 'update')->name('update')->middleware('can:update-ue');
        Route::delete('{ue}/supprimer', 'destroy')->name('delete')->middleware('can:delete-ue');
    });

    // Gestion des unités de valeur par l'administration
    Route::controller(UniteValeurController::class)->prefix('unites-de-valeur')->name('uvs.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::get('ajouter-une-matiere', 'create')->name('create');
        Route::get('{uv}/a-propos', 'show')->name('show');
        Route::get('{uv}/modifier', 'edit')->name('edit');
        Route::post('ajouter-une-matiere', 'store')->name('store')->middleware('can:create-uv');
        Route::put('{uv}/modifier', 'update')->name('update')->middleware('can:update-uv');
        Route::delete('{uv}/supprimer', 'destroy')->name('delete')->middleware('can:delete-uv');
    });

    // Gestion des périodes par l'administration
    Route::controller(PeriodeController::class)->prefix('decoupage-academique')->name('periodes.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::get('ajouter-un-decoupage', 'create')->name('create');
        Route::get('{periode}/a-propos', 'show')->name('show');
        Route::get('{periode}/modifier', 'edit')->name('edit');
        Route::post('ajouter-un-decoupage', 'store')->name('store')->middleware('can:create-semestre');
        Route::put('{periode}/modifier', 'update')->name('update')->middleware('can:update-semestre');
        Route::delete('{periode}/supprimer', 'destroy')->name('delete')->middleware('can:delete-semestre');
        Route::get('/periode-by-year', 'showByYear');
    });


    // Gestion des Salles par l'administration
    Route::controller(SalleController::class)->prefix('salles')->name('salles.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::get("{salle}/show", 'show');
        Route::post('ajouter-une-salle', 'store')->name('store')->middleware('can:create-salle');
        Route::put('{salle}/modifier-une-salle', 'update')->name('update')->middleware('can:update-salle');
        Route::get('{salle}/emploi-du-temps', 'displayCalendar')->name('display-calendar');
        Route::get('{salle}/load-edt', 'loadCalendar')->name('load-calendar');
        Route::delete('{salle}/supprimer-une-salle', 'destroy')->name('delete')->middleware('can:delete-salle');
    });

    // Gestion des Jours Fériés par l'administration
    Route::controller(\App\Http\Controllers\Admin\JourFerieController::class)->prefix('jours-feries')->name('jours-feries.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::post('ajouter', 'store')->name('store')->middleware('can:create-jour-ferie');
        Route::get('{jourFerie}', 'show')->name('show');
        Route::put('{jourFerie}/modifier', 'update')->name('update')->middleware('can:update-jour-ferie');
        Route::delete('{jourFerie}/supprimer', 'destroy')->name('delete')->middleware('can:delete-jour-ferie');
    });

    Route::controller(EmploiDuTempController::class)->prefix('emploi-du-temps')->name('edt.')->group(function () {
        Route::post('store', 'store')->name('store')->middleware('can:create-cours');
        Route::put('update-dates', 'updateDates')->name('update-dates')->middleware('can:update-cours');
        Route::put('update', 'update')->name('update')->middleware('can:update-cours');
        Route::post('check-availability', 'checkAvailability')->name('check-availability');
        Route::delete('{slug}/delete', 'destroy')->name('delete')->middleware('can:delete-cours');
        Route::get('/matrice/export', 'exportMatrice');
        Route::get('/data', 'getEmploiDuTempsData');
        Route::post('/import','importExcel')->middleware('can:create-cours');
    });

    // Journal d'activité (audit)
    Route::controller(ActivityLogController::class)->prefix('logs')->name('logs.')->middleware('can:view-logs')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('modules', 'modules')->name('modules');
        Route::delete('{id}', 'destroy')->name('destroy')->middleware('can:delete-log');
        Route::post('bulk-delete', 'bulkDestroy')->name('bulk-destroy')->middleware('can:delete-log');
    });

    // Historique personnel (self-service, sans permission dédiée : chacun voit son propre journal)
    Route::get('logs/mine', [ActivityLogController::class, 'mine'])->name('logs.mine');

    // Gestion des Rôles par l'administration — système de permissions dynamique
    Route::controller(RoleController::class)->prefix('roles')->name('roles.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::get('permissions-disponibles', 'availablePermissions')->name('permissions-disponibles');
        Route::post('ajouter-un-role', 'store')->name('store')->middleware('can:create-role');
        Route::get('{role}/a-propos', 'show')->name('show');
        Route::put('{role}/modifier', 'update')->name('update')->middleware('can:update-role');
        Route::put('{role}/permissions', 'syncPermissions')->name('sync-permissions')->middleware('can:assign-role-permissions');
        Route::delete('{role}/supprimer', 'destroy')->name('delete')->middleware('can:delete-role');
    });

    // Gestion des Utilisateurs par l'administration
    Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('create', 'store')->name('store')->middleware('can:create-user');
         Route::post('create-enseignant', 'storeEnseignant')->middleware('can:create-enseignant');
        Route::get('{user}/edit', 'edit')->name('edit');
        Route::get('{user}/show', 'show');
        Route::put('{user}/update', 'update')->middleware('can:update-user');
        Route::put('{user}/update-enseignant', 'updateEnseignant')->middleware('can:update-enseignant');
        Route::put('{user}/update-fiscalite', 'updateFiscalite')->middleware('can:update-user');
        Route::delete('{user}/delete', 'destroy')->name('delete')->middleware('can:delete-user');
        
        Route::get('{user}/load-edt', 'loadEmploiDuTemps')->name('load-edt'); // charge les edt de l'utilisateur
        Route::get('{user}/emploi-du-temps', 'ShowEmploiDuTemps')->name('show-edt'); // charge les edt de l'utilisateur
        Route::post('{user}/add-edt', 'storeEmploiDuTemps')->name('store-edt')->middleware('can:update-user'); // charge les edt de l'utilisateur
        Route::get('teachers/hours-summary', 'hoursSummary')->name('teachers.hours-summary'); // récapitulatif heures enseignants
        Route::get('/liste-des-enseignants', 'getEnseignant');
        Route::get('/liste-des-suveillants', 'getSurveillant');
        Route::put('/update-edt', 'updateEmploiDuTemps')->name('update-edt')->middleware('can:update-user'); // charge les edt de l'utilisateur
        Route::post('/import', 'importUsers')->middleware('can:create-user');
    });

    // Routes pour la gestion des surveillants
    Route::controller(SurveillantController::class)->prefix('surveillants')->name('surveillants.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{user}', 'show')->name('show');
        Route::post('{user}/update-type', 'updateType')->name('update-type')->middleware('can:update-surveillant');
        Route::post('{user}/make-interne', 'makeInterne')->name('make-interne')->middleware('can:update-surveillant');
        Route::post('{user}/make-externe', 'makeExterne')->name('make-externe')->middleware('can:update-surveillant');
        Route::post('{user}/remove-surveillance', 'removeSurveillance')->name('remove')->middleware('can:update-surveillant');
    });

    Route::controller(CandidatureController::class)->prefix('candidature')->name('candidatures.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::get('count-a-traiter', 'countCandidaturesATraiter')->name('count-a-traiter');
        Route::get('creation-d-une-candidature', 'inscriptionIndexForm')->name('create');
        Route::post("store-by-admin", "storeByAdmin")->name("store-by-admin")->middleware('can:create-candidat-entrant');
        Route::post("{candidature}/update-by-admin", "updateByAdmin")->name("update-by-admin")->middleware('can:update-candidat-entrant');
        Route::get('payement-des-frais-de-participation', 'payementCandidaturesIndex')->name('payement-des-frais-de-participation');
        Route::get('participation-au-concours', 'participantCandidaturesIndex')->name('participation-au-concours');
        Route::get('admission-a-' . Str::slug(env('APP_NAME')), 'admisCandidaturesIndex')->name('admission');
        Route::get('liste-des-rectifications', 'liste_des_rectifications')->name('index.rectifications');
        Route::get('liste-des-admis', 'InscriptionCandidaturesIndex')->name('liste-des-admis');
        Route::get('export/excel', 'exportCandidatsAdmisExcel')->name('export.excel');
        Route::get('export/etude-dossier', 'exportEtudeDossierExcel')->name('export.etude-dossier');
        Route::get('liste-des-rejets', 'liste_des_rejets')->name('index.rejections');
        Route::get('dossiers-incomplets', 'listeDossiersIncomplets')->name('index.incomplets');
        Route::delete('{candidature}/supprimer-brouillon', 'supprimerBrouillon')->name('delete-brouillon')->middleware('can:delete-brouillon-candidature');
        Route::get('{candidature}/evaluer', 'show')->name('show');
        Route::get('choix-de-groupe', 'chooseClassAssignmentGroupView')->name('choose-class-assignment-group-view');
        Route::get('attribution-de-groupe/{group}', 'showGroupClassAssignmentView')->name('show-class-assignment-view');
        Route::post('payement-des-frais-de-participation', 'payementCandidaturesStore')->name('payement-des-frais-de-participation.store')->middleware('can:payer-participation-candidature');
        Route::post('attribution-de-groupe', 'storeGroupClassAssignment')->name('attribution-de-groupe')->middleware('can:attribuer-groupe-candidature');
        Route::post('presence-sub', 'presenceControlStore')->name('presence-sub')->middleware('can:controler-presence-candidature');
        Route::post('admission-sub', 'admissionControl')->name('admission-sub')->middleware('can:controler-admission-candidature');
        Route::put('{candidature}/transmettre-academie', 'transmettreAcademie')->name('transmettre-academie')->middleware('can:transmettre-candidature');
        Route::put('{candidature}/valider', action: 'validateCandidature')->name('validate')->middleware('can:valider-candidature');
        Route::put('{candidature}/rejeter', 'rejectCandidature')->name('reject')->middleware('can:rejeter-candidature');
        Route::put('{candidature}/demander-rectification', 'askForRectificationOnCandidature')->name('ask-for-rectification')->middleware('can:rectifier-candidature');
        Route::post('{candidature}/reorienter', 'reorienter')->name('reorienter')->middleware('can:reorienter-candidature');
        Route::post('{candidature}/inscrire-un-etudiant', 'insertStudent')->name('inscrire-un-etudiant')->middleware('can:inscrire-etudiant-candidature');
        Route::get('{year}/generer-matricule', 'generateMatricule')->name('generer-matricule');
    });


    Route::controller(AgendaController::class)->prefix('agenda')->name('agenda.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/store', 'store')->name('store');
        Route::delete('{agenda}/destroy', 'destroy')->name('destroy');
        Route::post('{agenda}/modifier', 'update')->name('update');
        Route::get('get-my-agenda', 'getAgenda')->name("get");
    });

    Route::controller(AnneeScolaireController::class)->prefix('annee-scolaire')->name('annescolaire.')->group(function () {
        Route::get('/liste', 'index')->name('liste');
        Route::post('/create', 'store')->name('store')->middleware('can:create-annee-scolaire');
        Route::put('/{id}/activate', 'activer')->name('activer')->middleware('can:update-annee-scolaire');
        Route::put('{id}/desactivate', 'desactiver')->name('desactiver')->middleware('can:update-annee-scolaire');
        Route::put('{annee}/update', 'update')->name('update')->middleware('can:update-annee-scolaire');
    });

    Route::controller(FraisInscriptionController::class)->prefix('frais-inscription')->name('frais-inscription.')->group(function () {
        Route::get('/index', 'index')->name('index');
        Route::post('/payer', 'store')->name('store')->middleware('can:create-frais-inscription');
        Route::get('/{id}/detail', 'show')->name('show');
        Route::put('/update/{id}', 'update')->name('update')->middleware('can:update-frais-inscription');
        Route::put('/activate/{id}', 'activate')->name('activate')->middleware('can:update-frais-inscription');
        Route::delete('/destroy/{id}', 'destroy')->name('delete')->middleware('can:delete-frais-inscription');
    });

    Route::controller(NiveauController::class)->prefix('niveau')->name('niveau.')->group(function () {
        Route::get('/liste', 'index')->name('liste');
        Route::post('/ajouter', 'store')->name('store')->middleware('can:create-niveau');
        Route::put('/{id}/modifier', 'update')->name('update')->middleware('can:update-niveau');
        Route::patch('/{id}/toggle-status', 'toggleStatus')->name('toggle-status')->middleware('can:update-niveau');
        Route::get('/{id}/periodes', 'getPeriodes')->name('get-periodes');
        Route::post('/{id}/assign-periodes', 'assignPeriodes')->name('assign-periodes')->middleware('can:update-niveau');

        Route::get('/{id}/documents', 'getDocumentRequirementsAdmin');
        Route::post('/{id}/documents', 'storeDocumentRequirement')->middleware('can:create-niveau');
        Route::put('/documents/{id}', 'updateDocumentRequirement')->middleware('can:update-niveau');
        Route::delete('/documents/{id}', 'destroyDocumentRequirement')->middleware('can:delete-niveau');
    });

    Route::controller(\App\Http\Controllers\Api\Admin\ConcoursSessionController::class)->prefix('concours-session')->name('concours-session.')->group(function () {
        Route::get('/liste', 'index')->name('liste');
        Route::post('/ajouter', 'store')->name('store')->middleware('can:create-concours-session');
        Route::put('/{id}/modifier', 'update')->name('update')->middleware('can:update-concours-session');
        Route::patch('/{id}/toggle-status', 'toggleStatus')->name('toggle-status')->middleware('can:update-concours-session');
        Route::post('/{id}/publier', 'publish')->name('publish')->middleware('can:publish-concours-session');
        Route::post('/{id}/depublier', 'unpublish')->name('unpublish')->middleware('can:publish-concours-session');
    });

    Route::controller(\App\Http\Controllers\Api\Admin\ConcoursMatiereController::class)->prefix('concours-matiere')->name('concours-matiere.')->group(function () {
        Route::get('/liste', 'index')->name('liste');
        Route::post('/ajouter', 'store')->name('store')->middleware('can:create-concours-matiere');
        Route::put('/{id}/modifier', 'update')->name('update')->middleware('can:update-concours-matiere');
        Route::delete('/{id}/supprimer', 'destroy')->name('delete')->middleware('can:delete-concours-matiere');
    });

    Route::controller(\App\Http\Controllers\Api\Admin\ConcoursSessionMatiereController::class)->prefix('concours-session/{session}/matieres')->name('concours-session.matieres.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store')->middleware('can:create-concours-session-matiere');
        Route::put('/{sessionMatiere}', 'update')->name('update')->middleware('can:update-concours-session-matiere');
        Route::delete('/{sessionMatiere}', 'destroy')->name('delete')->middleware('can:delete-concours-session-matiere');
    });

    Route::controller(\App\Http\Controllers\Api\Admin\ConcoursNoteController::class)->prefix('concours-session/{session}/notes')->name('concours-session.notes.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/enregistrer', 'storeBulk')->name('store-bulk')->middleware('can:enregistrer-notes-concours');
    });





    Route::controller(ConfigurationController::class)->prefix('parametre')->name('configuration.')->group(function () {
        Route::put('parametre/modification', 'update')->name('update')->middleware('can:update-configuration-site');
    });

    Route::controller(GroupController::class)->prefix('groups')->name('groups.')->group(function () {
        Route::get('{group}/get-matieres', 'getMatieres')->name('get-matieres');
        Route::get('liste', 'index')->name('index');
        Route::get('{group}/attribution-aux-etudiants', 'showGroupAssignmentView')->name('show-attribution-view');
        Route::get('{group}/emploi-du-temps', 'displayCalendar')->name('display-calendar');
        Route::get('{group}/liste-des-etudiants', 'getEtudiants')->name('etudiants');
        Route::get('{group}/load-calendar', 'loadCalendar')->name('load-calendar');
        Route::post('{group}/emploi-du-temps', 'updateCalendar')->name('update-calendar')->middleware('can:update-groupe');
        Route::post('ajouter', 'store')->name('store')->middleware('can:create-groupe');
        Route::post('assign-delegue', 'assignDelegue')->name('assign-delegue')->middleware('can:update-groupe');
        Route::post('{group}/attribution-aux-etudiants-enregistrement', 'storeGroupAssignment')->name('store-attribution')->middleware('can:update-groupe');
        Route::delete('{groupe}/supprimer', 'destroy')->name('delete')->middleware('can:delete-groupe');
    });

    Route::controller(ClubController::class)->prefix('club')->name('club.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store')->middleware('can:create-club');
        Route::get('{club}/edit', 'edit')->name('edit');
        Route::put('{club}/update', 'update')->name('update')->middleware('can:update-club');
        Route::delete('{club}/delete', 'destroy')->name('delete')->middleware('can:delete-club');

        Route::get('{club}/etudiants', 'getEtudiant')
            ->name('etudiants.create');

        Route::post('{club}/etudiants', 'storeEtudiant')
            ->name('etudiants.store')
            ->middleware('can:update-club');

        Route::delete('{club}/etudiants/{etudiant}', 'destroyEtudiant')
            ->name('etudiants.destroy')
            ->middleware('can:update-club');
    });



    Route::controller(EtudiantController::class)->prefix('etudiants')->name('etudiants_actions.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::get('{etudiant}/details', 'show')->name('show');
        Route::put('{etudiant}/changer-de-groupe', 'changeGroup')->name('change-group')->middleware('can:update-etudiant');
        Route::post('{etudiant}/reinscrire', [ReinscriptionController::class, 'store'])->name('reinscrire')->middleware('can:reinscrire-etudiant');
    });




    // Gestion du comité de classe
    Route::controller(ClassCommitteeController::class)->prefix('comite-de-classe')->name('committee.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::post('', 'store')->name('store')->middleware('can:create-comite-classe');
        Route::delete('', 'destroy')->name('delete')->middleware('can:delete-comite-classe');
    });

    Route::prefix('evaluations')->name('evaluations.')->group(function () {
        Route::controller(EvaluationController::class)->group(function () {
            Route::get('liste', 'index')->name('index');
            Route::get('ajouter', 'create')->name('create');
            Route::get('{evaluation}/detail', 'show')->name('show');
            Route::post('ajouter', 'store')->name('store')->middleware('can:create-evaluation');
            Route::get('{evaluation}/modifier', 'edit')->name('edit');
            Route::put('{evaluation}/modifier', 'update')->name('update')->middleware('can:update-evaluation');
            Route::delete('{evaluation}/supprimer', 'destroy')->name('delete')->middleware('can:delete-evaluation');
            Route::get('{slug}/publier', 'publish')->name('publish')->middleware('can:publish-evaluation');
            Route::get('{evaluation}/fiche-de-note', 'getNoteFiche')->name(name: 'fiche-de-note');
            Route::get('/get-liste-enseignant-evaluations', 'getListEvaluationForTeacher');
            Route::get('/get-mes-evaluations', 'getMyEvaluations');
            Route::get('/get-liste-etudiant-evaluations', 'getListEvaluationForStudent');
        });

        // Allocation de salles, surveillants, répartition étudiants
        Route::controller(EvaluationRoomController::class)->prefix('{evaluation}/rooms')->name('rooms.')->group(function () {
            Route::post('allocate', 'allocate')->name('allocate')->middleware('can:allouer-salle-evaluation');
            Route::post('{evaluationRoom}/supervisors', 'setSupervisors')->name('set-supervisors')->middleware('can:allouer-salle-evaluation');
            Route::delete('reset', 'reset')->name('reset')->middleware('can:allouer-salle-evaluation');
            Route::get('summary', 'summary')->name('summary');
            Route::get('export.csv', 'exportCsv')->name('export');
        });

        Route::controller(NoteController::class)->prefix('{evaluation}/notes')->name('notes.')->group(function () {
            Route::post('enregistrer-note', 'storeNotes')->name('store-notes')->middleware('can:create-note');
            Route::put('publier-les-notes', 'publishNotes')->name('publish-notes')->middleware('can:publish-note');
            Route::post('changer-note', 'ChangeNotes')->name('change-notes')->middleware('can:update-note-apres-delai');
            Route::get('export', 'export')->name('export');
        });

        // Routes pour les fiches d'anonymes
        Route::controller(AnonymousSheetController::class)->prefix('{evaluation}/anonymous')->name('anonymous.')->group(function () {
            Route::post('generate', 'generateCodes')->name('generate')->middleware('can:generer-anonymat-evaluation');
            Route::get('codes', 'showCodes')->name('codes');
            Route::get('print', 'printSheet')->name('print');
            Route::get('export.csv', 'exportCsv')->name('export');
            Route::get('export.excel', 'exportExcel')->name('export.excel');
            Route::delete('delete', 'deleteCodes')->name('delete')->middleware('can:generer-anonymat-evaluation');
        });

        //	Route::get('index', 'index')->name('index');
        //	Route::get('index', 'index')->name('index');
    });

    Route::controller(FicheDePresenceController::class)->prefix('fiches-de-presence')->name('fiches.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::post('enregistrer', 'store')->name('store')->middleware('can:create-fiche-presence');
        Route::get('{fiche}/remplir', 'make')->name('make');
        Route::put('{fiche}/mettre-a-jour', 'update')->name('update')->middleware('can:update-fiche-presence');
        Route::post('{fiche}/soumettre', 'submit')->name('submit')->middleware('can:update-fiche-presence');
    });

    // Validation des absences (admin)
    Route::controller(\App\Http\Controllers\Admin\PresenceValidationController::class)
        ->prefix('presences')
        ->name('presences.')
        ->group(function () {
            Route::get('validation', 'index')->name('index');
            Route::post('{presence}/valider', 'validateOne')->name('validate-one')->middleware('can:valider-absence');
            Route::post('valider', 'validateBatch')->name('validate-batch')->middleware('can:valider-absence');
        });

    Route::controller(AdvertiserController::class)->prefix('partenaires')->name('advertisers.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::get('ajouter', 'create')->name('create');
        Route::get('{advertiser}/details', 'show')->name('show');
        Route::post('ajouter', 'store')->name('store')->middleware('can:create-partenaire');
        Route::get('{advertiser}/modifier', 'edit')->name('edit');
        Route::get('{advertiser}/modifier', 'edit')->name('edit');
        Route::put('{advertiser}/update', 'update')->name('update')->middleware('can:update-partenaire');
        Route::delete('{advertiser}/delete', 'destroy')->name('delete')->middleware('can:delete-partenaire');
    });


    Route::controller(AnnouncementController::class)->prefix('opportunites')->name('api.announcements.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::get('ajouter', 'create')->name('create');
        Route::get('{announcement}/details', 'show')->name('show');
        Route::delete('{announcement}/supprimer', 'destroy')->name('delete')->middleware('can:delete-opportunite');
        Route::post('ajouter', 'store')->name('store')->middleware('can:create-opportunite');
        Route::get('{announcement}/modifier', 'edit')->name('edit');
        Route::get('{announcement}/modifier', 'edit')->name('edit');
        Route::get('{announcement}/publier', 'publish')->name('publish')->middleware('can:publish-opportunite');
        Route::post('{announcement}/mettre-a-jour', 'update')->name('update')->middleware('can:update-opportunite');
        Route::get('{announcement}/postulants', 'etudiants')->name('etudiants');
        //	Route::delete('retirer', 'delete')->name('delete');
    });

    // Route pour les publications
    Route::controller(BlogController::class)->prefix('publications')->name('blogs.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('ajouter', 'create')->name('create');
        Route::post('nouveau', 'store')->name('store')->middleware('can:create-blog');
        Route::get('{blog}', 'show')->name('show');
        Route::get('{blog}/modifier', 'edit')->name('edit');
        Route::put('{blog}/modifier', 'update')->name('update')->middleware('can:update-blog');
        Route::delete('{blog}', 'delete')->name('destroy')->middleware('can:delete-blog');
        Route::post('', 'search')->name('search');
        Route::put("{blog}/togglestatus", 'publishedBlog')->middleware('can:publish-blog');
    });

    // Route pour les publications
    Route::controller(EventController::class)->prefix('evenements')->name('events.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('ajouter', 'create')->name('create');
        Route::post('', 'store')->name('store')->middleware('can:create-evenement');
        Route::get('{event}', 'show')->name('show');
        Route::get('{event}/modifier', 'edit')->name('edit');
        Route::put('{event}/modifier', 'update')->name('update')->middleware('can:update-evenement');
        Route::delete('{event}', 'delete')->name('destroy')->middleware('can:delete-evenement');
    });

    // Réclamations des étudiants
    // Route::controller(AdminReclamationController::class)->prefix('reclamations')->name('reclamations.')->group(function () {
    // 	Route::get('', 'index')->name('index');
    // 	Route::put('{reclamation}', 'updateStatus')->name('update');
    // });

    Route::controller(ReclamationController::class)->group(function () {
        Route::get('/reclamations', 'index');
        Route::post('/reclamations/{reclamation}/traiter', 'traiter')->middleware('can:update-reclamation');
    });


    Route::controller(ContactController::class)
        ->prefix('messages')
        ->name('messages.')
        ->group(function () {

            Route::get('count-unread-message', 'countEnreadMessage')
                ->name('count-unread-message');

            Route::get('', 'index')->name('index');

            Route::get('{contact}/lire', 'read')->name('read');
            Route::post('{contact}/repondre', 'reply')->name('reply')->middleware('can:reply-message-contact');

            Route::get('{contact}', 'show')->name('show');
            Route::delete('{contact}', 'destroy')->name('delete')->middleware('can:delete-message-contact');
        });

    // Prospects / Brochures
    Route::controller(\App\Http\Controllers\Admin\ProspectController::class)
        ->prefix('prospects')
        ->name('prospects.')
        ->group(function () {
            Route::get('export', 'export')->name('export');
            Route::get('count-unread', 'countUnread')->name('count-unread');
            Route::get('', 'index')->name('index');
            Route::get('{prospect}', 'show')->name('show');
            Route::patch('{prospect}/toggle-status', 'toggleStatus')->name('toggle-status')->middleware('can:update-prospect');
            Route::delete('{prospect}', 'destroy')->name('delete')->middleware('can:delete-prospect');
        });



    // Gestion des informations urgentes
    Route::controller(UrgentInfoController::class)->prefix('informations-urgentes')->name('urgent_infos.')->group(function () {
        Route::post('ajouter', 'store')->name('store')->middleware('can:create-actualite');
        Route::put('{urgent}/modifier', 'update')->name('update')->middleware('can:update-actualite');
        Route::post('{urgent}/publier', 'publish')->name('publish')->middleware('can:update-actualite');
        Route::post('{urgent}/depublier', 'unpublish')->name('unpublish')->middleware('can:update-actualite');
        Route::delete('{urgent}/supprimer', 'destroy')->name('destroy')->middleware('can:delete-actualite');
    });

    // Gestion de la Galerie (Albums & Photos)
    // Route::prefix('galerie')->name('gallery.')->middleware('can:manage-gallery')->group(function () {
    Route::prefix('galerie')->name('gallery.')->group(function () {
        // Albums
        Route::controller(GalleryAlbumController::class)->prefix('albums')->name('albums.')->group(function () {
            Route::get('', 'index')->name('index');
            Route::get('ajouter', 'create')->name('create');
            Route::post('', 'store')->name('store')->middleware('can:create-galerie-album');
            Route::get('{galleryAlbum}', 'show')->name('show');
            Route::get('{galleryAlbum}/modifier', 'edit')->name('edit');
            Route::put('{galleryAlbum}', 'update')->name('update')->middleware('can:update-galerie-album');
            Route::delete('{galleryAlbum}', 'destroy')->name('destroy')->middleware('can:delete-galerie-album');
        });

        // Photos
        Route::controller(GalleryPhotoController::class)->prefix('photos')->name('photos.')->group(function () {
            Route::get('', 'index')->name('index');
            Route::get('ajouter', 'create')->name('create');
            Route::post('', 'store')->name('store')->middleware('can:create-galerie-photo');
            Route::get('{galleryPhoto}', 'show')->name('show');
            Route::get('{galleryPhoto}/modifier', 'edit')->name('edit');
            Route::put('{galleryPhoto}', 'update')->name('update')->middleware('can:update-galerie-photo');
            Route::delete('{galleryPhoto}', 'destroy')->name('destroy')->middleware('can:delete-galerie-photo');
        });
    });

    // Routes pour les relevés de notes
    Route::controller(ReleveController::class)->prefix('releves')->name('releves.')->group(function () {
        Route::get('generer/{etudiant}', 'generateReleveForStudent')->name('generer');
        Route::get('/{etudiant_id}', 'genererReleve')->name('detail');

        Route::get('telecharger/{etudiant}', 'generateReleveForStudent')->name('telecharger');
        Route::get('download/{filename}', 'download')->name('download');
        Route::get('checked', 'checked')->name('checked');
        Route::post('groupe/{group}', 'generateGroupReleves')->name('groupe')->middleware('can:create-releve');
    });

    Route::controller(ReleveNoteController::class)->prefix('releves-de-note')->group(function () {
        Route::get('liste', 'index');
        Route::post('{etudiant}/generer-releve-de-note', 'recalculate')->middleware('can:create-releve');
        Route::get('{etudiant}/get-releve-de-note', 'showReleve');
        Route::delete('{releve:id}/supprimer', 'destroy')->middleware('can:delete-releve');
        Route::post('bulk-generate', 'bulkGenerate')->middleware('can:create-releve');
        Route::post('check-statuses', 'checkStatuses')->middleware('can:create-releve');
    });

    // Routes pour les cartes étudiants
    Route::controller(CarteEtudiantController::class)->prefix('carte')->name('carte.')->group(function () {
        Route::get('{etudiant}', 'genererCarteEtudiant')->name('index');
    });

    // Données pour la génération de cartes HTML (multi-sélection)
    Route::post('student-cards/selected-data', [CarteEtudiantController::class, 'selectedData'])
        ->name('student-cards.selected-data');

    Route::controller(AdminEtudiantController::class)->prefix('etudiants')->name('etudiants.')->group(function () {
        Route::get('liste', 'index')->name('index');
        Route::get('get-etudiant-non-boursier', "getNonBoursiers");

        Route::post('store', 'store')->name('store')->middleware('can:create-etudiant');
        Route::post('import', 'importEtudiant')->name('import')->middleware('can:create-etudiant');
        Route::get('export', function () {
            $anneActive = AnneeScolaire::where('active', true)->first()?->nom;
            return Excel::download(new EtudiantsExport, 'liste_des_etudiants_' . $anneActive . '.xlsx');
        })->name('export');
        Route::get('{etudiant}', 'show')->name('show');
        Route::put('{etudiant}', 'update')->name('update')->middleware('can:update-etudiant');
        Route::delete('{etudiant}', 'destroy')->name('destroy')->middleware('can:delete-etudiant');
    });

    Route::controller(NotificationController::class)->group(function () {
        Route::get('/notifications', 'index');
        Route::get('/notifications/unread', 'unread');

        Route::patch('/notifications/{id}/read', 'markAsRead');
        Route::patch('/notifications/read-all', 'markAllAsRead');

        Route::delete('/notifications/{id}', 'destroy');
        Route::delete('/notifications/delete-all', 'destroyAll');
    });


    // Routes pour les statistiques des filières
    Route::get('/statistiques/filieres/nombre', [StatistiquesController::class, 'NbreFilieres']);

    // Routes pour les statistiques des étudiants par niveau (Licence/Master)
    Route::get('/statistiques/etudiants/niveaux', [StatistiquesController::class, 'NbreEtudiants']);

    // Routes pour les statistiques des salles
    Route::get('/statistiques/salles/utilisees', [StatistiquesController::class, 'NbreSallesUtilisees']);
    Route::get('/statistiques/salles/dispos', [StatistiquesController::class, 'NbreSallesDispos']);

    // Communications (Admin)
    Route::controller(\App\Http\Controllers\Admin\CommunicationController::class)->prefix('admin/communications')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store')->middleware('can:create-communication');
        Route::get('/{communication}', 'show');
        Route::put('/{communication}', 'update')->middleware('can:update-communication');
        Route::delete('/{communication}', 'destroy')->middleware('can:delete-communication');
        Route::post('/{communication}/attachments', 'uploadAttachments')->middleware('can:update-communication');
        Route::delete('/attachments/{attachment}', 'deleteAttachment')->middleware('can:update-communication');
    });

    // Web Communications (Admin - Newsletter et Commentaires)
    Route::controller(\App\Http\Controllers\Api\Admin\WebCommunicationController::class)->prefix('admin/web-communications')->group(function () {
        Route::get('/comments', 'getComments');
        Route::put('/comments/{id}/status', 'updateCommentStatus')->middleware('can:moderate-commentaire-web');
        Route::delete('/comments/{id}', 'deleteComment')->middleware('can:moderate-commentaire-web');

        Route::get('/newsletter', 'getNewsletterSubscribers');
        Route::delete('/newsletter/{id}', 'deleteNewsletterSubscriber')->middleware('can:delete-abonne-newsletter');
    });

    // Routes pour les statistiques du nombre total d'étudiants
    Route::get('/statistiques/etudiants/total', [StatistiquesController::class, 'NbreTotalEtudiants']);
    Route::get('/statistiques/etudiants/total/{anneeScolaireId}', [StatistiquesController::class, 'NbreTotalEtudiants']);

    // Nouvelles routes de statistiques
    Route::get('/statistiques/enseignants/nombre', [StatistiquesController::class, 'NbreEnseignants']);
    Route::get('/statistiques/evaluations/nombre', [StatistiquesController::class, 'NbreEvaluations']);
    Route::get('/statistiques/presences/taux', [StatistiquesController::class, 'TauxPresenceMoyen']);
    Route::get('/statistiques/presences/tendance', [StatistiquesController::class, 'fetchPresenceTrend']);
    Route::get('/statistiques/filieres/top', [StatistiquesController::class, 'fetchTopFilieres']);
    Route::get('/statistiques/evaluations/stats', [StatistiquesController::class, 'fetchEvaluationsStats']);
    Route::get('/statistiques/periodes/current', [StatistiquesController::class, 'fetchCurrentPeriode']);
    Route::get('/statistiques/periodes', [StatistiquesController::class, 'fetchPeriodes']);
    Route::get('/statistiques/candidatures/en-attente', [StatistiquesController::class, 'NbreCandidaturesEnAttente']);
    Route::get('/statistiques/communication', [StatistiquesController::class, 'fetchCommunicationStats']);
});
