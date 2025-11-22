@extends('professeurs.base', [
    'title' => 'Créer une évaluation',
    'page_name' => 'Créer une évaluation',
    'breadcrumbs' => ['Évaluations', 'Créer une évaluation'],
])

@section('bases')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar avec aperçu et statistiques -->
        <div class="col-lg-4 mb-4">
            <div class="sticky-top" style="top: 20px;">
                <!-- Aperçu de l'évaluation -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-eye me-2"></i>Aperçu de l'évaluation</h6>
                    </div>
                    <div class="card-body">
                        <div class="evaluation-preview">
                            <div class="preview-stats">
                                <div class="stat-item d-flex justify-content-between mb-2">
                                    <span class="text-muted">Nombre de questions:</span>
                                    <strong id="question-count">0</strong>
                                </div>
                                <div class="stat-item d-flex justify-content-between mb-2">
                                    <span class="text-muted">Points totaux:</span>
                                    <strong id="total-points">0</strong>
                                </div>
                                <div class="stat-item d-flex justify-content-between mb-2">
                                    <span class="text-muted">Limite:</span>
                                    <strong id="points-limit" class="text-success">20/20</strong>
                                </div>
                                <div class="stat-item d-flex justify-content-between mb-2">
                                    <span class="text-muted">Types de questions:</span>
                                    <div id="question-types" class="text-end"></div>
                                </div>
                            </div>
                            <!-- Barre de progression -->
                            <div class="progress mt-3" style="height: 8px;">
                                <div id="points-progress" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small id="points-warning" class="text-danger mt-1" style="display: none;">
                                <i class="fas fa-exclamation-triangle me-1"></i>Dépassement de la limite de 20 points
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Guide rapide -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Guide rapide</h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Cliquez sur "Ajouter une question" pour créer de nouvelles questions</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Utilisez les flèches pour développer/réduire les questions</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Attribuez des points à chaque question (max 20 au total)</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Prévisualisez votre évaluation à droite</li>
                            </ul>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire principal -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-primary">
                            <i class="fas fa-plus-circle me-2"></i>
                            @if($evaluation && $evaluation->questions->count() > 0)
                                Modifier les questions de l'évaluation
                            @else
                                Créer une nouvelle évaluation
                            @endif
                        </h4>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary fs-6 me-2" id="live-question-count">0 question</span>
                            <span class="badge bg-success fs-6" id="total-points-badge">0/20 pts</span>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <form id="evaluation-question-form">
                        @csrf

                        <!-- Container des questions -->
                        <div id="questions-container" class="questions-container">
                            <!-- Les questions seront ajoutées ici dynamiquement -->
                        </div>

                        <!-- Bouton d'ajout de question -->
                        <div class="text-center mt-4 mb-4">
                            <button type="button" class="btn btn-primary btn-lg" id="add-question-btn">
                                <i class="fas fa-plus me-2"></i>Ajouter une question
                            </button>
                        </div>

                        <!-- Actions du formulaire -->
                        <div class="form-actions mt-5 pt-4 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <button type="button" class="btn btn-outline-secondary" id="preview-btn">
                                        <i class="fas fa-eye me-2"></i>Prévisualiser
                                    </button>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-success btn-lg px-4" id="submit-btn">
                                        <i class="fas fa-save me-2"></i>
                                        @if($evaluation && $evaluation->questions->count() > 0)
                                            Mettre à jour l'évaluation
                                        @else
                                            Enregistrer l'évaluation
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Messages d'alerte -->
                    <div id="error-messages" class="alert alert-danger mt-3" style="display: none;"></div>
                    <div id="success-message" class="alert alert-success mt-3" style="display: none;"></div>
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
@endsection

@section('other-css')
<style>
    :root {
        --primary-color: #4680ff;
        --secondary-color: #4680ff;
        --success-color: #4cc9f0;
        --warning-color: #f72585;
        --danger-color: #dc3545;
    }

    .questions-container {
        max-height: 70vh;
        overflow-y: auto;
        padding-right: 10px;
    }

    .questions-container::-webkit-scrollbar {
        width: 6px;
    }

    .questions-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .questions-container::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .questions-container::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    .question-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .question-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        border-color: var(--primary-color);
    }

    .question-card.expanded {
        border-color: var(--primary-color);
        box-shadow: 0 4px 20px rgba(67, 97, 238, 0.15);
    }

    .question-card.exceeds-limit {
        border-color: var(--danger-color);
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.15);
    }

    .question-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.25rem;
        cursor: pointer;
        border-bottom: 1px solid transparent;
        transition: all 0.3s ease;
    }

    .question-card.expanded .question-header {
        background: linear-gradient(135deg, #4680ff 0%, #4680ff 100%);
        color: white;
        border-bottom-color: rgba(255,255,255,0.2);
    }

    .question-card.exceeds-limit .question-header {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    }

    .question-header h5 {
        margin: 0;
        font-weight: 600;
    }

    .question-body {
        padding: 1.5rem;
        background: white;
    }

    .question-number {
        background: var(--primary-color);
        color: white;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.9rem;
    }

    .question-card.expanded .question-number {
        background: white;
        color: var(--primary-color);
    }

    .question-card.exceeds-limit .question-number {
        background: var(--danger-color);
    }

    .toggle-icon {
        transition: transform 0.3s ease;
    }

    .question-card.expanded .toggle-icon {
        transform: rotate(180deg);
        color: white;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
    }

    .form-control.exceeds-limit {
        border-color: var(--danger-color);
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .points-input-container {
        position: relative;
    }

    .points-feedback {
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    .points-feedback.exceeds-limit {
        color: var(--danger-color);
        font-weight: 600;
    }

    .options-container {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.25rem;
        margin-top: 1rem;
    }

    .option-item {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        transition: all 0.2s ease;
    }

    .option-item:hover {
        border-color: var(--primary-color);
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .option-item:last-child {
        margin-bottom: 0;
    }

    .btn-delete {
        transition: all 0.2s ease;
    }

    .btn-delete:hover {
        transform: scale(1.05);
    }

    .preview-stats {
        font-size: 0.9rem;
    }

    .type-badge {
        font-size: 0.7rem;
        margin-left: 0.25rem;
    }

    /* Animation pour l'ajout de questions */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .question-card.new {
        animation: slideIn 0.3s ease-out;
    }

    /* Styles pour la prévisualisation */
    .preview-question {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }

    .preview-question h6 {
        color: var(--primary-color);
        margin-bottom: 1rem;
    }

    .preview-option {
        padding: 0.5rem 0;
    }

    .progress-bar.exceeds-limit {
        background-color: var(--danger-color) !important;
    }
</style>
@endsection

@section('other-js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let questionCount = 0;
    let questionsData = [];
    const MAX_POINTS = 20;

    // Données des questions existantes
    const existingQuestions = @json($evaluation->questions ?? []);

    // Fonction pour obtenir le libellé du type
    function getTypeLabel(type) {
        const types = {
            'text': 'Texte court',
            'textarea': 'Texte long',
            'choice_single': 'Choix unique',
            'choice_multiple': 'Choix multiples'
        };
        return types[type] || type;
    }

    // Fonction pour vérifier si le total des points dépasse la limite
    function checkPointsLimit() {
        const totalPoints = questionsData.reduce((sum, q) => sum + (parseFloat(q.points) || 0), 0);
        const exceedsLimit = totalPoints > MAX_POINTS;
        
        // Mettre à jour la barre de progression
        const progressBar = document.getElementById('points-progress');
        const progressPercentage = Math.min((totalPoints / MAX_POINTS) * 100, 100);
        progressBar.style.width = `${progressPercentage}%`;
        
        if (exceedsLimit) {
            progressBar.classList.add('exceeds-limit');
            progressBar.classList.remove('bg-success');
            progressBar.classList.add('bg-danger');
        } else {
            progressBar.classList.remove('exceeds-limit');
            progressBar.classList.remove('bg-danger');
            progressBar.classList.add('bg-success');
        }
        
        // Mettre à jour l'affichage des points
        document.getElementById('points-limit').textContent = `${totalPoints.toFixed(1)}/20`;
        document.getElementById('total-points-badge').textContent = `${totalPoints.toFixed(1)}/20 pts`;
        
        if (exceedsLimit) {
            document.getElementById('points-limit').classList.add('text-danger');
            document.getElementById('points-limit').classList.remove('text-success');
            document.getElementById('points-warning').style.display = 'block';
        } else {
            document.getElementById('points-limit').classList.remove('text-danger');
            document.getElementById('points-limit').classList.add('text-success');
            document.getElementById('points-warning').style.display = 'none';
        }
        
        // Désactiver le bouton de soumission si la limite est dépassée
        const submitBtn = document.getElementById('submit-btn');
        if (exceedsLimit) {
            submitBtn.disabled = true;
            submitBtn.classList.remove('btn-success');
            submitBtn.classList.add('btn-danger');
        } else {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-danger');
            submitBtn.classList.add('btn-success');
        }
        
        return exceedsLimit;
    }

    // Fonction pour mettre à jour l'affichage des points d'une question
    function updateQuestionPointsDisplay(questionId) {
        const questionElem = document.querySelector(`[data-question-id="${questionId}"]`);
        const pointsInput = questionElem.querySelector('.question-points');
        const pointsValue = parseFloat(pointsInput.value) || 0;
        const totalPoints = questionsData.reduce((sum, q) => sum + (parseFloat(q.points) || 0), 0);
        
        // Vérifier si cette question fait dépasser la limite
        const otherQuestionsPoints = questionsData
            .filter(q => q.id !== questionId)
            .reduce((sum, q) => sum + (parseFloat(q.points) || 0), 0);
        
        const wouldExceedLimit = (otherQuestionsPoints + pointsValue) > MAX_POINTS;
        
        // Mettre à jour l'apparence
        if (wouldExceedLimit) {
            pointsInput.classList.add('exceeds-limit');
            questionElem.classList.add('exceeds-limit');
            
            // Afficher le feedback
            let feedbackElem = questionElem.querySelector('.points-feedback');
            if (!feedbackElem) {
                feedbackElem = document.createElement('div');
                feedbackElem.className = 'points-feedback exceeds-limit';
                pointsInput.parentNode.appendChild(feedbackElem);
            }
            const remaining = MAX_POINTS - otherQuestionsPoints;
            feedbackElem.textContent = `Limite dépassée! Maximum recommandé: ${remaining.toFixed(1)} points`;
        } else {
            pointsInput.classList.remove('exceeds-limit');
            questionElem.classList.remove('exceeds-limit');
            
            // Supprimer le feedback
            const feedbackElem = questionElem.querySelector('.points-feedback');
            if (feedbackElem) {
                feedbackElem.remove();
            }
        }
    }

    // Fonction pour charger les questions existantes
    function loadExistingQuestions() {
        if (existingQuestions && existingQuestions.length > 0) {
            // Vider le conteneur actuel
            document.getElementById('questions-container').innerHTML = '';
            questionsData = [];
            questionCount = 0;
            
            // Charger chaque question existante
            existingQuestions.forEach((existingQuestion, index) => {
                questionCount++;
                
                const questionCard = document.createElement('div');
                questionCard.className = 'question-card new';
                questionCard.setAttribute('data-question-id', questionCount);

                questionCard.innerHTML = `
                    <div class="question-header" onclick="toggleQuestion(${questionCount})">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="question-number me-3">${questionCount}</div>
                                <h5 class="mb-0">${existingQuestion.title || 'Question ' + questionCount}</h5>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-light text-dark me-2 question-type-badge">${getTypeLabel(existingQuestion.type)}</span>
                                <i class="fas fa-chevron-down toggle-icon text-muted"></i>
                            </div>
                        </div>
                    </div>
                    <div class="question-body" id="question-body-${questionCount}" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Titre de la question</label>
                                    <input type="text" class="form-control question-title" 
                                           value="${existingQuestion.title || ''}" 
                                           placeholder="Ex: Question sur les fonctions" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group points-input-container">
                                    <label class="form-label">Points (max 20 au total)</label>
                                    <input type="number" class="form-control question-points" 
                                           min="0" max="20" step="0.1" value="${existingQuestion.points || 5}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Énoncé de la question</label>
                            <textarea class="form-control question-statement" rows="3" 
                                      placeholder="Décrivez la question en détail..." required>${existingQuestion.statement || ''}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Type de question</label>
                            <select class="form-select question-type" required>
                                <option value="text" ${existingQuestion.type === 'text' ? 'selected' : ''}>Texte court</option>
                                <option value="textarea" ${existingQuestion.type === 'textarea' ? 'selected' : ''}>Texte long</option>
                                <option value="choice_single" ${existingQuestion.type === 'choice_single' ? 'selected' : ''}>Choix unique</option>
                                <option value="choice_multiple" ${existingQuestion.type === 'choice_multiple' ? 'selected' : ''}>Choix multiples</option>
                            </select>
                        </div>

                        <div class="options-container" id="question-options-${questionCount}">
                            <!-- Les options seront générées ici -->
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <button type="button" class="btn btn-outline-danger btn-delete" onclick="removeQuestion(${questionCount})">
                                <i class="fas fa-trash me-2"></i>Supprimer cette question
                            </button>
                            <small class="text-muted">Question ${questionCount}</small>
                        </div>
                    </div>
                `;

                document.getElementById('questions-container').appendChild(questionCard);
                
                // Générer les options selon le type
                generateExistingOptions(questionCount, existingQuestion);
                
                // Initialiser les événements
                initQuestionEvents(questionCount);
                
                // Mettre à jour le badge du type
                updateTypeBadge(questionCount, existingQuestion.type);
                
                // Mettre à jour les données
                updateQuestionData(questionCount);
            });
            
            // Mettre à jour l'aperçu
            updatePreview();
            
            // Développer la première question
            if (questionCount > 0) {
                setTimeout(() => toggleQuestion(1), 300);
            }
        } else {
            // Aucune question existante, ajouter une question vide
            addQuestion();
        }
    }

    // Fonction pour générer les options existantes
    function generateExistingOptions(questionId, existingQuestion) {
        const optionsContainer = document.getElementById(`question-options-${questionId}`);
        const questionType = existingQuestion.type;
        
        if (questionType === 'choice_single' || questionType === 'choice_multiple') {
            const inputType = questionType === 'choice_single' ? 'radio' : 'checkbox';
            const options = existingQuestion.options || [];
            
            let optionsHTML = `
                <label class="form-label mb-3">Options de réponse</label>
                <div id="options-list-${questionId}">
            `;
            
            if (options.length > 0) {
                optionsHTML += options.map((option, index) => `
                    <div class="option-item" data-option-id="${index + 1}">
                        <div class="d-flex align-items-center gap-3">
                            <input type="${inputType}" class="form-check-input" disabled>
                            <input type="text" class="form-control option-text" 
                                   value="${option.label || option.option_text || 'Option ' + (index + 1)}"
                                   placeholder="Option ${index + 1}">
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="removeOption(${questionId}, ${index + 1})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            } else {
                // Si pas d'options en base, créer des options par défaut
                optionsHTML += [1, 2, 3].map(i => `
                    <div class="option-item" data-option-id="${i}">
                        <div class="d-flex align-items-center gap-3">
                            <input type="${inputType}" class="form-check-input" disabled>
                            <input type="text" class="form-control option-text" 
                                   placeholder="Option ${i}" value="Option ${i}">
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="removeOption(${questionId}, ${i})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            }
            
            optionsHTML += `
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                        onclick="addNewOption(${questionId})">
                    <i class="fas fa-plus me-1"></i>Ajouter une option
                </button>
            `;
            
            optionsContainer.innerHTML = optionsHTML;
            
            // Initialiser les événements des options
            initOptionEvents(questionId);
        } else {
            optionsContainer.innerHTML = '';
        }
    }

    // Fonction pour mettre à jour l'aperçu
    function updatePreview() {
        const questionCountElem = document.getElementById('question-count');
        const liveQuestionCountElem = document.getElementById('live-question-count');
        const totalPointsElem = document.getElementById('total-points');
        const questionTypesElem = document.getElementById('question-types');
        
        const totalPoints = questionsData.reduce((sum, q) => sum + (parseFloat(q.points) || 0), 0);
        const typeCount = {};
        
        questionsData.forEach(q => {
            typeCount[q.type] = (typeCount[q.type] || 0) + 1;
        });

        questionCountElem.textContent = questionsData.length;
        liveQuestionCountElem.textContent = `${questionsData.length} question${questionsData.length !== 1 ? 's' : ''}`;
        totalPointsElem.textContent = totalPoints.toFixed(1);
        
        questionTypesElem.innerHTML = Object.entries(typeCount)
            .map(([type, count]) => 
                `<span class="badge bg-secondary type-badge">${getTypeLabel(type)}: ${count}</span>`
            ).join(' ');
        
        // Vérifier la limite des points
        checkPointsLimit();
    }

    // Fonction pour ajouter une nouvelle question
    function addQuestion() {
        questionCount++;
        
        const questionCard = document.createElement('div');
        questionCard.className = 'question-card new';
        questionCard.setAttribute('data-question-id', questionCount);

        questionCard.innerHTML = `
            <div class="question-header" onclick="toggleQuestion(${questionCount})">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="question-number me-3">${questionCount}</div>
                        <h5 class="mb-0">Question ${questionCount}</h5>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-light text-dark me-2 question-type-badge">Texte court</span>
                        <i class="fas fa-chevron-down toggle-icon text-muted"></i>
                    </div>
                </div>
            </div>
            <div class="question-body" id="question-body-${questionCount}" style="display: none;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Titre de la question</label>
                            <input type="text" class="form-control question-title" 
                                   placeholder="Ex: Question sur les fonctions" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group points-input-container">
                            <label class="form-label">Points (max 20 au total)</label>
                            <input type="number" class="form-control question-points" 
                                   min="0" max="20" step="0.1" placeholder="5" value="5">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Énoncé de la question</label>
                    <textarea class="form-control question-statement" rows="3" 
                              placeholder="Décrivez la question en détail..." required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Type de question</label>
                    <select class="form-select question-type" required>
                        <option value="text">Texte court</option>
                        <option value="textarea">Texte long</option>
                        <option value="choice_single">Choix unique</option>
                        <option value="choice_multiple">Choix multiples</option>
                    </select>
                </div>

                <div class="options-container" id="question-options-${questionCount}">
                    <!-- Les options seront générées ici -->
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <button type="button" class="btn btn-outline-danger btn-delete" onclick="removeQuestion(${questionCount})">
                        <i class="fas fa-trash me-2"></i>Supprimer cette question
                    </button>
                    <small class="text-muted">Question ${questionCount}</small>
                </div>
            </div>
        `;

        document.getElementById('questions-container').appendChild(questionCard);
        
        // Initialiser les événements
        initQuestionEvents(questionCount);
        
        // Générer les options initiales
        generateOptions(questionCount, 'text');
        
        // Développer la nouvelle question
        setTimeout(() => toggleQuestion(questionCount), 100);
        
        // Mettre à jour les données
        updateQuestionData(questionCount);
        updatePreview();
    }

    // Initialiser les événements pour une question
    function initQuestionEvents(questionId) {
        const questionElem = document.querySelector(`[data-question-id="${questionId}"]`);
        
        // Événement pour le type de question
        questionElem.querySelector('.question-type').addEventListener('change', function() {
            generateOptions(questionId, this.value);
            updateQuestionData(questionId);
            updateTypeBadge(questionId, this.value);
        });

        // Événements pour les champs de saisie
        const inputs = questionElem.querySelectorAll('.question-title, .question-statement');
        inputs.forEach(input => {
            input.addEventListener('input', () => updateQuestionData(questionId));
        });

        // Événement spécial pour les points
        const pointsInput = questionElem.querySelector('.question-points');
        pointsInput.addEventListener('input', function() {
            // Limiter la valeur maximale à 20
            if (parseFloat(this.value) > 20) {
                this.value = 20;
            }
            updateQuestionData(questionId);
            updateQuestionPointsDisplay(questionId);
        });
    }

    // Mettre à jour le badge du type
    function updateTypeBadge(questionId, type) {
        const badge = document.querySelector(`[data-question-id="${questionId}"] .question-type-badge`);
        badge.textContent = getTypeLabel(type);
        badge.className = `badge me-2 question-type-badge ${
            type.includes('choice') ? 'bg-warning text-dark' : 
            type === 'textarea' ? 'bg-info' : 'bg-secondary'
        }`;
    }

    // Générer les options selon le type
    function generateOptions(questionId, questionType) {
        const optionsContainer = document.getElementById(`question-options-${questionId}`);
        
        if (questionType === 'choice_single' || questionType === 'choice_multiple') {
            const inputType = questionType === 'choice_single' ? 'radio' : 'checkbox';
            
            optionsContainer.innerHTML = `
                <label class="form-label mb-3">Options de réponse</label>
                <div id="options-list-${questionId}">
                    ${[1, 2, 3].map(i => `
                        <div class="option-item" data-option-id="${i}">
                            <div class="d-flex align-items-center gap-3">
                                <input type="${inputType}" class="form-check-input" disabled>
                                <input type="text" class="form-control option-text" 
                                       placeholder="Option ${i}" value="Option ${i}">
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        onclick="removeOption(${questionId}, ${i})">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                        onclick="addNewOption(${questionId})">
                    <i class="fas fa-plus me-1"></i>Ajouter une option
                </button>
            `;
            
            // Initialiser les événements des options
            initOptionEvents(questionId);
        } else {
            optionsContainer.innerHTML = '';
        }
    }

    // Initialiser les événements des options
    function initOptionEvents(questionId) {
        const optionInputs = document.querySelectorAll(`#options-list-${questionId} .option-text`);
        optionInputs.forEach(input => {
            input.addEventListener('input', () => updateQuestionData(questionId));
        });
    }

    // Ajouter une nouvelle option
    function addNewOption(questionId) {
        const optionsList = document.getElementById(`options-list-${questionId}`);
        const optionCount = optionsList.children.length + 1;
        const questionType = document.querySelector(`[data-question-id="${questionId}"] .question-type`).value;
        const inputType = questionType === 'choice_single' ? 'radio' : 'checkbox';
        
        const optionItem = document.createElement('div');
        optionItem.className = 'option-item';
        optionItem.setAttribute('data-option-id', optionCount);
        optionItem.innerHTML = `
            <div class="d-flex align-items-center gap-3">
                <input type="${inputType}" class="form-check-input" disabled>
                <input type="text" class="form-control option-text" 
                       placeholder="Option ${optionCount}">
                <button type="button" class="btn btn-sm btn-outline-danger" 
                        onclick="removeOption(${questionId}, ${optionCount})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        optionsList.appendChild(optionItem);
        initOptionEvents(questionId);
        updateQuestionData(questionId);
    }

    // Supprimer une option
    function removeOption(questionId, optionId) {
        const optionItem = document.querySelector(`#options-list-${questionId} [data-option-id="${optionId}"]`);
        if (optionItem && document.querySelectorAll(`#options-list-${questionId} .option-item`).length > 1) {
            optionItem.remove();
            updateQuestionData(questionId);
        }
    }

    // Basculer l'affichage d'une question
    function toggleQuestion(questionId) {
        const questionCard = document.querySelector(`[data-question-id="${questionId}"]`);
        const questionBody = document.getElementById(`question-body-${questionId}`);
        const isExpanded = questionBody.style.display !== 'none';
        
        if (isExpanded) {
            questionBody.style.display = 'none';
            questionCard.classList.remove('expanded');
        } else {
            questionBody.style.display = 'block';
            questionCard.classList.add('expanded');
        }
    }

    // Supprimer une question
    function removeQuestion(questionId) {
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
                const questionCard = document.querySelector(`[data-question-id="${questionId}"]`);
                questionCard.style.opacity = '0';
                questionCard.style.transform = 'translateX(100px)';
                
                setTimeout(() => {
                    questionCard.remove();
                    questionsData = questionsData.filter(q => q.id !== questionId);
                    updatePreview();
                    renumberQuestions();
                }, 300);
            }
        });
    }

    // Renuméroter les questions
    function renumberQuestions() {
        const questionCards = document.querySelectorAll('.question-card');
        questionCards.forEach((card, index) => {
            const newNumber = index + 1;
            const questionId = card.getAttribute('data-question-id');
            card.querySelector('.question-number').textContent = newNumber;
            card.querySelector('h5').textContent = `Question ${newNumber}`;
            card.querySelector('.question-body small').textContent = `Question ${newNumber}`;
            
            // Mettre à jour l'ID dans les données
            const questionIndex = questionsData.findIndex(q => q.id == questionId);
            if (questionIndex !== -1) {
                questionsData[questionIndex].id = newNumber;
            }
        });
    }

    // Mettre à jour les données de la question
    function updateQuestionData(questionId) {
        const questionElem = document.querySelector(`[data-question-id="${questionId}"]`);
        const type = questionElem.querySelector('.question-type').value;
        
        let questionData = {
            id: questionId,
            title: questionElem.querySelector('.question-title').value,
            statement: questionElem.querySelector('.question-statement').value,
            type: type,
            points: parseFloat(questionElem.querySelector('.question-points').value) || 0
        };

        if (type === 'choice_single' || type === 'choice_multiple') {
            questionData.options_text = Array.from(questionElem.querySelectorAll('.option-text'))
                .map(input => ({ label: input.value }));
        }

        const existingIndex = questionsData.findIndex(q => q.id === questionId);
        if (existingIndex !== -1) {
            questionsData[existingIndex] = questionData;
        } else {
            questionsData.push(questionData);
        }

        updatePreview();
    }

    // Générer la prévisualisation
    function generatePreview() {
        const previewContent = document.getElementById('preview-content');
        previewContent.innerHTML = '';

        if (questionsData.length === 0) {
            previewContent.innerHTML = '<p class="text-muted text-center">Aucune question créée</p>';
            return;
        }

        questionsData.forEach((question, index) => {
            const questionDiv = document.createElement('div');
            questionDiv.className = 'preview-question';
            
            let optionsHTML = '';
            if (question.options_text) {
                optionsHTML = question.options_text.map(option => `
                    <div class="preview-option">
                        <div class="form-check">
                            <input class="form-check-input" type="${
                                question.type === 'choice_single' ? 'radio' : 'checkbox'
                            }" disabled>
                            <label class="form-check-label">${option.label}</label>
                        </div>
                    </div>
                `).join('');
            } else if (question.type === 'text') {
                optionsHTML = '<input type="text" class="form-control" placeholder="Réponse courte" disabled>';
            } else if (question.type === 'textarea') {
                optionsHTML = '<textarea class="form-control" rows="3" placeholder="Réponse longue" disabled></textarea>';
            }

            questionDiv.innerHTML = `
                <h6>${index + 1}. ${question.title || 'Sans titre'} <span class="badge bg-secondary">${question.points} pts</span></h6>
                <p class="mb-3">${question.statement || 'Aucun énoncé'}</p>
                ${optionsHTML}
            `;
            
            previewContent.appendChild(questionDiv);
        });
    }

    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        // Charger les questions existantes ou ajouter une nouvelle question
        loadExistingQuestions();

        // Événement pour le bouton d'ajout
        document.getElementById('add-question-btn').addEventListener('click', addQuestion);

        // Événement pour la prévisualisation
        document.getElementById('preview-btn').addEventListener('click', function() {
            generatePreview();
            new bootstrap.Modal(document.getElementById('previewModal')).show();
        });

        // Soumission du formulaire
        document.getElementById('evaluation-question-form').addEventListener('submit', function(event) {
            event.preventDefault();
            
            if (questionsData.length === 0) {
                Swal.fire('Erreur', 'Veuillez ajouter au moins une question', 'error');
                return;
            }

            // Validation des questions
            const invalidQuestions = questionsData.filter(q => !q.title || !q.statement);
            if (invalidQuestions.length > 0) {
                Swal.fire('Erreur', 'Certaines questions ont des champs manquants', 'error');
                return;
            }

            // Vérifier la limite des points
            if (checkPointsLimit()) {
                Swal.fire('Erreur', `Le total des points (${questionsData.reduce((sum, q) => sum + (parseFloat(q.points) || 0), 0).toFixed(1)}) dépasse la limite de 20 points`, 'error');
                return;
            }

            // Envoi des données
            const formData = {
                questions: questionsData
            };

            fetch('{{ route('enseignants.evaluation.store-evaluation-question', $emploiDuTemp->id) }}', {
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
                    Swal.fire('Succès', data.message, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    let errorMessages = '';
                    if (data.errors) {
                        for (let field in data.errors) {
                            errorMessages += `<p>${data.errors[field].join(', ')}</p>`;
                        }
                    }
                    document.getElementById('error-messages').innerHTML = errorMessages;
                    document.getElementById('error-messages').style.display = 'block';
                }
            })
            .catch(error => {
                Swal.fire('Erreur', 'Une erreur est survenue lors de l\'enregistrement', 'error');
            });
        });
    });
</script>
@endsection