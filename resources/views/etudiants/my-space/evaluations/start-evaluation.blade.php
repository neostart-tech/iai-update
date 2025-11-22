@extends('base', [
    'title' => 'Évaluation en Cours',
    'breadcrumbs' => ['Évaluation', 'En Cours'],
    'page_name' => 'Évaluation en Cours',
])

@section('content')
<div class="container-fluid py-4">
    @if ($evaluation)
        @php
            $now = now();
            $debut = $evaluation->debut;
            $fin = $evaluation->fin;
            $isBeforeStart = $now < $debut;
            $isAfterEnd = $now > $fin;
            $isInProgress = !$isBeforeStart && !$isAfterEnd;
        @endphp

        @if($isBeforeStart)
        <!-- Évaluation pas encore commencée -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-warning">
                    <div class="card-header bg-warning text-white text-center">
                        <h4 class="mb-0"><i class="fas fa-clock me-2"></i>Évaluation à venir</h4>
                    </div>
                    <div class="card-body text-center py-5">
                        <div class="waiting-animation mb-4">
                            <i class="fas fa-hourglass-start fa-4x text-warning mb-3"></i>
                        </div>
                        <h3 class="text-warning mb-3">L'évaluation n'a pas encore commencé</h3>
                        <p class="lead mb-4">
                            L'évaluation <strong>{{ $evaluation->type }}</strong> pour la matière 
                            <strong>{{ $evaluation->matiere->nom }}</strong> débutera le 
                            <strong>{{ $debut->format('d/m/Y à H:i') }}</strong>
                        </p>
                        
                        <div class="countdown-before-start mb-4">
                            <div class="card timer-card-waiting">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-3"><i class="fas fa-hourglass-start me-1"></i>Début dans</h6>
                                    <div id="before-start-timer" class="mb-2">
                                        <span id="before-start-countdown" class="display-4 fw-bold text-dark">00:00:00</span>
                                    </div>
                                    <small class="text-muted" id="before-start-status">Préparation en cours...</small>
                                </div>
                            </div>
                        </div>

                        <div class="evaluation-info mt-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-play-circle fa-2x text-primary mb-2"></i>
                                            <h6>Début</h6>
                                            <p class="mb-0 fw-bold">{{ $debut->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-stop-circle fa-2x text-danger mb-2"></i>
                                            <h6>Fin</h6>
                                            <p class="mb-0 fw-bold">{{ $fin->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Conseil :</strong> Revenez sur cette page à l'heure du début de l'évaluation. 
                            La page se rafraîchira automatiquement lorsque l'évaluation commencera.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @elseif($isInProgress)
        <!-- Évaluation en cours -->
        <div class="row">
            <!-- Sidebar avec informations et navigation -->
            <div class="col-lg-3 mb-4">
                <div class="sticky-top" style="top: 20px;">
                    <div class="card evaluation-sidebar shadow-sm">
                        <div class="card-header text-white" style="background-color: #4680ff;">
                            <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Informations</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Type d'évaluation</h6>
                                <p class="mb-0">{{ $evaluation->type }}</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Matière</h6>
                                <p class="mb-0">{{ $evaluation->matiere->nom }}</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Durée</h6>
                                <p class="mb-0">
                                    <i class="far fa-clock me-1"></i>
                                    {{ $evaluation->debut->format('d/m/Y H:i') }} — {{ $evaluation->fin->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-muted mb-1">Statut</h6>
                                <p class="mb-0">
                                    @if($isSubmitted)
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Soumis</span>
                                    @else
                                        <span class="badge" style="background-color: #4680ff;"><i class="fas fa-pencil-alt me-1"></i>En cours</span>
                                    @endif
                                </p>
                            </div>
                            
                            <!-- Navigation rapide vers les questions -->
                            <div class="questions-nav mt-4">
                                <h6 class="text-muted mb-2">Navigation</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($evaluation->questions as $key => $question)
                                        <a href="#question-{{ $question->id }}" 
                                           class="btn btn-sm btn-outline-primary question-nav-btn" 
                                           data-question-id="{{ $question->id }}"
                                           style="border-color: #4680ff; color: #4680ff;">
                                            {{ $key + 1 }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Timer -->
                    <div class="card mt-4 shadow-sm" style="background-color: #4680ff; color: white;">
                        <div class="card-body text-center">
                            <h6 class="mb-2 text-white"><i class="fas fa-hourglass-half me-1"></i>Temps restant</h6>
                            <div id="timer" class="mb-2">
                                <span id="countdown" class="display-5 fw-bold text-white">00:00</span>
                            </div>
                            <div class="progress mb-2" style="height: 6px; background: rgba(255,255,255,0.3);">
                                <div id="timeProgress" class="progress-bar" role="progressbar" style="width: 100%"></div>
                            </div>
                            <small class="text-white" id="timeStatus">Temps restant</small>
                        </div>
                    </div>
                    
                    <!-- Indicateur de sauvegarde automatique -->
                    @if(!$isSubmitted)
                    <div class="autosave-indicator mt-3 text-center">
                        <small id="autosaveStatus" class="text-muted">
                            <i class="fas fa-save me-1"></i>Sauvegarde automatique activée
                        </small>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Contenu principal de l'évaluation -->
            <div class="col-lg-9">
                <div class="card evaluation-main shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $evaluation->type }} : {{ $evaluation->matiere->nom }}</h4>
                        <div class="d-flex align-items-center">
                            <span class="badge me-2" style="background-color: #4680ff;">{{ $evaluation->questions->count() }} questions</span>
                            @if($isSubmitted)
                                <span class="badge bg-success">Soumis</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-body">
                        @php
                            $isDisabled = $isSubmitted ? 'disabled' : '';
                        @endphp

                        <!-- FORMULAIRE -->
                        <form id="evaluationForm" 
                              action="{{ route('etudiants.evaluation.evaluation.submit', $evaluation->id) }}"
                              method="POST">
                            @csrf

                            @foreach ($evaluation->questions as $key => $question)
                                <div class="question-card mb-4" id="question-{{ $question->id }}">
                                    <div class="question-header d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="question-title">
                                            <span class="question-number badge me-2" style="background-color: #4680ff;">{{ $key + 1 }}</span>
                                            {{ $question->title }}
                                        </h5>
                                        <div class="question-actions">
                                            <button type="button" class="btn btn-sm btn-outline-secondary mark-question" data-question-id="{{ $question->id }}" {{ $isDisabled }}>
                                                <i class="far fa-flag"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="question-statement mb-3">
                                        <p class="text-muted">{{ $question->statement }}</p>
                                    </div>

                                    @php
                                        $userAnswerText = $question->user_answer ?? '';
                                        $userAnswerOptions = $question->user_answer_options ?? [];
                                    @endphp

                                    <div class="question-response">
                                        @if ($question->type == 'text' || $question->type == 'textarea')
                                            @if ($question->is_long_text == 1)
                                                <div class="form-group">
                                                    <textarea class="form-control autosave-field response-textarea"
                                                              name="question_{{ $question->id }}" 
                                                              rows="6"
                                                              placeholder="Saisissez votre réponse détaillée ici..."
                                                              {{ $isDisabled }}>{{ $userAnswerText }}</textarea>
                                                    <div class="form-text">
                                                        <span class="char-count">0</span> caractères
                                                    </div>
                                                </div>
                                            @else
                                                <div class="form-group">
                                                    <textarea class="form-control autosave-field response-textarea"
                                                              name="question_{{ $question->id }}" 
                                                              rows="3"
                                                              placeholder="Saisissez votre réponse ici..."
                                                              {{ $isDisabled }}>{{ $userAnswerText }}</textarea>
                                                    <div class="form-text">
                                                        <span class="char-count">0</span> caractères
                                                    </div>
                                                </div>
                                            @endif

                                        @elseif ($question->type == 'choice_single')
                                            <div class="options-list">
                                                @foreach ($question->options as $option)
                                                    <div class="form-check option-item">
                                                        <input class="form-check-input autosave-field"
                                                               type="radio"
                                                               name="question_{{ $question->id }}"
                                                               value="{{ $option->id }}"
                                                               id="option_{{ $option->id }}"
                                                               @if(in_array($option->id, $userAnswerOptions)) checked @endif
                                                               {{ $isDisabled }}>
                                                        <label class="form-check-label" for="option_{{ $option->id }}">
                                                            {{ $option->label }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>

                                        @elseif ($question->type == 'choice_multiple')
                                            <div class="options-list">
                                                @foreach ($question->options as $option)
                                                    <div class="form-check option-item">
                                                        <input class="form-check-input autosave-field"
                                                               type="checkbox"
                                                               name="question_{{ $question->id }}[]"
                                                               value="{{ $option->id }}"
                                                               id="option_{{ $option->id }}"
                                                               @if(is_array($userAnswerOptions) && in_array($option->id, $userAnswerOptions)) checked @endif
                                                               {{ $isDisabled }}>
                                                        <label class="form-check-label" for="option_{{ $option->id }}">
                                                            {{ $option->label }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            @if(!$isSubmitted)
                            <div class="evaluation-actions mt-5 pt-4 border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary" id="saveDraftBtn" {{ $isDisabled }}>
                                            <i class="fas fa-save me-1"></i>Sauvegarder le brouillon
                                        </button>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-success btn-lg px-4" id="submitBtn" {{ $isDisabled }}>
                                            <i class="fas fa-paper-plane me-2"></i>Soumettre l'Évaluation
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @elseif($isAfterEnd)
        <!-- Évaluation terminée - Affichage des réponses de l'étudiant -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-info">
                    <div class="card-header text-white text-center" style="background-color: #4680ff;">
                        <h4 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Évaluation Terminée</h4>
                    </div>
                    <div class="card-body">
                        <!-- En-tête des résultats -->
                        <div class="results-header text-center mb-5">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <i class="fas fa-book fa-2x mb-2" style="color: #4680ff;"></i>
                                            <h6>Matière</h6>
                                            <p class="mb-0 fw-bold">{{ $evaluation->matiere->nom }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <i class="fas fa-tasks fa-2x mb-2" style="color: #4680ff;"></i>
                                            <h6>Type</h6>
                                            <p class="mb-0 fw-bold">{{ $evaluation->type }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <i class="fas fa-calendar-alt fa-2x mb-2" style="color: #4680ff;"></i>
                                            <h6>Date</h6>
                                            <p class="mb-0 fw-bold">{{ $evaluation->debut->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statut de soumission -->
                        <div class="submission-status text-center mb-4">
                            @if($isSubmitted)
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Évaluation soumise le {{ \Carbon\Carbon::parse($evaluation->submitted_at)->format('d/m/Y à H:i') }}</strong>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Évaluation non soumise - Temps écoulé</strong>
                                </div>
                            @endif
                        </div>

                        <!-- Affichage des questions et réponses de l'étudiant -->
                        <div class="questions-results">
                            <h5 class="mb-4 text-center"><i class="fas fa-list-ol me-2"></i>Vos réponses</h5>
                            
                            @foreach ($evaluation->questions as $key => $question)
                                <div class="question-result-card mb-4">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">
                                                <span class="badge me-2" style="background-color: #4680ff;">{{ $key + 1 }}</span>
                                                {{ $question->title }}
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Énoncé de la question -->
                                            <div class="question-statement mb-3">
                                                <p class="text-muted mb-2"><strong>Énoncé :</strong></p>
                                                <p>{{ $question->statement }}</p>
                                            </div>

                                            <!-- Réponse de l'utilisateur -->
                                            <div class="user-answer">
                                                <p class="text-muted mb-2"><strong>Votre réponse :</strong></p>
                                                
                                                @php
                                                    $userAnswerText = $question->user_answer ?? '';
                                                    $userAnswerOptions = $question->user_answer_options ?? [];
                                                @endphp

                                                @if ($question->type == 'text' || $question->type == 'textarea')
                                                    @if(!empty($userAnswerText))
                                                        <div class="answer-text p-3 rounded" style="background-color: rgba(70, 128, 255, 0.1); border: 1px solid #4680ff;">
                                                            <p class="mb-0">{{ $userAnswerText }}</p>
                                                        </div>
                                                    @else
                                                        <div class="answer-empty p-3 rounded" style="background-color: #f8f9fa;">
                                                            <p class="mb-0 text-muted"><i>Aucune réponse fournie</i></p>
                                                        </div>
                                                    @endif

                                                @elseif ($question->type == 'choice_single')
                                                    @if(!empty($userAnswerOptions))
                                                        @php
                                                            $selectedOption = $question->options->firstWhere('id', $userAnswerOptions[0]);
                                                        @endphp
                                                        @if($selectedOption)
                                                            <div class="answer-choice p-3 rounded" style="background-color: rgba(70, 128, 255, 0.1); border: 1px solid #4680ff;">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" checked disabled>
                                                                    <label class="form-check-label fw-bold">
                                                                        {{ $selectedOption->label }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <div class="answer-empty p-3 rounded" style="background-color: #f8f9fa;">
                                                                <p class="mb-0 text-muted"><i>Aucune réponse sélectionnée</i></p>
                                                            </div>
                                                        @endif
                                                    @else
                                                        <div class="answer-empty p-3 rounded" style="background-color: #f8f9fa;">
                                                            <p class="mb-0 text-muted"><i>Aucune réponse sélectionnée</i></p>
                                                        </div>
                                                    @endif

                                                @elseif ($question->type == 'choice_multiple')
                                                    @if(!empty($userAnswerOptions) && is_array($userAnswerOptions))
                                                        <div class="answer-choices p-3 rounded" style="background-color: rgba(70, 128, 255, 0.1); border: 1px solid #4680ff;">
                                                            @foreach($question->options as $option)
                                                                <div class="form-check">
                                                                    <input class="form-check-input" 
                                                                           type="checkbox" 
                                                                           {{ in_array($option->id, $userAnswerOptions) ? 'checked' : '' }} 
                                                                           disabled>
                                                                    <label class="form-check-label {{ in_array($option->id, $userAnswerOptions) ? 'fw-bold' : '' }}" style="{{ in_array($option->id, $userAnswerOptions) ? 'color: #4680ff;' : '' }}">
                                                                        {{ $option->label }}
                                                                        @if(in_array($option->id, $userAnswerOptions))
                                                                            <span class="badge ms-2" style="background-color: #4680ff;">Sélectionné</span>
                                                                        @endif
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="answer-empty p-3 rounded" style="background-color: #f8f9fa;">
                                                            <p class="mb-0 text-muted"><i>Aucune réponse sélectionnée</i></p>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Actions -->
                        <div class="results-actions mt-5 text-center">
                            <a href="{{ route('etudiants.evaluation.liste') }}" class="btn btn-primary" style="background-color: #4680ff; border-color: #4680ff;">
                                <i class="fas fa-history me-2"></i>Retour à l'historique
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @else
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i>Évaluation non trouvée.
    </div>
    @endif
</div>
@endsection

@section('other-css')
<style>
    :root {
        --primary-color: #4680ff;
        --secondary-color: #3a6bf0;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --light-bg: #f8f9fa;
    }
    
    .evaluation-sidebar {
        border-left: 4px solid var(--primary-color);
    }
    
    /* TIMER */
    .timer-card-waiting {
        background: linear-gradient(135deg, #ffd166 0%, #ffb347 100%);
        color: #333;
        border: none;
        transition: all 0.5s ease;
    }
    
    .evaluation-main {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .question-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
        scroll-margin-top: 100px;
    }
    
    .question-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        border-left-color: var(--primary-color);
    }
    
    .question-title {
        font-weight: 600;
        color: #2d3748;
    }
    
    .question-number {
        font-size: 0.8rem;
        padding: 0.3rem 0.6rem;
    }
    
    .options-list {
        padding-left: 0.5rem;
    }
    
    .option-item {
        padding: 0.5rem 0;
        border-radius: 5px;
        transition: background-color 0.2s;
    }
    
    .option-item:hover {
        background-color: rgba(70, 128, 255, 0.05);
    }
    
    /* Styles améliorés pour les textarea */
    .response-textarea {
        resize: vertical;
        min-height: 80px;
        transition: all 0.3s ease;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 12px;
        font-size: 14px;
        line-height: 1.5;
    }
    
    .response-textarea:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(70, 128, 255, 0.25);
        outline: none;
    }
    
    .response-textarea:disabled {
        background-color: #f8f9fa;
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    /* Textarea pour réponses longues */
    .response-textarea[rows="6"] {
        min-height: 150px;
    }
    
    /* Textarea pour réponses courtes */
    .response-textarea[rows="3"] {
        min-height: 80px;
        max-height: 200px;
    }
    
    .form-text {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 5px;
        font-size: 0.8rem;
    }
    
    .char-count {
        font-weight: 600;
        color: var(--primary-color);
        background: rgba(70, 128, 255, 0.1);
        padding: 2px 6px;
        border-radius: 4px;
    }
    
    .question-nav-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        text-decoration: none;
    }
    
    .question-nav-btn:hover, .question-nav-btn.active {
        background-color: var(--primary-color);
        color: white;
        transform: translateY(-2px);
    }
    
    .mark-question.active {
        color: var(--warning-color);
    }
    
    #timer {
        transition: all 0.5s ease;
    }
    
    .blink {
        animation: blinker 1s linear infinite;
    }
    
    @keyframes blinker {
        50% { opacity: 0.7; }
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.02); }
        100% { transform: scale(1); }
    }

    /* Animation pour l'attente */
    .waiting-animation {
        animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(-10px);
        }
        60% {
            transform: translateY(-5px);
        }
    }
    
    .autosave-indicator {
        padding: 0.5rem;
        border-radius: 5px;
        background-color: rgba(70, 128, 255, 0.1);
    }
    
    /* Styles pour la sidebar fixe */
    .sticky-top {
        z-index: 100;
    }
    
    /* Amélioration du scroll */
    html {
        scroll-behavior: smooth;
    }

    /* Styles pour le compte à rebours avant début */
    .countdown-before-start {
        max-width: 400px;
        margin: 0 auto;
    }

    #before-start-countdown {
        font-family: 'Courier New', monospace;
        color: #e67700;
    }

    /* Progress bar states */
    .progress-bar.bg-success {
        background-color: var(--primary-color) !important;
    }
    
    .progress-bar.bg-warning {
        background-color: var(--warning-color) !important;
    }
    
    .progress-bar.bg-danger {
        background-color: var(--danger-color) !important;
    }

    /* Styles pour les résultats */
    .question-result-card .card {
        border-left: 4px solid var(--primary-color);
    }
    
    .answer-text, .answer-choice, .answer-choices {
        border: 1px solid #4680ff;
    }
</style>
@endsection

@section('other-js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let autosaveEnabled = {{ $isSubmitted ? 'false' : 'true' }};
    let formLocked = {{ $isSubmitted ? 'true' : 'false' }};
    let markedQuestions = new Set();
    
    @if(isset($isBeforeStart) && $isBeforeStart)
        // Gestion du compte à rebours avant le début
        const beforeStartCountdownElem = document.getElementById("before-start-countdown");
        const beforeStartStatusElem = document.getElementById("before-start-status");
        let evaluationStartTime = new Date("{{ $evaluation->debut->format('Y-m-d H:i:s') }}").getTime();

        function updateBeforeStartTimer() {
            let now = new Date().getTime();
            let distance = evaluationStartTime - now;

            if (distance <= 0) {
                // L'évaluation commence maintenant, on recharge la page
                beforeStartCountdownElem.innerHTML = "00:00:00";
                beforeStartStatusElem.textContent = "L'évaluation commence maintenant !";
                
                Swal.fire({
                    title: 'Évaluation disponible !',
                    text: "L'évaluation peut maintenant commencer.",
                    icon: 'success',
                    confirmButtonText: 'Commencer'
                }).then(() => {
                    window.location.reload();
                });
                return;
            }

            // Calcul du temps restant
            let days = Math.floor(distance / (1000 * 60 * 60 * 24));
            let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((distance % (1000 * 60)) / 1000);

            let timeString = '';
            if (days > 0) {
                timeString = days + "j " + hours.toString().padStart(2, '0') + ":" + minutes.toString().padStart(2, '0') + ":" + seconds.toString().padStart(2, '0');
                beforeStartStatusElem.textContent = "Début dans " + days + " jour" + (days > 1 ? "s" : "");
            } else if (hours > 0) {
                timeString = hours.toString().padStart(2, '0') + ":" + minutes.toString().padStart(2, '0') + ":" + seconds.toString().padStart(2, '0');
                beforeStartStatusElem.textContent = "Début dans " + hours + " heure" + (hours > 1 ? "s" : "");
            } else {
                timeString = minutes.toString().padStart(2, '0') + ":" + seconds.toString().padStart(2, '0');
                beforeStartStatusElem.textContent = "Début dans " + minutes + " minute" + (minutes > 1 ? "s" : "");
                
                // Dernière minute : on accélère le rafraîchissement
                if (minutes < 1) {
                    clearInterval(beforeStartTimerInterval);
                    beforeStartTimerInterval = setInterval(updateBeforeStartTimer, 1000);
                }
            }
            
            beforeStartCountdownElem.innerHTML = timeString;
        }

        let beforeStartTimerInterval = setInterval(updateBeforeStartTimer, 5000);
        updateBeforeStartTimer();

    @elseif(isset($isInProgress) && $isInProgress)
        // Gestion du timer pendant l'évaluation
        const countdownElem = document.getElementById("countdown");
        const timerElem = document.getElementById("timer");
        const timerCard = document.querySelector('.timer-card');
        const timeProgress = document.getElementById("timeProgress");
        const timeStatus = document.getElementById("timeStatus");
        const autosaveStatus = document.getElementById("autosaveStatus");
        let countdownTime = new Date("{{ $evaluation->fin->format('Y-m-d H:i:s') }}").getTime();
        let totalTime = countdownTime - new Date("{{ $evaluation->debut->format('Y-m-d H:i:s') }}").getTime();

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            updateTimer();
            initQuestionNavigation();
            initMarkQuestions();
            initCharCounters();
            
            // Si déjà soumis, désactiver tout
            if ({{ $isSubmitted ? 'true' : 'false' }}) {
                disableForm();
            }
        });

        function updateTimer() {
            if(formLocked) return;

            let now = new Date().getTime();
            let distance = countdownTime - now;
            let progressWidth = (distance / totalTime) * 100;

            // Mettre à jour la barre de progression
            timeProgress.style.width = Math.max(0, progressWidth) + '%';

            if(distance <= 0) {
                formLocked = true;
                autosaveEnabled = false;
                countdownElem.innerHTML = "00:00";
                timeStatus.textContent = "Temps écoulé";
                timeProgress.style.width = '0%';

                disableForm();
                clearInterval(timerInterval);
                clearInterval(autosaveInterval);
                
                // Avertissement de fin de temps
                Swal.fire({
                    title: 'Temps écoulé',
                    text: "Le temps imparti pour cette évaluation est écoulé.",
                    icon: 'warning',
                    confirmButtonText: 'Compris'
                }).then(() => {
                    window.location.reload();
                });
                return;
            }

            let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            let min = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            let sec = Math.floor((distance % (1000 * 60)) / 1000);
            
            let timeString = '';
            if (hours > 0) {
                timeString = hours + ":" + (min < 10 ? "0" + min : min) + ":" + (sec < 10 ? "0" + sec : sec);
            } else {
                timeString = min + ":" + (sec < 10 ? "0" + sec : sec);
            }
            
            countdownElem.innerHTML = timeString;

            // Gestion des états du timer
            timeProgress.classList.remove("bg-warning", "bg-danger");
            
            if(distance <= 5*60*1000) { // < 5 min
                timeProgress.classList.add("bg-danger");
                timeStatus.textContent = "Dernières minutes";
                if (!countdownElem.classList.contains('blink')) {
                    countdownElem.classList.add('blink');
                }
            } else if(distance <= 15*60*1000) { // 5-15 min
                timeProgress.classList.add("bg-warning");
                timeStatus.textContent = "Presque terminé";
                countdownElem.classList.remove('blink');
            } else { // >15 min
                timeProgress.classList.add("bg-success");
                timeStatus.textContent = "Temps restant";
                countdownElem.classList.remove('blink');
            }
        }

        let timerInterval = setInterval(updateTimer, 1000);

        function disableForm() {
            document.querySelectorAll("#evaluationForm input, #evaluationForm textarea, #evaluationForm button")
                .forEach(el => el.setAttribute("disabled", true));
        }

        function autosave() {
            if (!autosaveEnabled) return;
            
            // Mettre à jour l'indicateur
            autosaveStatus.innerHTML = '<i class="fas fa-sync-alt fa-spin me-1"></i>Sauvegarde en cours...';
            
            let form = document.getElementById("evaluationForm");
            let formData = new FormData(form);

            fetch("{{ route('etudiants.evaluation.evaluation.autosave', $evaluation->id) }}", {
                method: "POST",
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                autosaveStatus.innerHTML = '<i class="fas fa-check me-1"></i>Dernière sauvegarde: ' + new Date().toLocaleTimeString();
                
                // Réinitialiser après 3 secondes
                setTimeout(() => {
                    autosaveStatus.innerHTML = '<i class="fas fa-save me-1"></i>Sauvegarde automatique activée';
                }, 3000);
            })
            .catch(error => {
                autosaveStatus.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Erreur de sauvegarde';
                console.error('Erreur de sauvegarde:', error);
            });
        }

        let autosaveInterval = setInterval(() => {
            if (autosaveEnabled) autosave();
        }, 30000);

        // Sauvegarde manuelle
        document.getElementById('saveDraftBtn').addEventListener('click', function() {
            autosave();
            Swal.fire({
                title: 'Brouillon sauvegardé',
                text: 'Vos réponses ont été sauvegardées avec succès.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        });

        document.querySelectorAll('.autosave-field').forEach(elem => {
            elem.addEventListener('change', autosave);
            elem.addEventListener('keyup', autosave);
        });

        // Navigation entre les questions
        function initQuestionNavigation() {
            document.querySelectorAll('.question-nav-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const questionId = this.getAttribute('data-question-id');
                    const targetElement = document.getElementById('question-' + questionId);
                    
                    if (targetElement) {
                        document.querySelectorAll('.question-nav-btn').forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                        
                        const sidebarHeight = document.querySelector('.sticky-top').offsetHeight;
                        const offsetPosition = targetElement.offsetTop - 100;
                        
                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });
                        
                        targetElement.style.backgroundColor = 'rgba(70, 128, 255, 0.05)';
                        setTimeout(() => {
                            targetElement.style.backgroundColor = '';
                        }, 2000);
                    }
                });
            });
        }

        // Marquer les questions pour révision
        function initMarkQuestions() {
            document.querySelectorAll('.mark-question').forEach(btn => {
                btn.addEventListener('click', function() {
                    const questionId = this.getAttribute('data-question-id');
                    
                    if (markedQuestions.has(questionId)) {
                        markedQuestions.delete(questionId);
                        this.classList.remove('active');
                        this.innerHTML = '<i class="far fa-flag"></i>';
                    } else {
                        markedQuestions.add(questionId);
                        this.classList.add('active');
                        this.innerHTML = '<i class="fas fa-flag"></i>';
                    }
                });
            });
        }

        // Compteur de caractères pour TOUS les textarea
        function initCharCounters() {
            document.querySelectorAll('.response-textarea').forEach(textarea => {
                const charCount = textarea.parentNode.querySelector('.char-count');
                
                charCount.textContent = textarea.value.length;
                
                textarea.addEventListener('input', function() {
                    charCount.textContent = this.value.length;
                    
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
                
                textarea.style.height = 'auto';
                textarea.style.height = (textarea.scrollHeight) + 'px';
            });
        }

        // Confirmation avant soumission
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('evaluationForm');

        submitBtn.addEventListener('click', function(e){
            e.preventDefault(); 
            
            let markedWarning = '';
            if (markedQuestions.size > 0) {
                markedWarning = `<br><br><div class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i>Vous avez marqué ${markedQuestions.size} question(s) pour révision.</div>`;
            }
            
            Swal.fire({
                title: 'Confirmer la soumission',
                html: `Êtes-vous sûr de vouloir soumettre l'évaluation ?${markedWarning}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, soumettre',
                cancelButtonText: 'Annuler',
                footer: '<small>Après soumission, vous ne pourrez plus modifier vos réponses.</small>'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Soumission en cours...';
                    
                    form.submit(); 
                }
            });
        });

    @endif

    // Afficher les messages flash
    @if(session('succes'))
        Swal.fire({
            title: 'Succès',
            text: '{{ session("succes") }}',
            icon: 'success',
            confirmButtonText: 'OK'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            title: 'Erreur',
            text: '{{ session("error") }}',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    @endif

    @if(session('warning'))
        Swal.fire({
            title: 'Attention',
            text: '{{ session("warning") }}',
            icon: 'warning',
            confirmButtonText: 'OK'
        });
    @endif

    // Si déjà soumis, timer "Terminé"
    @if($isSubmitted && isset($isInProgress))
        countdownElem.innerHTML = "Terminé";
        timeStatus.textContent = "Évaluation soumise";
        timeProgress.style.width = '0%';
    @endif
</script>
@endsection