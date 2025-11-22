@extends('professeurs.base', [
    'title' => 'Soumissions des étudiants',
    'page_name' => 'Soumissions des étudiants',
    'breadcrumbs' => ['Évaluations', 'Soumissions'],
])

@section('bases')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Header Card -->
            <div class="card card-glass mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h2 class="h4 mb-2 text-gradient text-primary">
                                <i class="fas fa-list-check me-3"></i>Soumissions des étudiants
                            </h2>
                            <p class="text-sm text-muted mb-0">
                                Consultez et corrigez les réponses soumises par les étudiants
                            </p>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                        <input type="text" id="searchInput" class="form-control border-start-0" 
                                               placeholder="Rechercher un étudiant...">
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="bg-gradient-primary text-white rounded-3 p-3">
                                        <h3 class="h2 mb-0" id="totalSubmissionsCount">
                                            @php
                                                $totalSubmissions = 0;
                                                foreach($evaluation as $eval) {
                                                    $totalSubmissions += $eval->submissions->count();
                                                }
                                            @endphp
                                            {{ $totalSubmissions }}
                                        </h3>
                                        <small class="opacity-8">Soumission(s)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($evaluation->count() > 0 && $totalSubmissions > 0)
                @foreach($evaluation as $eval)
                <!-- Evaluation Card -->
                <div class="card card-glass mb-5 evaluation-card" data-eval-id="{{ $eval->id }}">
                    <div class="card-header bg-transparent px-4 pt-4 pb-0">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div class="icon icon-shape icon-sm bg-gradient-primary text-white rounded-3 me-3">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1 text-dark">{{ $eval->type }}</h5>
                                        <p class="text-sm text-muted mb-0">
                                            {{ \Carbon\Carbon::parse($eval->date)->format('d/m/Y') }} • 
                                            {{ \Carbon\Carbon::parse($eval->debut)->format('H:i') }} - {{ \Carbon\Carbon::parse($eval->fin)->format('H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-gradient-secondary submission-count" data-original-count="{{ $eval->submissions->count() }}">
                                    {{ $eval->submissions->count() }} soumission(s)
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Students Grid -->
                        <div class="row g-4 submissions-container">
                            @foreach($eval->submissions as $submission)
                            <div class="col-xl-4 col-lg-6 col-md-6 submission-item" 
                                 data-name="{{ strtolower($submission->etudiant->prenom . ' ' . $submission->etudiant->nom) }}"
                                 data-matricule="{{ strtolower($submission->etudiant->matricule) }}"
                                 data-eval-id="{{ $eval->id }}">
                                <div class="card card-hover border-0 shadow-sm h-100">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="icon icon-shape icon-md bg-gradient-primary text-white rounded-3 me-3">
                                                <i class="fas fa-user-graduate"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 text-dark student-name">{{ $submission->etudiant->prenom }} {{ $submission->etudiant->nom }}</h6>
                                                <p class="text-xs text-muted mb-0 student-matricule">
                                                    <i class="fas fa-id-card me-1"></i>{{ $submission->etudiant->matricule }}
                                                </p>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link text-dark px-0" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#submissionModal{{ $submission->id }}">
                                                            <i class="fas fa-eye me-2"></i>Voir réponses
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <!-- Status & Time -->
                                        <div class="row text-center g-2 mb-3">
                                            <div class="col-6">
                                                <div class="border rounded-2 p-2">
                                                    <p class="text-xs text-muted mb-1">Début</p>
                                                    <p class="text-sm font-weight-bold mb-0">
                                                        {{ \Carbon\Carbon::parse($submission->started_at)->format('H:i') }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="border rounded-2 p-2">
                                                    <p class="text-xs text-muted mb-1">Soumission</p>
                                                    <p class="text-sm font-weight-bold mb-0 {{ $submission->submitted_at ? 'text-success' : 'text-warning' }}">
                                                        @if($submission->submitted_at)
                                                            {{ \Carbon\Carbon::parse($submission->submitted_at)->format('H:i') }}
                                                        @else
                                                            En cours
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Progress & Actions -->
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge badge-sm bg-gradient-{{ $submission->status === 'submitted' ? 'success' : 'warning' }}">
                                                {{ $submission->status === 'submitted' ? 'Soumis' : 'En cours' }}
                                            </span>
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-icon-only btn-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#submissionModal{{ $submission->id }}"
                                                        title="Voir réponses">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Message si aucun résultat -->
                        <div class="no-results text-center py-5" style="display: none;">
                            <div class="icon icon-shape icon-lg bg-gradient-warning text-white rounded-3 mb-3 mx-auto">
                                <i class="fas fa-search"></i>
                            </div>
                            <h5 class="text-gradient text-warning mb-2">Aucun résultat trouvé</h5>
                            <p class="text-muted">Aucun étudiant ne correspond à votre recherche.</p>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <!-- Empty State -->
                <div class="card card-glass">
                    <div class="card-body text-center py-6">
                        <div class="icon icon-shape icon-lg bg-gradient-secondary text-white rounded-3 mb-4 mx-auto">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <h5 class="text-gradient text-secondary mb-2">Aucune soumission pour le moment</h5>
                        <p class="text-muted">Les étudiants n'ont pas encore soumis leurs réponses.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modals pour afficher les réponses détaillées -->
@foreach($evaluation as $eval)
    @foreach($eval->submissions as $submission)
    <div class="modal fade" id="submissionModal{{ $submission->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient-primary text-white">
                    <div class="d-flex align-items-center">
                        <div class="icon icon-shape icon-md bg-white text-primary rounded-3 me-3">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0">
                                {{ $submission->etudiant->prenom }} {{ $submission->etudiant->nom }}
                            </h5>
                            <p class="text-xs text-white opacity-8 mb-0">
                                {{ $submission->etudiant->matricule }}
                            </p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-0">
                    <!-- Header Info -->
                    <div class="bg-light border-bottom p-3">
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div>
                                    <p class="text-xs text-muted mb-0">Début</p>
                                    <p class="text-sm font-weight-bold mb-0">
                                        {{ \Carbon\Carbon::parse($submission->started_at)->format('H:i') }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div>
                                    <p class="text-xs text-muted mb-0">Soumission</p>
                                    <p class="text-sm font-weight-bold mb-0 {{ $submission->submitted_at ? 'text-success' : 'text-warning' }}">
                                        @if($submission->submitted_at)
                                            {{ \Carbon\Carbon::parse($submission->submitted_at)->format('H:i') }}
                                        @else
                                            En attente
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div>
                                    <p class="text-xs text-muted mb-0">Statut</p>
                                    <span class="badge bg-gradient-{{ $submission->status === 'submitted' ? 'success' : 'warning' }}">
                                        {{ $submission->status === 'submitted' ? 'Soumis' : 'En cours' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Questions & Answers -->
                    <div class="p-3" style="max-height: 60vh; overflow-y: auto;">
                        @if($submission->answers->count() > 0)
                            @foreach($submission->answers as $answer)
                            <div class="card card-glass mb-3">
                                <div class="card-header bg-transparent px-3 py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 text-dark" style="font-size: 0.9rem;">
                                            <span class="badge bg-gradient-primary me-2">Q{{ $loop->iteration }}</span>
                                            {{ Str::limit($answer->question->title, 50) }}
                                        </h6>
                                        <span class="badge bg-gradient-secondary" style="font-size: 0.7rem;">
                                            {{ $answer->question->points }} pts
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <!-- Question Statement -->
                                    <div class="mb-3">
                                        <p class="text-xs text-muted mb-1">Énoncé :</p>
                                        <p class="text-dark mb-0" style="font-size: 0.85rem;">
                                            {{ Str::limit($answer->question->statement, 150) }}
                                        </p>
                                    </div>

                                    <!-- Student Answer -->
                                    <div class="mb-3">
                                        <p class="text-xs text-muted mb-1">Réponse :</p>
                                        @if($answer->question->type === 'textarea' || $answer->question->type === 'text')
                                            <div class="bg-gradient-light border-radius-lg p-2">
                                                <pre class="text-dark mb-0" style="white-space: pre-wrap; font-family: inherit; font-size: 0.8rem; line-height: 1.4; max-height: 100px; overflow-y: auto;">{{ $answer->answer_text ?? 'Aucune réponse' }}</pre>
                                            </div>
                                        @elseif($answer->question->type === 'choice_single')
                                            @if($answer->answer_options && count($answer->answer_options) > 0)
                                                @php
                                                    // Trouver l'option correspondante dans les options de la question
                                                    $selectedOptionId = $answer->answer_options[0];
                                                    $selectedOption = $answer->question->options->firstWhere('id', $selectedOptionId);
                                                    $selectedLabel = $selectedOption ? $selectedOption->label : 'Option non trouvée';
                                                @endphp
                                                <div class="alert alert-success border-radius-lg py-2" style="font-size: 0.8rem;">
                                                    <i class="fas fa-check-circle me-2"></i>
                                                    <strong>Réponse :</strong> {{ $selectedLabel }}
                                                </div>
                                            @else
                                                <div class="alert alert-warning border-radius-lg py-2" style="font-size: 0.8rem;">
                                                    <i class="fas fa-exclamation-circle me-2"></i>
                                                    Aucune réponse
                                                </div>
                                            @endif
                                        @elseif($answer->question->type === 'choice_multiple')
                                            @if($answer->answer_options && count($answer->answer_options) > 0)
                                                @php
                                                    // Trouver les labels des options sélectionnées
                                                    $selectedLabels = [];
                                                    foreach ($answer->answer_options as $optionId) {
                                                        $option = $answer->question->options->firstWhere('id', $optionId);
                                                        if ($option) {
                                                            $selectedLabels[] = $option->label;
                                                        }
                                                    }
                                                @endphp
                                                <div class="alert alert-success border-radius-lg p-2" style="font-size: 0.8rem;">
                                                    <i class="fas fa-check-double me-2"></i>
                                                    <strong>Réponses :</strong>
                                                    <div class="mt-1">
                                                        @foreach($selectedLabels as $label)
                                                        <span class="badge bg-gradient-primary me-1 mb-1" style="font-size: 0.7rem;">{{ $label }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <div class="alert alert-warning border-radius-lg py-2" style="font-size: 0.8rem;">
                                                    <i class="fas fa-exclamation-circle me-2"></i>
                                                    Aucune réponse
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-3">
                                <div class="icon icon-shape icon-md bg-gradient-warning text-white rounded-3 mb-2 mx-auto">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <h6 class="text-gradient text-warning mb-1">Aucune réponse</h6>
                                <p class="text-muted" style="font-size: 0.8rem;">Aucune réponse soumise</p>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-light border-radius-lg" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Fermer
                    </button>
                    
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endforeach
@endsection

@section('other-css')
<style>
    .card-glass {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    
    .card-hover {
        transition: all 0.3s ease;
    }
    
    .card-hover:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12) !important;
    }
    
    .icon-shape {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    .icon-xs {
        width: 20px;
        height: 20px;
        font-size: 0.7rem;
    }
    
    .icon-sm {
        width: 28px;
        height: 28px;
        font-size: 0.8rem;
    }
    
    .icon-md {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .icon-lg {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    
    .border-radius-lg {
        border-radius: 0.5rem !important;
    }
    
    .text-gradient {
        background: linear-gradient(45deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .bg-gradient-light {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }
    
    .btn-icon-only {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }
    
    .submission-item {
        transition: all 0.3s ease;
    }
    
    .submission-item.hidden {
        display: none !important;
    }
    
    .highlight {
        background-color: #fff3cd !important;
        border-color: #ffeaa7 !important;
    }
</style>
@endsection

@section('other-js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        let searchTimeout;

        // Recherche en temps réel avec délai
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    performSearch(this.value.trim().toLowerCase());
                }, 300);
            });

            // Effacer la recherche
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    performSearch('');
                }
            });
        }

        function performSearch(searchTerm) {
            let totalVisible = 0;
            let hasVisibleResults = false;

            // Parcourir toutes les évaluations
            document.querySelectorAll('.evaluation-card').forEach(evaluationCard => {
                const evalId = evaluationCard.getAttribute('data-eval-id');
                const submissionsContainer = evaluationCard.querySelector('.submissions-container');
                const noResultsMessage = evaluationCard.querySelector('.no-results');
                const submissionCountBadge = evaluationCard.querySelector('.submission-count');
                const originalCount = parseInt(submissionCountBadge.getAttribute('data-original-count'));
                
                let evalVisibleCount = 0;

                // Parcourir tous les éléments de soumission de cette évaluation
                const submissionItems = evaluationCard.querySelectorAll('.submission-item');
                submissionItems.forEach(item => {
                    const name = item.getAttribute('data-name');
                    const matricule = item.getAttribute('data-matricule');
                    const nameElement = item.querySelector('.student-name');
                    const matriculeElement = item.querySelector('.student-matricule');

                    // Réinitialiser les surlignages
                    if (nameElement) {
                        nameElement.innerHTML = nameElement.textContent;
                    }
                    if (matriculeElement) {
                        matriculeElement.innerHTML = matriculeElement.textContent;
                    }

                    if (searchTerm === '') {
                        // Afficher tout si recherche vide
                        item.style.display = 'block';
                        evalVisibleCount++;
                    } else if (name.includes(searchTerm) || matricule.includes(searchTerm)) {
                        // Afficher et surligner les correspondances
                        item.style.display = 'block';
                        evalVisibleCount++;

                        // Surligner le texte correspondant
                        if (name.includes(searchTerm) && nameElement) {
                            const regex = new RegExp(`(${searchTerm})`, 'gi');
                            nameElement.innerHTML = nameElement.textContent.replace(regex, '<mark class="highlight">$1</mark>');
                        }
                        if (matricule.includes(searchTerm) && matriculeElement) {
                            const regex = new RegExp(`(${searchTerm})`, 'gi');
                            matriculeElement.innerHTML = matriculeElement.textContent.replace(regex, '<mark class="highlight">$1</mark>');
                        }
                    } else {
                        // Cacher les non-correspondances
                        item.style.display = 'none';
                    }
                });

                // Gérer l'affichage du message "Aucun résultat"
                if (noResultsMessage) {
                    if (evalVisibleCount === 0 && searchTerm !== '') {
                        noResultsMessage.style.display = 'block';
                        submissionsContainer.style.display = 'none';
                    } else {
                        noResultsMessage.style.display = 'none';
                        submissionsContainer.style.display = 'block';
                    }
                }

                // Mettre à jour le compteur pour cette évaluation
                if (submissionCountBadge) {
                    submissionCountBadge.textContent = `${evalVisibleCount} soumission(s)`;
                    if (evalVisibleCount === 0 && searchTerm !== '') {
                        submissionCountBadge.classList.add('bg-gradient-warning');
                        submissionCountBadge.classList.remove('bg-gradient-secondary');
                    } else {
                        submissionCountBadge.classList.remove('bg-gradient-warning');
                        submissionCountBadge.classList.add('bg-gradient-secondary');
                    }
                }

                // Cacher complètement l'évaluation si aucun résultat
                if (evalVisibleCount === 0 && searchTerm !== '') {
                    evaluationCard.style.display = 'none';
                } else {
                    evaluationCard.style.display = 'block';
                    hasVisibleResults = true;
                }

                totalVisible += evalVisibleCount;
            });

            // Mettre à jour le compteur total
            const totalCountElement = document.getElementById('totalSubmissionsCount');
            if (totalCountElement) {
                if (searchTerm === '') {
                    totalCountElement.textContent = '{{ $totalSubmissions }}';
                } else {
                    totalCountElement.textContent = totalVisible;
                }
            }

            // Afficher un message si aucun résultat global
            if (!hasVisibleResults && searchTerm !== '') {
                showNoResultsMessage();
            } else {
                hideNoResultsMessage();
            }
        }

        function showNoResultsMessage() {
            let noResultsGlobal = document.getElementById('noResultsGlobal');
            if (!noResultsGlobal) {
                noResultsGlobal = document.createElement('div');
                noResultsGlobal.id = 'noResultsGlobal';
                noResultsGlobal.className = 'card card-glass mb-4';
                noResultsGlobal.innerHTML = `
                    <div class="card-body text-center py-5">
                        <div class="icon icon-shape icon-lg bg-gradient-warning text-white rounded-3 mb-3 mx-auto">
                            <i class="fas fa-search"></i>
                        </div>
                        <h5 class="text-gradient text-warning mb-2">Aucun résultat trouvé</h5>
                        <p class="text-muted">Aucun étudiant ne correspond à votre recherche "<strong id="searchTermText"></strong>"</p>
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="clearSearch()">
                            <i class="fas fa-times me-1"></i>Effacer la recherche
                        </button>
                    </div>
                `;
                document.querySelector('.container-fluid .row .col-12').appendChild(noResultsGlobal);
            }
            document.getElementById('searchTermText').textContent = document.getElementById('searchInput').value;
            noResultsGlobal.style.display = 'block';
        }

        function hideNoResultsMessage() {
            const noResultsGlobal = document.getElementById('noResultsGlobal');
            if (noResultsGlobal) {
                noResultsGlobal.style.display = 'none';
            }
        }

        function clearSearch() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.value = '';
                performSearch('');
                searchInput.focus();
            }
        }

        // Exposer la fonction globalement
        window.clearSearch = clearSearch;
    });

    function downloadSubmission(submissionId) {
        window.location.href = '{{ url("enseignants/evaluation/download-submission") }}/' + submissionId;
    }
</script>
@endsection