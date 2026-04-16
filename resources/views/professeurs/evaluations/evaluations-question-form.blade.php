@extends('professeurs.base', [
'title' => 'Créer/Éditer une évaluation',
'page_name' => (isset($evaluation) && (isset($evaluation->id) || isset($evaluation['id']))) ? 'Éditer une évaluation' : 'Créer une évaluation',
'breadcrumbs' => ['Évaluations', (isset($evaluation) && (isset($evaluation->id) || isset($evaluation['id']))) ? 'Éditer' : 'Créer'],
])

@section('bases')
<div class="container-fluid py-4">
    <!-- Sidebar avec aperçu et statistiques (MAINTENANT EN HAUT) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Aperçu de l'évaluation</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-value" id="parts-count">0</div>
                                <div class="stat-label">Parties</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-value" id="questions-count">0</div>
                                <div class="stat-label">Questions</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-value" id="total-points">0</div>
                                <div class="stat-label">Points</div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="stat-value" id="points-limit">0/20</div>
                                <div class="stat-label">Limite</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total des points</span>
                            <span class="fw-bold" id="points-summary">0/20 pts</span>
                        </div>
                        <div class="progress" style="height: 12px;">
                            <div id="points-progress" class="progress-bar bg-success" style="width: 0%"></div>
                        </div>
                        <div id="points-warning" class="text-danger small mt-1" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-1"></i>La limite de 20 points est dépassée
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Conseils pratiques</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Limite : 20 points maximum</li>
                                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Ajoutez un contexte d'étude de cas si nécessaire</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Vérifiez l'évaluation avant enregistrement</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Les parties peuvent être réorganisées</li>
                                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Utilisez la prévisualisation avant sauvegarde</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Vérifiez la cohérence des points</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Résumé actuel</h5>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-center">
                            <div class="text-center">
                                <div class="display-6 mb-2" id="live-parts-count">0 parties</div>
                                <div class="display-6 text-primary" id="total-points-badge">0/20 pts</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire principal (MAINTENANT EN DESSOUS) -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-primary">
                            <i class="fas fa-{{ (isset($evaluation) && (isset($evaluation->id) || isset($evaluation['id']))) ? 'edit' : 'plus-circle' }} me-2"></i>
                            {{ (isset($evaluation) && (isset($evaluation->id) || isset($evaluation['id']))) ? 'Modifier l\'évaluation' : 'Créer une nouvelle évaluation' }}
                        </h4>
                        <div class="d-flex align-items-center">
                            <button type="button" class="btn btn-outline-secondary me-2" id="preview-btn">
                                <i class="fas fa-eye me-2"></i>Prévisualiser
                            </button>
                            <button type="submit" class="btn btn-success btn-lg px-4" id="submit-btn" form="evaluation-form">
                                <i class="fas fa-save me-2"></i>
                                {{ (isset($evaluation) && (isset($evaluation->id) || isset($evaluation['id']))) ? 'Mettre à jour' : 'Enregistrer' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form id="evaluation-form">
                        @csrf

                        <!-- SECTION UNIQUE : GESTION DES PARTIES -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Gestion des parties</h5>
                                <button type="button" class="btn btn-sm btn-light" id="add-part-btn">
                                    <i class="fas fa-plus me-1"></i>Ajouter une partie
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="parts-container">
                                    <!-- Les parties seront ajoutées ici dynamiquement -->
                                </div>

                                <div class="text-center mt-3" id="no-parts-message" style="display: none;">
                                    <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucune partie définie. Cliquez sur "Ajouter une partie" pour commencer.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions du formulaire -->
                        <div class="form-actions mt-5 pt-4 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <button type="button" class="btn btn-outline-info" id="validate-btn">
                                        <i class="fas fa-check me-2"></i>Vérifier la cohérence
                                    </button>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg px-4" id="submit-btn-bottom">
                                        <i class="fas fa-save me-2"></i>
                                        {{ (isset($evaluation) && (isset($evaluation->id) || isset($evaluation['id']))) ? 'Mettre à jour l\'évaluation' : 'Enregistrer l\'évaluation' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Messages d'alerte -->
                    <div id="error-messages" class="alert alert-danger mt-3" style="display: none;"></div>
                    <div id="success-message" class="alert alert-success mt-3" style="display: none;"></div>
                    <div id="validation-results" class="alert alert-info mt-3" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de prévisualisation -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Prévisualisation de l'évaluation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="preview-content"></div>
            </div>
        </div>
    </div>
</div>

<!-- Template pour une partie avec contexte intégré -->
<template id="part-template">
    <div class="part-card card border-primary mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="fas fa-grip-vertical text-muted me-2 part-handle" style="cursor: move;"></i>
                <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none part-toggle-btn">
                    <i class="fas fa-chevron-down me-2 part-toggle-icon"></i>
                    <h6 class="mb-0 part-title-display">Nouvelle Partie</h6>
                </button>
                <span class="badge bg-primary ms-2 part-questions-count">0 questions</span>
                <span class="badge bg-warning ms-2 part-context-indicator" style="display: none;">
                    <i class="fas fa-file-alt me-1"></i>Avec contexte
                </span>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary add-question-btn me-1">
                    <i class="fas fa-plus me-1"></i>Question
                </button>
                <button type="button" class="btn btn-sm btn-outline-info ai-suggest-btn me-1" title="Suggérer des questions avec l'IA">
                    <i class="fas fa-robot me-1"></i>IA
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning toggle-context-btn me-1">
                    <i class="fas fa-file-alt me-1"></i>Contexte
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger delete-part-btn">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="collapse show part-collapse">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="form-label">Identifiant</label>
                            <select class="form-select part-identifier">
                                <option value="I">I</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                                <option value="IV">IV</option>
                                <option value="V">V</option>
                                <option value="VI">VI</option>
                                <option value="VII">VII</option>
                                <option value="VIII">VIII</option>
                                <option value="IX">IX</option>
                                <option value="X">X</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group mb-3">
                            <label class="form-label">Titre de la partie</label>
                            <input type="text" class="form-control part-title" placeholder="Ex: QCM, Étude de cas, Exercices...">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Description (optionnel)</label>
                    <textarea class="form-control part-description" rows="2" placeholder="Description de cette partie..."></textarea>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Type de questions par défaut</label>
                    <select class="form-select part-question-type">
                        <option value="text">Cours (texte court)</option>
                        <option value="textarea">Étude de cas (texte long)</option>
                        <option value="choice_single">Question à choix unique</option>
                        <option value="choice_multiple">Question à choix multiples</option>
                    </select>
                </div>

                <!-- SECTION CONTEXTE D'ÉTUDE DE CAS (cachée par défaut) -->
                <div class="part-context-section" style="display: none;">
                    <div class="card border-warning mb-3">
                        <div class="card-header bg-warning bg-opacity-25 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Contexte d'étude de cas</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-context-btn">
                                <i class="fas fa-trash me-1"></i>Supprimer le contexte
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="form-label">Problématique</label>
                                <textarea class="form-control part-context-problematic" rows="3" placeholder="Ex: Comment cette IMF peut-elle réussir sa transition numérique..."></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Ressources</label>
                                <textarea class="form-control part-context-resources" rows="2" placeholder="Ex: Vous pouvez consulter les sites web des autorités..."></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label">Consignes de présentation</label>
                                <textarea class="form-control part-context-instructions" rows="2" placeholder="Ex: Votre étude de cas doit être claire, bien structurée..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des questions de cette partie -->
                <div class="questions-list mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Questions de cette partie</h6>
                        <div>
                            <span class="badge bg-secondary me-2 part-total-points-badge">0 pts</span>
                        </div>
                    </div>
                    <div class="questions-container">
                        <!-- Les questions seront ajoutées ici -->
                    </div>
                    <div class="text-center py-3 no-questions-message">
                        <i class="fas fa-question-circle fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Aucune question dans cette partie</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Template pour une question -->
<template id="question-template">
    <div class="question-card card border mb-3">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <div class="d-flex align-items-center">
                <i class="fas fa-grip-vertical text-muted me-2 question-handle" style="cursor: move;"></i>
                <button type="button" class="btn btn-sm btn-link text-dark p-0 text-decoration-none question-toggle-btn toggle-btn">
                    <i class="fas fa-chevron-down me-2 question-toggle-icon"></i>
                    <span class="badge bg-primary me-2 question-number">1</span>
                    <span class="question-title-display">Nouvelle Question</span>
                </button>
                <span class="badge question-type-badge bg-secondary ms-2">Texte court</span>
                <span class="badge bg-success ms-2 question-points-badge">0 pts</span>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-outline-info ai-refine-question-btn me-1" title="Optimiser avec l'IA">
                    <i class="fas fa-magic"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger delete-question-btn">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="collapse show question-collapse">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="form-label">Titre de la question</label>
                            <input type="text" class="form-control question-title" placeholder="Ex: Question 1, Exercice 1...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Points</label>
                            <input type="number" class="form-control question-points" min="0.5" step="0.5" value="1">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Énoncé</label>
                    <textarea class="form-control question-statement" rows="3" placeholder="Énoncé de la question..."></textarea>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Type de réponse</label>
                    <select class="form-select question-type">
                        <option value="text">Texte court</option>
                        <option value="textarea">Texte long (étude de cas)</option>
                        <option value="choice_single">Choix unique</option>
                        <option value="choice_multiple">Choix multiples</option>
                    </select>
                </div>

                <!-- Section des options (seulement pour les questions à choix) -->
                <div class="options-container" style="display: none;">
                    <div class="card border-info">
                        <div class="card-header bg-info bg-opacity-25 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Options de réponse</h6>
                            <button type="button" class="btn btn-sm btn-outline-info add-option-btn">
                                <i class="fas fa-plus me-1"></i>Ajouter une option
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="options-list">
                                <!-- Les options seront ajoutées ici -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Template pour une option de réponse -->
<template id="option-template">
    <div class="option-item d-flex align-items-center mb-2">
        <i class="fas fa-grip-vertical text-muted me-2 option-handle" style="cursor: move;"></i>
        <div class="form-check me-3">
            <input class="form-check-input option-correct" type="checkbox">
            <label class="form-check-label small">Correcte</label>
        </div>
        <input type="text" class="form-control option-label" placeholder="Texte de l'option...">
        <button type="button" class="btn btn-sm btn-outline-danger ms-2 delete-option-btn">
            <i class="fas fa-times"></i>
        </button>
    </div>
</template>
@endsection

@section('other-css')
<style>
    .stat-card {
        padding: 15px;
        border-radius: 8px;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        background: #e9ecef;
        transform: translateY(-2px);
    }
    
    .stat-value {
        font-size: 28px;
        font-weight: bold;
        color: #2c3e50;
    }
    
    .stat-label {
        font-size: 14px;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .sortable-ghost {
        opacity: 0.4;
    }
    
    .sortable-chosen {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .part-card, .question-card {
        transition: all 0.3s ease;
    }
    
    .toggle-btn i {
        transition: transform 0.3s ease;
    }
    
    .toggle-btn.collapsed i {
        transform: rotate(-90deg);
    }
    
    .part-context-section {
        animation: fadeIn 0.3s ease;
    }

    .toggle-context-btn.active {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }
    
    .question-card .collapse:not(.show) {
        display: none;
    }
    
    .part-card .collapse:not(.show) {
        display: none;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
</style>
@endsection



@section('other-css')
<style>
    .sortable-ghost {
        opacity: 0.4;
    }
    
    .sortable-chosen {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .stat-card {
        padding: 10px;
    }
    
    .stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #2c3e50;
    }
    
    .stat-label {
        font-size: 12px;
        color: #7f8c8d;
        text-transform: uppercase;
    }
    
    .part-card, .question-card {
        transition: all 0.3s ease;
    }
    
    .toggle-btn i {
        transition: transform 0.3s ease;
    }
    
    .toggle-btn.collapsed i {
        transform: rotate(-90deg);
    }
    
    .part-context-section {
        animation: fadeIn 0.3s ease;
    }

    .toggle-context-btn.active {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #212529;
    }
    
    .question-card .collapse:not(.show) {
        display: none;
    }
    
    .part-card .collapse:not(.show) {
        display: none;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>
@endsection

@section('other-js')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Structure de données simplifiée
    const evaluationData = @json($evaluation ?? null);
    
    document.addEventListener('DOMContentLoaded', function() {
        const MAX_POINTS = 20;
        let partsData = [];
        let nextPartId = 1;
        let nextQuestionId = 1;
        let nextOptionId = 1;
        let hasUnsavedChanges = false;

        // Marquer les changements pour l'alerte de départ
        function markChanges() {
            hasUnsavedChanges = true;
            saveToLocalStorage();
        }

        // Sauvegarde locale (Brouillon)
        function saveToLocalStorage() {
            const draftData = {
                parts: partsData,
                evaluation_id: evaluationData?.id || 'new',
                timestamp: new Date().getTime()
            };
            localStorage.setItem('evaluation_draft', JSON.stringify(draftData));
        }

        // Charger le brouillon
        function loadFromLocalStorage() {
            const saved = localStorage.getItem('evaluation_draft');
            if (saved) {
                const draft = JSON.parse(saved);
                // Vérifier si c'est pour la même évaluation et si c'est récent (moins de 24h)
                const isSameEval = draft.evaluation_id == (evaluationData?.id || 'new');
                const isRecent = (new Date().getTime() - draft.timestamp) < 86400000;

                if (isSameEval && isRecent && draft.parts.length > 0) {
                    Swal.fire({
                        title: 'Brouillon trouvé',
                        text: "Voulez-vous restaurer votre travail non enregistré ?",
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Oui, restaurer',
                        cancelButtonText: 'Non, ignorer'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            loadPartsData(draft.parts);
                        }
                    });
                }
            }
        }

        window.onbeforeunload = function() {
            if (hasUnsavedChanges) {
                return "Vous avez des modifications non enregistrées. Voulez-vous vraiment quitter ?";
            }
        };

        // ============ FONCTIONS UTILITAIRES ============

        // Fonction simple pour les accordéons
        function setupSimpleAccordion(toggleBtn, collapseElement) {
            if (toggleBtn && collapseElement) {
                // Initialiser l'icône
                const icon = toggleBtn.querySelector('i');
                if (icon && collapseElement.classList.contains('show')) {
                    icon.style.transform = 'rotate(0deg)';
                }
                
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Basculer la classe 'show'
                    if (collapseElement.classList.contains('show')) {
                        collapseElement.classList.remove('show');
                        if (icon) icon.style.transform = 'rotate(-90deg)';
                    } else {
                        collapseElement.classList.add('show');
                        if (icon) icon.style.transform = 'rotate(0deg)';
                    }
                });
            }
        }

        // Fonction pour initialiser le tri
        function initSortable(container, groupName) {
            if (!container) return;
            
            return new Sortable(container, {
                group: groupName,
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function(evt) {
                    // Mettre à jour l'ordre dans les données
                    const partId = container.closest('.part-card')?.dataset.partId;
                    const questionId = container.closest('.question-card')?.dataset.questionId;
                    
                    if (partId && !questionId) {
                        // Tri des questions dans une partie
                        const partIndex = partsData.findIndex(p => p.id == partId);
                        if (partIndex !== -1) {
                            const questionElements = container.querySelectorAll('.question-card');
                            questionElements.forEach((element, index) => {
                                const qId = element.dataset.questionId;
                                const question = partsData[partIndex].questions.find(q => q.id == qId);
                                if (question) {
                                    question.order_in_part = index;
                                }
                            });
                            
                            // Renumérotage
                            renumberQuestionsInPart(partId);
                        }
                    }
                }
            });
        }

        // ============ GESTION DES PARTIES AVEC CONTEXTE ============

        // Fonction pour créer une partie avec contexte optionnel
        function createPart(partData = {}) {
            const partId = partData.id || nextPartId++;
            const part = {
                id: partId,
                identifier: partData.identifier || 'I',
                title: partData.title || '',
                description: partData.description || '',
                question_type: partData.question_type || 'text',
                order: partData.order || partsData.length,
                has_case_study_context: !!partData.case_study_context,
                case_study_context: partData.case_study_context || null,
                questions: [] // IMPORTANT: Initialiser avec tableau vide
            };

            console.log('Création de partie avec ID:', partId, 'Données (sans questions pour l\'instant):', part);

            // Rendre le template
            const partTemplate = document.getElementById('part-template');
            const partClone = partTemplate.content.cloneNode(true);
            const partElement = partClone.querySelector('.part-card');
            partElement.dataset.partId = partId;

            // Configurer l'accordéon de la partie
            const partToggleBtn = partElement.querySelector('.part-toggle-btn');
            const partCollapse = partElement.querySelector('.part-collapse');
            setupSimpleAccordion(partToggleBtn, partCollapse);

            // Remplir les champs DE BASE (sans les questions pour l'instant)
            fillPartBasicFields(partElement, part);

            // Configurer le contexte SI IL EXISTE
            if (part.has_case_study_context && part.case_study_context) {
                // Afficher le contexte et remplir les champs
                showPartContext(partElement, part.case_study_context);
            }

            // Événements pour les boutons
            setupPartEventListeners(partElement, partId);

            // Ajouter au DOM
            const partsContainer = document.getElementById('parts-container');
            if (partsContainer) {
                partsContainer.appendChild(partElement);
            }

            // CORRECTION CRITIQUE : Ajouter la partie aux données AVANT d'ajouter les questions
            partsData.push(part);
            console.log('Partie ajoutée à partsData. partsData:', partsData);

            // Cacher le message "aucune partie"
            const noPartsMessage = document.getElementById('no-parts-message');
            if (noPartsMessage) {
                noPartsMessage.style.display = 'none';
            }

            // Initialiser le tri des questions
            const questionsContainer = partElement.querySelector('.questions-container');
            if (questionsContainer) {
                initSortable(questionsContainer, `part-${partId}`);
            }

            // MAINTENANT ajouter les questions (après que la partie est dans partsData)
            // MAIS SEULEMENT si partData.questions existe et n'a pas encore été ajoutée
            if (partData.questions && partData.questions.length > 0 && part.questions.length === 0) {
                console.log('Ajout des questions APRÈS que la partie est dans partsData');
                const noQuestionsMessage = partElement.querySelector('.no-questions-message');
                addQuestionsToPart(partId, questionsContainer, noQuestionsMessage, partData.questions);
            }

            // Mettre à jour l'aperçu
            updatePreview();

            return partId;
        }

        // Fonction pour remplir les champs de BASE d'une partie (sans les questions)
        function fillPartBasicFields(partElement, part) {
            const identifierSelect = partElement.querySelector('.part-identifier');
            const titleInput = partElement.querySelector('.part-title');
            const descriptionInput = partElement.querySelector('.part-description');
            const questionTypeSelect = partElement.querySelector('.part-question-type');
            const titleDisplay = partElement.querySelector('.part-title-display');

            if (identifierSelect) identifierSelect.value = part.identifier;
            if (titleInput) titleInput.value = part.title;
            if (descriptionInput) descriptionInput.value = part.description || '';
            if (questionTypeSelect) questionTypeSelect.value = part.question_type;
            if (titleDisplay) titleDisplay.textContent = part.title || 'Nouvelle Partie';
        }

        // Fonction pour ajouter les questions à une partie (après que la partie est dans partsData)
        function addQuestionsToPart(partId, container, noQuestionsMessage, questionsArray) {
            if (!container || !questionsArray || questionsArray.length === 0) {
                console.log('Aucune question à ajouter ou conteneur manquant');
                return;
            }

            console.log('Ajout de questions à la partie ID:', partId, 'Questions:', questionsArray);
            
            // Trier les questions par order_in_part avant de les ajouter
            const sortedQuestions = [...questionsArray].sort((a, b) => {
                return (a.order_in_part || 0) - (b.order_in_part || 0);
            });
            
            console.log('Questions triées pour partie ID', partId, ':', sortedQuestions);
            
            sortedQuestions.forEach((questionData, index) => {
                // Corriger order_in_part si tous sont 0
                if (questionData.order_in_part === 0 && index > 0) {
                    questionData.order_in_part = index;
                }
                console.log('Ajout question à partie ID', partId, ':', questionData);
                addQuestionToPart(partId, container, noQuestionsMessage, questionData);
            });
            
            if (noQuestionsMessage) {
                noQuestionsMessage.style.display = 'none';
            }
        }

        // Fonction pour AFFICHER et remplir le contexte
        function showPartContext(partElement, contextData) {
            const contextSection = partElement.querySelector('.part-context-section');
            const toggleBtn = partElement.querySelector('.toggle-context-btn');
            const contextIndicator = partElement.querySelector('.part-context-indicator');
            const problematicInput = partElement.querySelector('.part-context-problematic');
            const resourcesInput = partElement.querySelector('.part-context-resources');
            const instructionsInput = partElement.querySelector('.part-context-instructions');
            
            if (contextSection && toggleBtn && contextIndicator) {
                // Afficher la section
                contextSection.style.display = 'block';
                toggleBtn.classList.add('active');
                contextIndicator.style.display = 'inline-flex';
                
                // Remplir les champs avec les données existantes
                if (problematicInput) problematicInput.value = contextData.problematic || '';
                if (resourcesInput) resourcesInput.value = contextData.resources || '';
                if (instructionsInput) instructionsInput.value = contextData.instructions || '';
            }
        }

        // Fonction pour configurer les événements d'une partie
        function setupPartEventListeners(partElement, partId) {
            // Bouton pour ajouter une question
            const addQuestionBtn = partElement.querySelector('.add-question-btn');
            if (addQuestionBtn) {
                addQuestionBtn.addEventListener('click', function() {
                    const container = partElement.querySelector('.questions-container');
                    const noMsg = partElement.querySelector('.no-questions-message');
                    addQuestionToPart(partId, container, noMsg);
                });
            }

            // Bouton IA pour suggérer des questions
            const aiSuggestBtn = partElement.querySelector('.ai-suggest-btn');
            if (aiSuggestBtn) {
                aiSuggestBtn.addEventListener('click', function() {
                    suggestAIQuestions(partId);
                });
            }

            // Bouton pour basculer le contexte
            const toggleContextBtn = partElement.querySelector('.toggle-context-btn');
            if (toggleContextBtn) {
                toggleContextBtn.addEventListener('click', function() {
                    togglePartContext(partElement, partId);
                });
            }

            // Bouton pour supprimer la partie
            const deleteBtn = partElement.querySelector('.delete-part-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    deletePart(partId);
                });
            }

            // Bouton pour supprimer le contexte
            const removeContextBtn = partElement.querySelector('.remove-context-btn');
            if (removeContextBtn) {
                removeContextBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    removePartContext(partElement, partId);
                });
            }

            // Écouteurs pour les champs de la partie
            const partFields = partElement.querySelectorAll('.part-identifier, .part-title, .part-description, .part-question-type');
            partFields.forEach(field => {
                field.addEventListener('change', function() {
                    updatePartData(partId);
                    if (field.classList.contains('part-title')) {
                        const display = partElement.querySelector('.part-title-display');
                        if (display) display.textContent = field.value || 'Nouvelle Partie';
                    }
                });
            });

            // Écouteurs pour les champs du contexte
            const contextFields = partElement.querySelectorAll('.part-context-problematic, .part-context-resources, .part-context-instructions');
            contextFields.forEach(field => {
                field.addEventListener('input', function() {
                    updatePartContextData(partId);
                });
            });
        }

        // Fonction pour basculer l'affichage du contexte
        function togglePartContext(partElement, partId) {
            const contextSection = partElement.querySelector('.part-context-section');
            const toggleBtn = partElement.querySelector('.toggle-context-btn');
            const contextIndicator = partElement.querySelector('.part-context-indicator');
            
            if (contextSection.style.display === 'none' || !contextSection.style.display) {
                // Afficher le contexte
                contextSection.style.display = 'block';
                toggleBtn.classList.add('active');
                if (contextIndicator) contextIndicator.style.display = 'inline-flex';
                
                // Mettre à jour les données
                const partIndex = partsData.findIndex(p => p.id == partId);
                if (partIndex !== -1) {
                    if (!partsData[partIndex].case_study_context) {
                        partsData[partIndex].case_study_context = {
                            problematic: '',
                            resources: '',
                            instructions: ''
                        };
                    }
                    partsData[partIndex].has_case_study_context = true;
                }
            } else {
                // Cacher le contexte
                contextSection.style.display = 'none';
                toggleBtn.classList.remove('active');
                if (contextIndicator) contextIndicator.style.display = 'none';
                
                // Mettre à jour les données
                const partIndex = partsData.findIndex(p => p.id == partId);
                if (partIndex !== -1) {
                    partsData[partIndex].has_case_study_context = false;
                }
            }
        }

        // Fonction pour supprimer le contexte d'une partie
        function removePartContext(partElement, partId) {
            Swal.fire({
                title: 'Supprimer ce contexte ?',
                text: "Le contexte sera définitivement supprimé.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    const contextSection = partElement.querySelector('.part-context-section');
                    const toggleBtn = partElement.querySelector('.toggle-context-btn');
                    const contextIndicator = partElement.querySelector('.part-context-indicator');
                    
                    // Cacher la section
                    if (contextSection) contextSection.style.display = 'none';
                    if (toggleBtn) toggleBtn.classList.remove('active');
                    if (contextIndicator) contextIndicator.style.display = 'none';
                    
                    // Réinitialiser les champs
                    const problematicInput = partElement.querySelector('.part-context-problematic');
                    const resourcesInput = partElement.querySelector('.part-context-resources');
                    const instructionsInput = partElement.querySelector('.part-context-instructions');
                    
                    if (problematicInput) problematicInput.value = '';
                    if (resourcesInput) resourcesInput.value = '';
                    if (instructionsInput) instructionsInput.value = '';
                    
                    // Mettre à jour les données
                    const partIndex = partsData.findIndex(p => p.id == partId);
                    if (partIndex !== -1) {
                        partsData[partIndex].has_case_study_context = false;
                        partsData[partIndex].case_study_context = null;
                    }
                }
            });
        }

        // ============ GESTION DES QUESTIONS ============

        // Fonction pour ajouter une question à une partie
        function addQuestionToPart(partId, container, noQuestionsMessage, questionData = {}) {
            const partIndex = partsData.findIndex(p => p.id == partId);
            if (partIndex === -1) {
                console.error('Partie non trouvée dans partsData. partId:', partId, 'partsData:', partsData);
                return null;
            }

            const questionId = questionData.id || nextQuestionId++;
            const question = {
                id: questionId,
                title: questionData.title || '',
                statement: questionData.statement || '',
                type: questionData.type || 'text',
                points: questionData.points || 1,
                order_in_part: questionData.order_in_part || partsData[partIndex].questions.length,
                options_text: questionData.options_text || []
            };

            console.log('Création de question ID:', questionId, 'pour partie ID:', partId, 'Données:', question);

            // Rendre le template
            const questionTemplate = document.getElementById('question-template');
            if (!questionTemplate) {
                console.error('Template de question non trouvé!');
                return null;
            }
            
            const questionClone = questionTemplate.content.cloneNode(true);
            const questionElement = questionClone.querySelector('.question-card');
            if (!questionElement) {
                console.error('Élément question-card non trouvé dans le template!');
                return null;
            }
            
            questionElement.dataset.questionId = questionId;
            questionElement.dataset.partId = partId;

            // Configurer l'accordéon de la question
            const questionToggleBtn = questionElement.querySelector('.question-toggle-btn');
            const questionCollapse = questionElement.querySelector('.question-collapse');
            setupSimpleAccordion(questionToggleBtn, questionCollapse);

            // Remplir les champs
            const titleInput = questionElement.querySelector('.question-title');
            const statementInput = questionElement.querySelector('.question-statement');
            const typeSelect = questionElement.querySelector('.question-type');
            const pointsInput = questionElement.querySelector('.question-points');
            const titleDisplay = questionElement.querySelector('.question-title-display');
            const pointsBadge = questionElement.querySelector('.question-points-badge');
            const typeBadge = questionElement.querySelector('.question-type-badge');
            const questionNumber = questionElement.querySelector('.question-number');

            if (titleInput) titleInput.value = question.title;
            if (statementInput) statementInput.value = question.statement;
            if (typeSelect) typeSelect.value = question.type;
            if (pointsInput) pointsInput.value = question.points;
            if (titleDisplay) titleDisplay.textContent = question.title || 'Nouvelle Question';
            if (pointsBadge) pointsBadge.textContent = `${question.points} pts`;
            
            // CORRECTION : Utiliser l'ordre réel de la question
            if (questionNumber) {
                questionNumber.textContent = (question.order_in_part + 1) || 1;
            }
            
            // Mettre à jour le badge de type
            if (typeBadge) {
                const typeLabels = {
                    'text': { text: 'Texte court', class: 'bg-secondary' },
                    'textarea': { text: 'Texte long', class: 'bg-warning text-dark' },
                    'choice_single': { text: 'Choix unique', class: 'bg-success' },
                    'choice_multiple': { text: 'Choix multiples', class: 'bg-info' }
                };
                const typeInfo = typeLabels[question.type] || typeLabels.text;
                typeBadge.textContent = typeInfo.text;
                typeBadge.className = `badge question-type-badge ${typeInfo.class}`;
            }

            // Gérer les options si c'est une question à choix
            const optionsContainer = questionElement.querySelector('.options-container');
            const optionsList = questionElement.querySelector('.options-list');
            const addOptionBtn = questionElement.querySelector('.add-option-btn');
            
            if (question.type === 'choice_single' || question.type === 'choice_multiple') {
                if (optionsContainer) optionsContainer.style.display = 'block';
                
                // Ajouter les options existantes
                if (optionsList && question.options_text && question.options_text.length > 0) {
                    question.options_text.forEach((optionData, index) => {
                        addOptionToQuestion(questionId, optionsList, optionData);
                    });
                } else if (optionsList && addOptionBtn) {
                    // Ajouter 3 options par défaut si aucune n'existe
                    for (let i = 0; i < 3; i++) {
                        addOptionToQuestion(questionId, optionsList);
                    }
                }
                
                // Configurer le bouton pour ajouter des options
                if (addOptionBtn) {
                    addOptionBtn.addEventListener('click', function() {
                        addOptionToQuestion(questionId, optionsList);
                    });
                }
            }

            // Événement pour changer le type de question
            if (typeSelect) {
                typeSelect.addEventListener('change', function() {
                    const newType = this.value;
                    
                    // Mettre à jour le badge
                    if (typeBadge) {
                        const typeLabels = {
                            'text': { text: 'Texte court', class: 'bg-secondary' },
                            'textarea': { text: 'Texte long', class: 'bg-warning text-dark' },
                            'choice_single': { text: 'Choix unique', class: 'bg-success' },
                            'choice_multiple': { text: 'Choix multiples', class: 'bg-info' }
                        };
                        const typeInfo = typeLabels[newType] || typeLabels.text;
                        typeBadge.textContent = typeInfo.text;
                        typeBadge.className = `badge question-type-badge ${typeInfo.class}`;
                    }
                    
                    // Afficher/masquer les options
                    if (optionsContainer) {
                        if (newType === 'choice_single' || newType === 'choice_multiple') {
                            optionsContainer.style.display = 'block';
                            if (optionsList && optionsList.children.length === 0) {
                                for (let i = 0; i < 3; i++) {
                                    addOptionToQuestion(questionId, optionsList);
                                }
                            }
                        } else {
                            optionsContainer.style.display = 'none';
                        }
                    }
                    
                    updateQuestionData(partId, questionId);
                });
            }

            // Événements pour les autres champs
            [titleInput, statementInput, pointsInput].forEach(field => {
                if (field) {
                    field.addEventListener('input', function() {
                        updateQuestionData(partId, questionId);
                        if (field === titleInput && titleDisplay) {
                            titleDisplay.textContent = field.value || 'Nouvelle Question';
                        }
                        if (field === pointsInput && pointsBadge) {
                            pointsBadge.textContent = `${field.value} pts`;
                        }
                    });
                }
            });

            // Événement pour supprimer la question
            const deleteBtn = questionElement.querySelector('.delete-question-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    deleteQuestion(partId, questionId);
                });
            }

            // Événement pour raffiner la question avec l'IA
            const aiRefineBtn = questionElement.querySelector('.ai-refine-question-btn');
            if (aiRefineBtn) {
                aiRefineBtn.addEventListener('click', function() {
                    refineAIQuestion(partId, questionId);
                });
            }

            // Ajouter au DOM
            if (container) {
                console.log('Ajout de la question ID', questionId, 'au conteneur');
                container.appendChild(questionElement);
                console.log('Question ajoutée avec succès');
            } else {
                console.error('Conteneur de questions non trouvé pour partie ID', partId);
            }
            
            if (noQuestionsMessage) {
                noQuestionsMessage.style.display = 'none';
            }

            // Ajouter aux données
            if (!partsData[partIndex].questions) {
                partsData[partIndex].questions = [];
            }
            partsData[partIndex].questions.push(question);

            // Renumérotage
            renumberQuestionsInPart(partId);
            
            // Mettre à jour le compteur de questions
            updatePartQuestionCount(partId);
            
            // Mettre à jour l'aperçu
            updatePreview();

            return questionId;
        }

        // Fonction pour ajouter une option à une question
        function addOptionToQuestion(questionId, container, optionData = {}) {
            const optionId = nextOptionId++;
            const optionTemplate = document.getElementById('option-template');
            const optionClone = optionTemplate.content.cloneNode(true);
            const optionElement = optionClone.querySelector('.option-item');
            optionElement.dataset.optionId = optionId;

            const optionLabel = optionElement.querySelector('.option-label');
            const optionCorrect = optionElement.querySelector('.option-correct');
            
            if (optionLabel) optionLabel.value = optionData.label || '';
            if (optionCorrect && optionData.correct !== undefined) {
                optionCorrect.checked = optionData.correct;
            }

            // Événement pour supprimer l'option
            const deleteBtn = optionElement.querySelector('.delete-option-btn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    optionElement.remove();
                });
            }

            // Événement pour mettre à jour
            if (optionLabel) {
                optionLabel.addEventListener('input', function() {
                    // Mettre à jour les données si nécessaire
                });
            }

            if (container) container.appendChild(optionElement);
            return optionId;
        }

        // ============ FONCTIONS DE MISE À JOUR ============

        // Fonction pour mettre à jour les données d'une partie
        function updatePartData(partId) {
            const partElement = document.querySelector(`[data-part-id="${partId}"]`);
            if (!partElement) return;

            const partIndex = partsData.findIndex(p => p.id == partId);
            if (partIndex === -1) return;

            const identifierSelect = partElement.querySelector('.part-identifier');
            const titleInput = partElement.querySelector('.part-title');
            const descriptionInput = partElement.querySelector('.part-description');
            const questionTypeSelect = partElement.querySelector('.part-question-type');

            if (identifierSelect) partsData[partIndex].identifier = identifierSelect.value;
            if (titleInput) partsData[partIndex].title = titleInput.value;
            if (descriptionInput) partsData[partIndex].description = descriptionInput.value;
            if (questionTypeSelect) partsData[partIndex].question_type = questionTypeSelect.value;

            updatePreview();
        }

        // Fonction pour mettre à jour les données du contexte
        function updatePartContextData(partId) {
            const partElement = document.querySelector(`[data-part-id="${partId}"]`);
            if (!partElement) return;

            const partIndex = partsData.findIndex(p => p.id == partId);
            if (partIndex === -1) return;

            const problematicInput = partElement.querySelector('.part-context-problematic');
            const resourcesInput = partElement.querySelector('.part-context-resources');
            const instructionsInput = partElement.querySelector('.part-context-instructions');
            
            if (!partsData[partIndex].case_study_context) {
                partsData[partIndex].case_study_context = {};
            }
            
            if (problematicInput) partsData[partIndex].case_study_context.problematic = problematicInput.value;
            if (resourcesInput) partsData[partIndex].case_study_context.resources = resourcesInput.value;
            if (instructionsInput) partsData[partIndex].case_study_context.instructions = instructionsInput.value;
            
            partsData[partIndex].has_case_study_context = true;
        }

        // Fonction pour mettre à jour les données d'une question
        function updateQuestionData(partId, questionId) {
            const questionElement = document.querySelector(`[data-question-id="${questionId}"]`);
            if (!questionElement) return;

            const partIndex = partsData.findIndex(p => p.id == partId);
            if (partIndex === -1) return;

            const questionIndex = partsData[partIndex].questions.findIndex(q => q.id == questionId);
            if (questionIndex === -1) return;

            const titleInput = questionElement.querySelector('.question-title');
            const statementInput = questionElement.querySelector('.question-statement');
            const typeSelect = questionElement.querySelector('.question-type');
            const pointsInput = questionElement.querySelector('.question-points');

            if (titleInput) partsData[partIndex].questions[questionIndex].title = titleInput.value;
            if (statementInput) partsData[partIndex].questions[questionIndex].statement = statementInput.value;
            if (typeSelect) partsData[partIndex].questions[questionIndex].type = typeSelect.value;
            if (pointsInput) partsData[partIndex].questions[questionIndex].points = parseFloat(pointsInput.value) || 0;

            // Mettre à jour les options
            const optionsList = questionElement.querySelector('.options-list');
            if (optionsList) {
                const optionElements = optionsList.querySelectorAll('.option-item');
                partsData[partIndex].questions[questionIndex].options_text = Array.from(optionElements).map(opt => ({
                    label: opt.querySelector('.option-label')?.value || '',
                    correct: opt.querySelector('.option-correct')?.checked || false
                }));
            }

            updatePreview();
        }

        // Fonction pour renumeroter les questions
        function renumberQuestionsInPart(partId) {
            const partElement = document.querySelector(`[data-part-id="${partId}"]`);
            if (!partElement) return;

            const questionElements = partElement.querySelectorAll('.question-card');
            questionElements.forEach((element, index) => {
                const numberElement = element.querySelector('.question-number');
                if (numberElement) {
                    numberElement.textContent = index + 1;
                }
            });
        }

        // Fonction pour mettre à jour le compteur de questions
        function updatePartQuestionCount(partId) {
            const partElement = document.querySelector(`[data-part-id="${partId}"]`);
            if (!partElement) return;

            const partIndex = partsData.findIndex(p => p.id == partId);
            if (partIndex === -1) return;

            const countElement = partElement.querySelector('.part-questions-count');
            if (countElement) {
                countElement.textContent = `${partsData[partIndex].questions.length} question${partsData[partIndex].questions.length !== 1 ? 's' : ''}`;
            }
        }

        // ============ SUPPRESSION ============

        // Fonction pour supprimer une question
        function deleteQuestion(partId, questionId) {
            Swal.fire({
                title: 'Supprimer cette question ?',
                text: "Cette action est irréversible !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    const questionElement = document.querySelector(`[data-question-id="${questionId}"]`);
                    if (questionElement) {
                        questionElement.style.opacity = '0';
                        questionElement.style.transform = 'translateX(100px)';
                        
                        setTimeout(() => {
                            questionElement.remove();
                            
                            // Supprimer des données
                            const partIndex = partsData.findIndex(p => p.id == partId);
                            if (partIndex !== -1) {
                                partsData[partIndex].questions = partsData[partIndex].questions.filter(q => q.id != questionId);
                                renumberQuestionsInPart(partId);
                                updatePartQuestionCount(partId);
                            }
                            
                            updatePreview();
                        }, 300);
                    }
                }
            });
        }

        // Fonction pour supprimer une partie
        function deletePart(partId) {
            Swal.fire({
                title: 'Supprimer cette partie ?',
                text: "Toutes les questions de cette partie seront également supprimées !",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            }).then((result) => {
                if (result.isConfirmed) {
                    const partElement = document.querySelector(`[data-part-id="${partId}"]`);
                    if (partElement) {
                        partElement.style.opacity = '0';
                        partElement.style.transform = 'translateX(100px)';
                        
                        setTimeout(() => {
                            partElement.remove();
                            partsData = partsData.filter(p => p.id != partId);
                            updatePreview();
                            
                            if (partsData.length === 0) {
                                document.getElementById('no-parts-message').style.display = 'block';
                            }
                        }, 300);
                    }
                }
            });
        }

        // ============ APERÇU ET VALIDATION ============

        // Fonction pour générer la prévisualisation
        function generatePreview() {
            let previewHTML = '<div class="preview-evaluation">';
            
            if (partsData.length === 0) {
                previewHTML += '<div class="alert alert-warning">Aucune partie définie</div>';
                return previewHTML;
            }
            
            partsData.forEach((part, partIndex) => {
                previewHTML += `
                    <div class="preview-part mb-4">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h4 class="mb-0">${part.identifier}. ${part.title || 'Sans titre'}</h4>
                                ${part.description ? `<p class="mb-0 mt-2">${part.description}</p>` : ''}
                            </div>
                            <div class="card-body">
                `;
                
                // Afficher le contexte d'étude de cas s'il existe
                if (part.has_case_study_context && part.case_study_context) {
                    previewHTML += `
                        <div class="card border-warning mb-4">
                            <div class="card-header bg-warning bg-opacity-25">
                                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Contexte d'étude de cas</h5>
                            </div>
                            <div class="card-body">
                    `;
                    
                    if (part.case_study_context.problematic) {
                        previewHTML += `
                            <div class="mb-3">
                                <h6>Problématique :</h6>
                                <p>${part.case_study_context.problematic}</p>
                            </div>
                        `;
                    }
                    
                    if (part.case_study_context.resources) {
                        previewHTML += `
                            <div class="mb-3">
                                <h6>Ressources :</h6>
                                <p>${part.case_study_context.resources}</p>
                            </div>
                        `;
                    }
                    
                    if (part.case_study_context.instructions) {
                        previewHTML += `
                            <div class="mb-3">
                                <h6>Consignes de présentation :</h6>
                                <p>${part.case_study_context.instructions}</p>
                            </div>
                        `;
                    }
                    
                    previewHTML += `
                            </div>
                        </div>
                    `;
                }
                
                // Afficher les questions
                if (part.questions && part.questions.length > 0) {
                    part.questions.forEach((question, qIndex) => {
                        previewHTML += `
                            <div class="preview-question mb-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Question ${qIndex + 1}</h5>
                                            <span class="badge bg-primary">${question.points} point${question.points !== 1 ? 's' : ''}</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                        `;
                        
                        if (question.title) {
                            previewHTML += `<h6 class="mb-2">${question.title}</h6>`;
                        }
                        
                        if (question.statement) {
                            previewHTML += `<p class="mb-3">${question.statement}</p>`;
                        }
                        
                        // Afficher le type de réponse
                        const typeLabels = {
                            'text': 'Réponse courte',
                            'textarea': 'Réponse longue',
                            'choice_single': 'Choix unique',
                            'choice_multiple': 'Choix multiples'
                        };
                        
                        previewHTML += `
                            <div class="mb-2">
                                <small class="text-muted">Type de réponse : ${typeLabels[question.type] || 'Non spécifié'}</small>
                            </div>
                        `;
                        
                        // Afficher les options pour les questions à choix
                        if ((question.type === 'choice_single' || question.type === 'choice_multiple') && question.options_text && question.options_text.length > 0) {
                            previewHTML += '<div class="mt-3"><h6>Options :</h6><ul class="list-unstyled">';
                            question.options_text.forEach((option, optIndex) => {
                                previewHTML += `<li>${String.fromCharCode(65 + optIndex)}. ${option.label || 'Option non définie'}</li>`;
                            });
                            previewHTML += '</ul></div>';
                        }
                        
                        previewHTML += `
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    previewHTML += '<div class="alert alert-info">Aucune question dans cette partie</div>';
                }
                
                previewHTML += `
                            </div>
                        </div>
                    </div>
                `;
            });
            
            // Résumé
            const totalPoints = partsData.reduce((total, part) => {
                return total + (part.questions || []).reduce((partTotal, question) => {
                    return partTotal + (parseFloat(question.points) || 0);
                }, 0);
            }, 0);
            
            const totalQuestions = partsData.reduce((total, part) => total + (part.questions || []).length, 0);
            
            previewHTML += `
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Résumé</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="display-4">${partsData.length}</div>
                                <div>Parties</div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="display-4">${totalQuestions}</div>
                                <div>Questions</div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="display-4">${totalPoints.toFixed(1)}</div>
                                <div>Points total</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            previewHTML += '</div>';
            return previewHTML;
        }

        // Fonction pour mettre à jour l'aperçu
        function updatePreview() {
            const totalPoints = partsData.reduce((total, part) => {
                return total + (part.questions || []).reduce((partTotal, question) => {
                    return partTotal + (parseFloat(question.points) || 0);
                }, 0);
            }, 0);

            const totalQuestions = partsData.reduce((total, part) => total + (part.questions || []).length, 0);
            const exceedsLimit = totalPoints > MAX_POINTS;

            // Mettre à jour les compteurs
            const partsCountElem = document.getElementById('parts-count');
            const questionsCountElem = document.getElementById('questions-count');
            const totalPointsElem = document.getElementById('total-points');
            const livePartsCountElem = document.getElementById('live-parts-count');
            const totalPointsBadgeElem = document.getElementById('total-points-badge');
            const progressBar = document.getElementById('points-progress');
            const pointsLimitElem = document.getElementById('points-limit');
            const pointsWarningElem = document.getElementById('points-warning');
            const submitBtn = document.getElementById('submit-btn');

            if (partsCountElem) partsCountElem.textContent = partsData.length;
            if (questionsCountElem) questionsCountElem.textContent = totalQuestions;
            if (totalPointsElem) totalPointsElem.textContent = totalPoints.toFixed(1);
            if (livePartsCountElem) livePartsCountElem.textContent = `${partsData.length} partie${partsData.length !== 1 ? 's' : ''}`;
            if (totalPointsBadgeElem) totalPointsBadgeElem.textContent = `${totalPoints.toFixed(1)}/20 pts`;

            // Barre de progression
            if (progressBar) {
                const progressPercentage = Math.min((totalPoints / MAX_POINTS) * 100, 100);
                progressBar.style.width = `${progressPercentage}%`;

                if (exceedsLimit) {
                    progressBar.className = 'progress-bar bg-danger';
                    if (pointsLimitElem) pointsLimitElem.className = 'text-danger';
                    if (pointsWarningElem) pointsWarningElem.style.display = 'block';
                    if (submitBtn) submitBtn.disabled = true;
                } else {
                    progressBar.className = 'progress-bar bg-success';
                    if (pointsLimitElem) pointsLimitElem.className = 'text-success';
                    if (pointsWarningElem) pointsWarningElem.style.display = 'none';
                    if (submitBtn) submitBtn.disabled = false;
                }
            }
            
            if (pointsLimitElem) {
                pointsLimitElem.textContent = `${totalPoints.toFixed(1)}/20 pts`;
            }
        }

        // ============ INITIALISATION DES DONNÉES ============

        function initializeExistingParts() {
            console.log('Données complètes chargées:', evaluationData);
            
            if (evaluationData && evaluationData.parts && evaluationData.parts.length > 0) {
                console.log('Chargement des données depuis evaluationData.parts');
                loadPartsData(evaluationData.parts);
            } else if (evaluationData && evaluationData.evalutions && evaluationData.evalutions.parts) {
                console.log('Chargement des données depuis evaluationData.evalutions.parts');
                loadPartsData(evaluationData.evalutions.parts);
            } else {
                console.log('Aucune donnée à charger');
                document.getElementById('no-parts-message').style.display = 'block';
            }
        }

        function loadPartsData(partsArray) {
            // Réinitialiser les données
            partsData = [];
            nextPartId = 1;
            nextQuestionId = 1;
            nextOptionId = 1;
            
            // Vider le conteneur
            const partsContainer = document.getElementById('parts-container');
            if (partsContainer) partsContainer.innerHTML = '';
            
            // Charger chaque partie
            partsArray.forEach((part, index) => {
                console.log(`Partie ${index} chargée:`, part);
                
                // CORRECTION : NE PAS ajouter les questions ici
                // Les questions seront ajoutées dans createPart après que la partie est dans partsData
                const partData = {
                    id: nextPartId++,
                    identifier: part.identifier || 'I',
                    title: part.title || '',
                    description: part.description || '',
                    question_type: part.question_type || 'text',
                    order: index,
                    case_study_context: part.case_study_context || null,
                    has_case_study_context: !!part.case_study_context,
                    questions: part.questions || [] // Garder les questions ici pour qu'elles soient ajoutées dans createPart
                };

                const createdPartId = createPart(partData);
                console.log('Partie créée avec ID:', createdPartId);
            });
            
            updatePreview();
            document.getElementById('no-parts-message').style.display = 'none';
            
            console.log('PartsData après chargement:', partsData);
        }

        // ============ ÉVÉNEMENTS ============

        document.getElementById('add-part-btn').addEventListener('click', function() {
            createPart();
            updatePreview();
            document.getElementById('no-parts-message').style.display = 'none';
        });

        // Bouton de prévisualisation
        document.getElementById('preview-btn').addEventListener('click', function() {
            const previewContent = document.getElementById('preview-content');
            if (previewContent) {
                previewContent.innerHTML = generatePreview();
            }
            
            const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
            previewModal.show();
        });

        // ============ SOUMISSION ============

        document.getElementById('evaluation-form').addEventListener('submit', function(event) {
            event.preventDefault();
            
            if (!validateForm()) {
                Swal.fire('Erreur', 'Veuillez corriger les erreurs avant de soumettre.', 'error');
                return;
            }

            // Préparer les données
            const formData = {
                parts: partsData.map((part, index) => ({
                    id: part.id,
                    identifier: part.identifier,
                    title: part.title,
                    description: part.description,
                    question_type: part.question_type,
                    order: index,
                    has_case_study_context: part.has_case_study_context,
                    case_study_context: part.has_case_study_context ? part.case_study_context : null,
                    questions: part.questions.map((question, qIndex) => ({
                        id: question.id,
                        title: question.title,
                        statement: question.statement,
                        type: question.type,
                        points: question.points,
                        order_in_part: qIndex,
                        options_text: question.options_text
                    }))
                }))
            };

            console.log('Données envoyées:', formData);

            // Envoyer les données
            fetch("{{ route('enseignants.evaluation.store-evaluation-question', $emploiDuTemp->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify(formData),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    hasUnsavedChanges = false;
                    localStorage.removeItem('evaluation_draft');
                    Swal.fire({
...
                        title: 'Succès !',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    let errorMessages = '';
                    if (data.errors) {
                        for (let field in data.errors) {
                            errorMessages += `<p>${data.errors[field].join(', ')}</p>`;
                        }
                    }
                    const errorMessagesElem = document.getElementById('error-messages');
                    if (errorMessagesElem) {
                        errorMessagesElem.innerHTML = errorMessages;
                        errorMessagesElem.style.display = 'block';
                    }
                }
            })
            .catch(error => {
                Swal.fire('Erreur', 'Une erreur est survenue lors de l\'enregistrement.', 'error');
            });
        });

        // Fonction de validation
        function validateForm(showSuccess = false) {
            if (partsData.length === 0) {
                Swal.fire('Erreur', 'Veuillez ajouter au moins une partie.', 'error');
                return false;
            }

            let errors = [];
            let totalPoints = 0;

            partsData.forEach((part, pIdx) => {
                if (!part.title || part.title.trim() === '') {
                    errors.push(`La partie ${part.identifier} n'a pas de titre.`);
                }

                if (part.questions.length === 0) {
                    errors.push(`La partie ${part.identifier} n'a aucune question.`);
                }

                part.questions.forEach((q, qIdx) => {
                    totalPoints += parseFloat(q.points) || 0;
                    const qRef = `Partie ${part.identifier}, Question ${qIdx + 1}`;

                    if (!q.statement || q.statement.trim() === '') {
                        errors.push(`L'énoncé est vide pour : ${qRef}`);
                    }

                    if (parseFloat(q.points) <= 0) {
                        errors.push(`Le barème doit être supérieur à 0 pour : ${qRef}`);
                    }

                    if (q.type === 'choice_single' || q.type === 'choice_multiple') {
                        if (!q.options_text || q.options_text.length < 2) {
                            errors.push(`Au moins 2 options sont requises pour : ${qRef}`);
                        } else {
                            const hasCorrect = q.options_text.some(opt => opt.correct);
                            if (!hasCorrect) {
                                errors.push(`Veuillez cocher au moins une réponse correcte pour : ${qRef}`);
                            }
                            
                            if (q.type === 'choice_single') {
                                const correctCount = q.options_text.filter(opt => opt.correct).length;
                                if (correctCount > 1) {
                                    errors.push(`Une seule réponse correcte autorisée pour : ${qRef}`);
                                }
                            }

                            const emptyOptions = q.options_text.some(opt => !opt.label || opt.label.trim() === '');
                            if (emptyOptions) {
                                errors.push(`Certaines options de réponse sont vides pour : ${qRef}`);
                            }
                        }
                    }
                });
            });

            if (totalPoints > MAX_POINTS) {
                errors.push(`Le total des points (${totalPoints.toFixed(1)}) dépasse la limite de ${MAX_POINTS} points.`);
            }

            if (errors.length > 0) {
                Swal.fire({
                    title: 'Erreurs de validation',
                    html: `<div class="text-start"><ul class="small mb-0">${errors.map(e => `<li>${e}</li>`).join('')}</ul></div>`,
                    icon: 'error'
                });
                return false;
            }

            if (showSuccess) {
                Swal.fire('Validation', 'Le formulaire est parfaitement cohérent !', 'success');
            }
            return true;
        }

        // Fonction pour l'IA
        async function suggestAIQuestions(partId) {
            const partIndex = partsData.findIndex(p => p.id == partId);
            const part = partsData[partIndex];
            
            let topic = part.title;
            if (part.has_case_study_context && part.case_study_context?.problematic) {
                topic += " - Contexte: " + part.case_study_context.problematic;
            }

            Swal.fire({
                title: 'Génération IA en cours...',
                text: 'Gemini prépare des questions adaptées à votre contexte.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const response = await fetch("{{ route('enseignants.evaluation.ai-suggest-questions') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({ topic: topic, part_id: partId })
                });

                const data = await response.json();
                Swal.close();

                if (data.success && data.questions) {
                    const container = document.querySelector(`[data-part-id="${partId}"] .questions-container`);
                    const noMsg = document.querySelector(`[data-part-id="${partId}"] .no-questions-message`);
                    
                    data.questions.forEach(q => {
                        addQuestionToPart(partId, container, noMsg, {
                            title: q.title || "Question IA",
                            statement: q.content || q.statement,
                            type: q.type === 'qcm_unique' ? 'choice_single' : (q.type === 'qcm_multiple' ? 'choice_multiple' : 'text'),
                            points: q.points || 2,
                            options_text: q.options ? q.options.map(o => ({ label: o.text, correct: o.is_correct })) : []
                        });
                    });
                    
                    markChanges();
                    Swal.fire('Succès', `${data.questions.length} questions ont été ajoutées.`, 'success');
                } else {
                    Swal.fire('Erreur', data.message || 'L\'IA n\'a pas pu générer de questions.', 'error');
                }
            } catch (error) {
                Swal.close();
                Swal.fire('Erreur', 'Impossible de contacter le service IA.', 'error');
            }
        }
        
        // Fonction pour raffiner une question spécifique
        async function refineAIQuestion(partId, questionId) {
            const partIndex = partsData.findIndex(p => p.id == partId);
            const questionIndex = partsData[partIndex].questions.findIndex(q => q.id == questionId);
            const question = partsData[partIndex].questions[questionIndex];

            if (!question.statement || question.statement.trim() === '') {
                Swal.fire('Info', 'Veuillez d\'abord saisir un énoncé à améliorer.', 'info');
                return;
            }

            Swal.fire({
                title: 'Optimisation...',
                text: 'Gemini analyse et améliore votre question.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const response = await fetch("{{ route('enseignants.evaluation.ai-refine-questions') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({ 
                        content: question.statement,
                        type: question.type,
                        options: question.options_text
                    })
                });

                const data = await response.json();
                Swal.close();

                if (data.success && data.refined_content) {
                    Swal.fire({
                        title: 'Suggestion de l\'IA',
                        html: `<div class="text-start mb-3"><strong>Ancien :</strong><br><small>${question.statement}</small></div>
                               <div class="text-start p-2 bg-light border-start border-primary"><strong>Nouveau :</strong><br>${data.refined_content}</div>`,
                        showCancelButton: true,
                        confirmButtonText: 'Appliquer',
                        cancelButtonText: 'Garder l\'original'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const qElem = document.querySelector(`[data-question-id="${questionId}"]`);
                            const statementInput = qElem.querySelector('.question-statement');
                            if (statementInput) {
                                statementInput.value = data.refined_content;
                                updateQuestionData(partId, questionId);
                                markChanges();
                            }
                        }
                    });
                } else {
                    Swal.fire('Erreur', data.message || 'L\'IA n\'a pas pu optimiser cette question.', 'error');
                }
            } catch (error) {
                Swal.close();
                Swal.fire('Erreur', 'Impossible de contacter le service IA.', 'error');
            }
        }

        // Bouton de validation
        document.getElementById('validate-btn').addEventListener('click', function() {
            validateForm(true);
        });

        // ============ INITIALISATION ============

        // Initialiser les parties existantes
        setTimeout(() => {
            initializeExistingParts();
            loadFromLocalStorage();
        }, 100);

        // Ajouter des écouteurs de changements globaux après l'init
        setTimeout(() => {
            document.querySelectorAll('input, select, textarea').forEach(el => {
                el.addEventListener('change', markChanges);
            });
        }, 500);
    });
</script>