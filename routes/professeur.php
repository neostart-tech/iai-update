<?php

use App\Http\Controllers\Enseignant\NoteEntryController;
use App\Http\Controllers\enseignantAuth\AuthentificationSessionController;
use App\Http\Controllers\EspaceProfesseurControleur;
use App\http\Controllers\EvaluationController;
use Illuminate\Support\Facades\Route;

// NOTE: This file is loaded by RouteServiceProvider with prefix('espace-enseignant') and name('enseignants.')

Route::middleware('guest:enseignants')
    ->controller(AuthentificationSessionController::class)
    ->name('auth.')
    ->prefix('me-connecter')
    ->group(function () {
        Route::get('', 'login')->name('login');
        Route::post('/se-connecter', 'store')->name('store');
        Route::delete('/se-déconnecter', 'destroy')->name('logout');
    });

Route::middleware('auth:enseignants')->controller(EvaluationController::class)->group(function () {
    Route::get('/enseignant/evaluations/create', 'create');
    Route::post('/enseignant/evaluations', 'store');
});

Route::middleware('auth:enseignants')
    ->controller(EspaceProfesseurControleur::class)
    ->group(function () {
        Route::get('mon-emploi-de-temps', 'show')->name('index');
        Route::get('mes-cours', 'mescours')->name('cours.show');
        Route::get('etudiants/{group}/liste', 'listeetudiant')->name('etudiant.show');

        // Presence views
        Route::get('presence/vue/{emploi_du_temps_id}', 'vuePresence')->name('presence.view');
        Route::get('presence/{cours_id}/{date}', 'listePresence')->name('presence.list');
        Route::get('presence/{emploi_du_temps_id}/stats', 'presenceStats')->name('presence.stats');

        // Cours du professeur connectté
        Route::get('cours-du-jour', 'mesCoursShow')->name('cours.du.jour');
        Route::get('mes-evaluations', 'mesEvaluationsShow')->name('mes-evaluations');

        // Routes de gestion des evalautions

        // Legacy endpoints with {group} param
        Route::post('enregistrement/{group}/presence', 'storePresence')->name('presence.store');
        Route::post('enregistrement/{group}/cahier-de-texte', 'storeCahierDeTexte')->name('cahier-de-texte.store');
        Route::post('enregistrement/{group}/devoir', 'storeDevoir')->name('devoir.store');

        // New simplified endpoints used by the frontend
        Route::post('enregistrement-absences', 'enregistrerAbsences')->name('absences.store');

        // Teacher presence endpoints
        Route::post('presence/enseignant', 'saveTeacherPresence')->name('presence.teacher.save');
        Route::get('presence/enseignant/{emploi_du_temps_id}', 'getTeacherPresence')->name('presence.teacher.get');

        // Export recap presence file
        Route::get('presence/{emploi_du_temps_id}/export', function ($emploi_du_temps_id) {
            $emploi = \App\Models\EmploiDuTemp::findOrFail($emploi_du_temps_id);
            $date = $emploi->debut ? date('Y-m-d', strtotime($emploi->debut)) : now()->toDateString();
            $cours = \App\Models\Cours::where('uv_id', $emploi->uv_id)
                ->where('groupe_id', $emploi->group_id)
                ->whereDate('date_cours', $date)
                ->firstOrFail();

            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\CoursAttendanceExport($cours, $emploi), 'recap_presence.xlsx');
        })->name('presence.export');
        Route::post('enregistrement-cahier-texte', 'enregistrerCahierTexte')->name('cahier.store');
        Route::post('enregistrement-devoir', 'enregistrerDevoir')->name('devoir.simple.store');

        // Data endpoints
        Route::get('cahier-texte/{emploi_du_temps_id}', 'getCahierTexte')->name('cahier.show');
        Route::get('devoir/{emploi_du_temps_id}', 'getDevoir')->name('devoir.show');
        Route::get('absences/{emploi_du_temps_id}', 'getAbsences')->name('absences.show');
    });

// Notes entry by enseignants
Route::middleware('auth:enseignants')
    ->controller(NoteEntryController::class)
    ->prefix('notes')
    ->name('notes.')
    ->group(function () {
        Route::get('{evaluation}', 'index')->name('index');
        Route::post('{evaluation}', 'store')->name('store');
    });

// Legacy alternative prefix kept for compatibility
Route::middleware('auth:enseignants')
    ->controller(EspaceProfesseurControleur::class)
    ->prefix('espace-professeur')
    ->name('professeurs.')
    ->group(function () {
        Route::get('', 'show')->name('index');
        Route::get('mes-cours', 'myCourses')->name('my-courses');
        Route::get('mes-etudiants', 'myStudents')->name('my-students');
    });

Route::middleware('auth:enseignants')
    ->controller(EvaluationController::class)
    ->name('evaluation.')
    ->group(function () {

        Route::get('evaluations/{emploiDuTemp}/config', 'editEvaluation')->name('config');
        Route::put('evaluations/{emploiDuTemp}', 'updateEvaluation')->name('update');
        Route::get('evaluations/{id}/questions', 'showQuestions')->name('questions');
        Route::get('evaluations/{emploiDuTemp}/submissions', 'submissions')->name('submissions');
        route::get('evaluations/{id}/create-question-evaluation', 'createQuestionEvaluation')->name('create-question-evaluation');
        route::post('evaluations/{id}/store-evaluation-question', 'StoreEvaluationQuestion')->name('store-evaluation-question');
        route::get('evaluations/{id}/student-evaluation-submission', 'getStudentEvaluationSubmission')->name('student-evaluation-submission');
        route::get('evaluations/{id}/student-evaluation-submission/index', 'getStudentEvaluationSubmissionview')->name('student-evaluation-submission-view');

    });
