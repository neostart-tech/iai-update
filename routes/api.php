<?php

use App\Http\Controllers\Admin\NegociationController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ReclamationController;
use App\Http\Controllers\Api\Admin\CandidaturePresenceController;
use App\Http\Controllers\Api\Etudiant\MonParcoursController;
use App\Http\Controllers\Api\SemoaCallBackController;
use App\Http\Controllers\BourseController;
use App\Http\Controllers\BroadcastAuthController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DashboardPaiementController;
use App\Http\Controllers\messageController;
use App\Http\Controllers\MyCalendarController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\PaiementExportController;
use App\Http\Controllers\PaiementGlobalController;
use App\Http\Controllers\PlanPaiementController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\PresenceExportController;
use App\Http\Controllers\TranchePaiementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
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

    Route::controller(PresenceController::class)->prefix('presence')->group(function () {
        Route::get('/get-my-course', 'mesCours');
        Route::post('/save-student-presence', 'enregistrerAbsences');
    });

    Route::get('/presences/export/{emploiDuTempsId}', [PresenceExportController::class, 'exportByCours']);
    Route::post('/presences/export/filtered', [PresenceExportController::class, 'exportWithFilters']);


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

    // Dashboard des négociations
    Route::controller(NegociationController::class)->prefix('admin')->group(function () {

        Route::get('negociations/dashboard', 'dashboard');

        // Ressource complète pour les négociations
        Route::resource('negociations', NegociationController::class);
        Route::post('negociations/{id}/ajouter-paiement',  'ajouterPaiement')
            ->name('negociations.ajouter-paiement');
    });
    Route::controller(NegociationController::class)->group(function () {
        Route::get('negociations/dashboard',  'dashboard');
        Route::get('negociations/{id}',  'show');
        Route::post('negociations/{id}/ajouter-paiement',  'ajouterPaiement');
    });

    Route::controller(PaiementController::class)->prefix('paiements')->group(function () {
        // Recherche d'étudiants
        Route::get('/rechercher-etudiants', 'rechercherEtudiants');

        // Informations de paiement
        Route::get('/infos/{etudiantId}', 'getInfos');

        // Récapitulatif
        Route::get('/recap/{etudiantId}', 'getRecap');

        // Historique des paiements
        Route::get('/historique/{etudiantId}', 'getHistorique');

        // Effectuer un paiement
        Route::post('/', 'store');
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
});



Route::any('semoa-callback-url', SemoaCallBackController::class);

require __DIR__ . '/api_admin_routes.php';

require __DIR__ . '/api_auth.php';

require __DIR__ . '/api_candidature.php';

require __DIR__ . '/api_comptable.php';

require __DIR__ . '/api_etudiant.php';

require __DIR__ . '/api_professeur.php';
