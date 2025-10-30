<?php

use App\Http\Controllers\{
	Admin\AnnouncementController,
	Admin\BlogController,
	AdvertiserController,
	CandidatureController,
	EtudiantController,
	EvaluationController,
	GroupController,
	ConfigurationController,
	ReleveController,

};
use App\Http\Controllers\Admin\{
	AnonymousSheetController,
	ContactController,
	EmploiDuTempController,
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
	UniteEnseignementController,
	UniteValeurController,
	UserController,
	UrgentInfoController
};
use App\Http\Controllers\Admin\ClassCommitteeController;
use App\Http\Controllers\CarteEtudiantController;
use App\Http\Controllers\AnneeScolaireController;
use App\Http\Controllers\Admin\EvaluationRoomController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::controller(FiliereController::class)->prefix('filieres')->name('filieres.')->group(function () {
	Route::get('liste', 'index')->name('index');
	Route::get('ajouter-une-filiere', 'create')->name('create');
	Route::get('{filiere}/a-propos', 'show')->name('show');
	Route::get('{filiere}/modifier', 'edit')->name('edit');
	Route::post('ajouter-une-filiere', 'store')->name('store');
	Route::put('{filiere}/modifier', 'update')->name('update');
	Route::delete('supprimer', 'destroy')->name('delete');
});



// Gestion des unités d'enseignement par l'administration
Route::controller(UniteEnseignementController::class)->prefix('unites-d-enseignement')->name('ues.')->group(function () {
	Route::get('liste', 'index')->name('index');
	Route::get('ajouter-une-ue', 'create')->name('create');
	Route::get('{ue}/a-propos', 'show')->name('show');
	Route::get('{ue}/modifier', 'edit')->name('edit');
	Route::post('ajouter-une-ue', 'store')->name('store');
	Route::put('{ue}/modifier', 'update')->name('update');
	Route::delete('/supprimer', 'destroy')->name('delete');
});

// Gestion des unités de valeur par l'administration
Route::controller(UniteValeurController::class)->prefix('unites-de-valeur')->name('uvs.')->group(function () {
	Route::get('liste', 'index')->name('index');
	Route::get('ajouter-une-matiere', 'create')->name('create');
	Route::get('{uv}/a-propos', 'show')->name('show');
	Route::get('{uv}/modifier', 'edit')->name('edit');
	Route::post('ajouter-une-matiere', 'store')->name('store');
	Route::put('{uv}/modifier', 'update')->name('update');
	Route::delete('/supprimer', 'destroy')->name('delete');
});

// Gestion des périodes par l'administration
Route::controller(PeriodeController::class)->prefix('decoupage-academique')->name('periodes.')->group(function () {
	Route::get('liste', 'index')->name('index');
	Route::get('ajouter-un-decoupage', 'create')->name('create');
	Route::get('{periode}/a-propos', 'show')->name('show');
	Route::get('{periode}/modifier', 'edit')->name('edit');
	Route::post('ajouter-un-decoupage', 'store')->name('store');
	Route::put('{periode}/modifier', 'update')->name('update');
	Route::delete('supprimer', 'destroy')->name('delete');
});


// Gestion des Salles par l'administration
Route::controller(SalleController::class)->prefix('salles')->name('salles.')->group(function () {
	Route::get('liste', 'index')->name('index');
	Route::post('ajouter-une-salle', 'store')->name('store');
	Route::get('{salle}/emploi-du-temps', 'displayCalendar')->name('display-calendar');
	Route::get('{salle}/load-edt', 'loadCalendar')->name('load-calendar');
	Route::delete('supprimer-une-salle', 'destroy')->name('delete');

});

Route::controller(EmploiDuTempController::class)->prefix('emploi-du-temps')->name('edt.')->group(function () {
	Route::post('store', 'store')->name('store');
	Route::put('update-dates', 'updateDates')->name('update-dates');
	Route::put('update', 'update')->name('update');
	Route::get('check-availability', 'checkAvailability')->name('check-availability');
	Route::delete('', 'destroy')->name('delete');
});

// Gestion des Rôles par l'administration
Route::controller(RoleController::class)->prefix('roles')->name('roles.')->group(function () {
	Route::get('liste', 'index')->name('index');
	//		Route::get('ajouter-un-role', 'create')->name('create');
//		Route::get('{role}/a-propos', 'show')->name('show');
//		Route::get('{role}/modifier', 'edit')->name('edit');
//		Route::post('ajouter-un-role', 'store')->name('store');
//		Route::put('{role}/modifier', 'update')->name('update');
//		Route::delete('{role}/supprimer', 'destroy')->name('delete');
});

// Gestion des Utilisateurs par l'administration
Route::controller(UserController::class)->prefix('utilisateurs')->name('users.')->group(function () {
	Route::get('liste/{profil?}', 'index')->name('index');
	Route::get('ajouter-un-utilisateur', 'create')->name('create');
	Route::get('{user}/a-propos', 'show')->name('show');
	Route::get('{user}/modifier', 'edit')->name('edit');
	Route::post('ajouter-un-utilisateur', 'store')->name('store');
	Route::put('{user}/modifier-le-profil', 'update')->name('update');
	Route::delete('/supprimer', 'destroy')->name('delete');

	// Résumé des heures effectuées par enseignant
	Route::get('enseignants/heures', 'hoursSummary')->name('teachers.hours-summary');

	// Emploi du temps des profs
	Route::get('{user}/load-edt', 'loadEmploiDuTemps')->name('load-edt'); // charge les edt de l'utilisateur
	Route::get('{user}/emploi-du-temps', 'ShowEmploiDuTemps')->name('show-edt'); // charge les edt de l'utilisateur
	Route::post('{user}/add-edt', 'storeEmploiDuTemps')->name('store-edt'); // charge les edt de l'utilisateur

	Route::put('/update-edt', 'updateEmploiDuTemps')->name('update-edt'); // charge les edt de l'utilisateur

});

Route::controller(CandidatureController::class)->prefix('candidature')->name('candidatures.')->group(function () {
	Route::get('liste', 'index')->name('index');
	Route::get('payement-des-frais-de-participation', 'payementCandidaturesIndex')->name('payement-des-frais-de-participation');
	Route::get('participation-au-concours', 'participantCandidaturesIndex')->name('participation-au-concours');
	Route::get('admission-a-' . Str::slug(env('APP_NAME')), 'admisCandidaturesIndex')->name('admission');
	Route::get('liste-des-rectifications', 'rectificationIndex')->name('index.rectifications');
	Route::get('liste-des-rejets', 'rejectionIndex')->name('index.rejections');
	Route::get('{candidature}/evaluer', 'show')->name('show');
	Route::get('choix-de-groupe', 'chooseClassAssignmentGroupView')->name('choose-class-assignment-group-view');
	Route::get('attribution-de-groupe/{group}', 'showGroupClassAssignmentView')->name('show-class-assignment-view');
	Route::post('payement-des-frais-de-participation', 'payementCandidaturesStore')->name('payement-des-frais-de-participation.store');
	Route::post('attribution-de-groupe', 'makeCandidatsUser')->name('attribution-de-groupe');
	Route::post('presence-sub', 'presenceControlStore')->name('presence-sub');
	Route::post('admission-sub', 'admissionControl')->name('admission-sub');
	Route::put('{candidature}/valider', action: 'validateCandidature')->name('validate');
	Route::put('{candidature}/rejeter', 'rejectCandidature')->name('reject');
	Route::put('{candidature}/demander-rectification', 'askForRectificationOnCandidature')->name('ask-for-rectification');
    
});

Route::controller(GroupController::class)->prefix('groups')->name('groups.')->group(function () {
	Route::get('{group}/get-matieres', 'getMatieres')->name('get-matieres');
	Route::get('liste', 'index')->name('index');
	Route::get('{group}/attribution-aux-etudiants', 'showGroupAssignmentView')->name('show-attribution-view');
	Route::get('{group}/emploi-du-temps', 'displayCalendar')->name('display-calendar');
	Route::get('{group}/liste-des-etudiants', 'getEtudiants')->name('etudiants');
	Route::get('{group}/load-calendar', 'loadCalendar')->name('load-calendar');
	Route::post('{group}/emploi-du-temps', 'updateCalendar')->name('update-calendar');
	Route::post('ajouter', 'store')->name('store');
	Route::post('{group}/attribution-aux-etudiants-enregistrement', 'storeGroupAssignment')->name('store-attribution');
	Route::delete('supprimer', 'destroy')->name('delete');
});

Route::controller(EtudiantController::class)->prefix('etudiants')->name('etudiants.')->group(function () {
	Route::get('liste', 'index')->name('index');
	Route::get('{etudiant}/details', 'show')->name('show');
	Route::put('{etudiant}/changer-de-groupe', 'changeGroup')->name('change-group');
});

// Gestion du comité de classe
Route::controller(ClassCommitteeController::class)->prefix('comite-de-classe')->name('committee.')->group(function(){
	Route::get('', 'index')->name('index');
	Route::post('', 'store')->name('store');
	Route::delete('', 'destroy')->name('delete');
});

Route::prefix('evaluations')->name('evaluations.')->group(function () {
	Route::controller(EvaluationController::class)->group(function () {
		Route::get('liste', 'index')->name('index');
		Route::get('ajouter', 'create')->name('create');
		Route::get('{evaluation}/detail', 'show')->name('show');
		Route::post('ajouter', 'store')->name('store');
		Route::get('{evaluation}/modifier', 'edit')->name('edit');
		Route::put('{evaluation}/modifier', 'update')->name('update');
		Route::delete('{evaluation}/supprimer', 'destroy')->name('delete');
		Route::get('{slug}/publier', 'publish')->name('publish');
		Route::get('{evaluation}/fiche-de-note', 'getNoteFiche')->name(name: 'fiche-de-note');
	});

	// Allocation de salles, surveillants, répartition étudiants
	Route::controller(EvaluationRoomController::class)->prefix('{evaluation}/rooms')->name('rooms.')->group(function () {
		Route::post('allocate', 'allocate')->name('allocate');
		Route::post('{evaluationRoom}/supervisors', 'setSupervisors')->name('set-supervisors');
		Route::delete('reset', 'reset')->name('reset');
		Route::get('summary', 'summary')->name('summary');
		Route::get('export.csv', 'exportCsv')->name('export');
	});

	Route::controller(NoteController::class)->prefix('{evaluation}/notes')->name('notes.')->group(function () {
		Route::post('enregistrer-note', 'storeNotes')->name('store-notes');
		Route::put('publier-les-notes', 'publishNotes')->name('publish-notes');
		Route::post('changer-note', 'ChangeNotes')->name('change-notes');
		Route::get('export', 'export')->name('export');
	});

	// Routes pour les fiches d'anonymes
	Route::controller(AnonymousSheetController::class)->prefix('{evaluation}/anonymous')->name('anonymous.')->group(function () {
		Route::post('generate', 'generateCodes')->name('generate');
		Route::get('codes', 'showCodes')->name('codes');
		Route::get('print', 'printSheet')->name('print');
		Route::get('export.csv', 'exportCsv')->name('export');
		Route::delete('delete', 'deleteCodes')->name('delete');
	});

	//	Route::get('index', 'index')->name('index');
//	Route::get('index', 'index')->name('index');
});

Route::controller(FicheDePresenceController::class)->prefix('fiches-de-presence')->name('fiches.')->group(function () {
	Route::get('liste', 'index')->name('index');
	Route::post('enregistrer', 'store')->name('store');
	Route::get('{fiche}/remplir', 'make')->name('make');
	Route::put('{fiche}/mettre-a-jour', 'update')->name('update');
	Route::post('{fiche}/soumettre', 'submit')->name('submit');
});

// Validation des absences (admin)
Route::controller(\App\Http\Controllers\Admin\PresenceValidationController::class)
	->prefix('presences')
	->name('presences.')
	->group(function () {
		Route::get('validation', 'index')->name('index');
		Route::post('{presence}/valider', 'validateOne')->name('validate-one');
		Route::post('valider', 'validateBatch')->name('validate-batch');
	});

Route::controller(AdvertiserController::class)->prefix('partenaires')->name('advertisers.')->group(function () {
	Route::get('liste', 'index')->name('index');
	Route::get('ajouter', 'create')->name('create');
	Route::get('{advertiser}/details', 'show')->name('show');
	Route::post('ajouter', 'store')->name('store');
	Route::get('{advertiser}/modifier', 'edit')->name('edit');
	Route::get('{advertiser}/modifier', 'edit')->name('edit');
	Route::put('{advertiser}/supprimer', 'update')->name('update');
	Route::delete('delete', 'destroy')->name('delete');
});


Route::controller(AnnouncementController::class)->prefix('opportunites')->name('announcements.')->group(function () {
	Route::get('liste', 'index')->name('index');
	Route::get('ajouter', 'create')->name('create');
	Route::get('{announcement}/details', 'show')->name('show');
	Route::delete('supprimer', 'destroy')->name('delete');
	Route::post('ajouter', 'store')->name('store');
	Route::get('{announcement}/modifier', 'edit')->name('edit');
	Route::get('{announcement}/modifier', 'edit')->name('edit');
	Route::get('{announcement}/publier', 'publish')->name('publish');
	Route::put('{announcement}/mettre-a-jour', 'update')->name('update');
	Route::get('{announcement}/postulants', 'etudiants')->name('etudiants');
	//	Route::delete('retirer', 'delete')->name('delete');
});

// Route pour les publications
Route::controller(BlogController::class)->prefix('publications')->name('blogs.')->group(function () {
	Route::get('', 'index')->name('index');
	Route::get('ajouter', 'create')->name('create');
	Route::post('nouveau', 'store')->name('store');
	Route::get('{blog}', 'show')->name('show');
	Route::get('{blog}/modifier', 'edit')->name('edit');
	Route::put('{blog}/modifier', 'update')->name('update');
	Route::delete('{blog}', 'delete')->name('destroy');
	Route::post('', 'search')->name('search');
});

// Route pour les publications
Route::controller(EventController::class)->prefix('evenements')->name('events.')->group(function () {
	Route::get('', 'index')->name('index');
	Route::get('ajouter', 'create')->name('create');
	Route::post('', 'store')->name('store');
	Route::get('{event}', 'show')->name('show');
	Route::get('{event}/modifier', 'edit')->name('edit');
	Route::put('{event}/modifier', 'update')->name('update');
	Route::delete('{event}', 'delete')->name('destroy');
});

// Réclamations des étudiants
Route::controller(AdminReclamationController::class)->prefix('reclamations')->name('reclamations.')->group(function () {
	Route::get('', 'index')->name('index');
	Route::put('{reclamation}', 'updateStatus')->name('update');
});


// Route pour les publications
Route::controller(ContactController::class)->prefix('messages')->name('messages.')->group(function () {
	Route::get('', 'index')->name('index');
	Route::get('{contact}', 'show')->name('show');
	Route::get('{contact}/lire', 'read')->name('read');
	Route::delete('{contact}', 'destroy')->name('delete');
});




// Gestion des informations urgentes
Route::controller(UrgentInfoController::class)->prefix('informations-urgentes')->name('urgent_infos.')->group(function () {
	Route::get('liste', 'index')->name('index');
	Route::post('ajouter', 'store')->name('store');
	Route::put('{urgent}/modifier', 'update')->name('update');
	Route::post('{urgent}/publier', 'publish')->name('publish');
	Route::post('{urgent}/depublier', 'unpublish')->name('unpublish');
	Route::delete('{urgent}/supprimer', 'destroy')->name('destroy');
});

// Gestion de la Galerie (Albums & Photos)
Route::prefix('galerie')->name('gallery.')->middleware('can:manage-gallery')->group(function () {
    // Albums
    Route::controller(GalleryAlbumController::class)->prefix('albums')->name('albums.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('ajouter', 'create')->name('create');
        Route::post('', 'store')->name('store');
        Route::get('{galleryAlbum}', 'show')->name('show');
        Route::get('{galleryAlbum}/modifier', 'edit')->name('edit');
        Route::put('{galleryAlbum}', 'update')->name('update');
        Route::delete('{galleryAlbum}', 'destroy')->name('destroy');
    });

    // Photos
    Route::controller(GalleryPhotoController::class)->prefix('photos')->name('photos.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('ajouter', 'create')->name('create');
        Route::post('', 'store')->name('store');
        Route::get('{galleryPhoto}', 'show')->name('show');
        Route::get('{galleryPhoto}/modifier', 'edit')->name('edit');
        Route::put('{galleryPhoto}', 'update')->name('update');
        Route::delete('{galleryPhoto}', 'destroy')->name('destroy');
    });
});

// Routes pour les relevés de notes
Route::controller(ReleveController::class)->prefix('releves')->name('releves.')->group(function () {
    Route::get('generer/{etudiant}', 'generateReleveForStudent')->name('generer');
    Route::get('telecharger/{etudiant}', 'generateReleveForStudent')->name('telecharger');
    Route::get('download/{filename}', 'download')->name('download');
    Route::get('checked', 'checked')->name('checked');
    Route::post('groupe/{group}', 'generateGroupReleves')->name('groupe');
});

// Routes pour les cartes étudiants
Route::controller(CarteEtudiantController::class)->prefix('carte')->name('carte.')->group(function () {
    Route::get('{etudiant}', 'genererCarteEtudiant')->name('index');
});
