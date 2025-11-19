@extends('base')
{{-- Soumission des etudiants pour une evaluation --}}
@section('content')
<h3>Soumissions : {{ $evaluation->title }}</h3>
@foreach($evaluation->submissions as $submission)
    <h4>{{ $submission->etudiant->nom }} {{ $submission->etudiant->prenom }} ({{ $submission->status }})</h4>
    <ul>
        @foreach($submission->answers as $answer)
            <li>
                <strong>{{ $answer->question->title }}:</strong>
                @if($answer->question->type == 'text' || $answer->question->type == 'textarea')
                    {{ $answer->answer_text }}
                @else
                    {{ implode(', ', $answer->question->options->whereIn('id', $answer->answer_options ?? [])->pluck('label')->toArray()) }}
                @endif
            </li>
        @endforeach
    </ul>
@endforeach
@endsection



{{-- use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\EvaluationSubmissionController;

Route::prefix('enseignants')->middleware(['auth:enseignants'])->group(function () {
    Route::get('evaluations/{evaluation}/config', [EvaluationController::class, 'edit'])->name('enseignants.evaluations.config');
    Route::put('evaluations/{evaluation}', [EvaluationController::class, 'update'])->name('enseignants.evaluations.update');
    Route::get('evaluations/{evaluation}/questions', [EvaluationController::class, 'showQuestions'])->name('enseignants.evaluations.questions');
    Route::get('evaluations/{evaluation}/submissions', [EvaluationController::class, 'submissions'])->name('enseignants.evaluations.submissions');
});

Route::prefix('etudiants')->middleware(['auth:etudiants'])->group(function () {
    Route::get('evaluations/{evaluation}', [EvaluationSubmissionController::class, 'show'])->name('etudiants.evaluations.show');
    Route::post('evaluations/{evaluation}/save', [EvaluationSubmissionController::class, 'saveProgress'])->name('etudiants.evaluations.save');
    Route::post('evaluations/{evaluation}/submit', [EvaluationSubmissionController::class, 'submit'])->name('etudiants.evaluations.submit');
}); --}}
