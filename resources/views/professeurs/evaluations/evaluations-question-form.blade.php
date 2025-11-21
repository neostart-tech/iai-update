@extends('professeurs.base', [
    'title' => 'Créer une évaluation',
    'page_name' => 'Créer une évaluation',
    'breadcrumbs' => ['Évaluations', 'Créer une évaluation'],
])

@section('bases')
    <div class="row">
        {{-- Formulaire de création de l'évaluation --}}
        <div class="col-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <h5>Ajouter des questions à l'évaluation</h5>
                </div>
                <div class="card-body">
                    <form id="evaluation-question-form">
                        @csrf

                        <div id="questions-container">
                            {{-- Les questions seront ajoutées ici dynamiquement --}}
                        </div>

                        <button type="button" class="btn btn-secondary" id="add-question-btn">Ajouter une question</button>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary">Enregistrer l'évaluation</button>
                        </div>
                    </form>

                    <div id="error-messages" class="alert alert-danger mt-3" style="display: none;"></div>
                    <div id="success-message" class="alert alert-success mt-3" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('other-js')
    <script>
        let questionCount = 0;

        // Fonction pour ajouter une nouvelle question avec un dropdown
        function addQuestion() {
            questionCount++;

            const questionDiv = document.createElement('div');
            questionDiv.classList.add('question', 'mb-5'); // Ajout d'une marge de séparation entre les questions
            questionDiv.setAttribute('data-question-id', questionCount);

            // Création du dropdown pour la question (question pliable/ouvrable)
            const dropdown = document.createElement('div');
            dropdown.classList.add('dropdown-question');
            dropdown.innerHTML = `
            <div class="question-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Question ${questionCount}</h6>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleQuestion(${questionCount})">
                    <i class="fas fa-chevron-down" id="toggle-icon-${questionCount}"></i>
                </button>
            </div>
            <div id="question-body-${questionCount}" class="question-body" style="display: none;">
                <div class="form-group">
                    <label for="question_title_${questionCount}">Titre de la question</label>
                    <input type="text" class="form-control" id="question_title_${questionCount}" name="questions[${questionCount}][title]" required>
                </div>

                <div class="form-group">
                    <label for="question_statement_${questionCount}">Enoncé de la question</label>
                    <textarea class="form-control" id="question_statement_${questionCount}" name="questions[${questionCount}][statement]" required></textarea>
                </div>

                <div class="form-group">
                    <label for="question_type_${questionCount}">Type de question</label>
                    <select class="form-control" id="question_type_${questionCount}" name="questions[${questionCount}][type]" required>
                        <option value="text">Texte court</option>
                        <option value="textarea">Texte long</option>
                        <option value="choice_single">Choix unique</option>
                        <option value="choice_multiple">Choix multiples</option>
                    </select>
                </div>

                <div class="form-group" id="question_options_${questionCount}">
                    <!-- Les options seront ajoutées ici dynamiquement -->
                </div>

                <div class="form-group">
                    <label for="question_points_${questionCount}">Points</label>
                    <input type="number" class="form-control" id="question_points_${questionCount}" name="questions[${questionCount}][points]" min="0" step="0.1">
                </div>

                <button type="button" class="btn btn-danger btn-sm" onclick="removeQuestion(${questionCount})">Supprimer cette question</button>
            </div>
        `;

            // Ajouter le dropdown de la question dans le conteneur
            document.getElementById('questions-container').appendChild(dropdown);

            // Ajouter un événement pour le changement de type de question (pour gérer les options)
            const questionTypeSelect = document.getElementById(`question_type_${questionCount}`);
            questionTypeSelect.addEventListener('change', function() {
                generateOptions(questionCount, this.value);
            });

            // Générer les options initiales en fonction du type de question
            generateOptions(questionCount, questionTypeSelect.value);
        }

        // Fonction pour générer les options en fonction du type de question
        function generateOptions(questionId, questionType) {
            const optionsDiv = document.getElementById(`question_options_${questionId}`);
            optionsDiv.innerHTML = ''; // Vider les options précédentes

            if (questionType === 'choice_single' || questionType === 'choice_multiple') {
                const optionsLabel = document.createElement('label');
                optionsLabel.innerHTML = 'Définissez les options de réponse';
                optionsDiv.appendChild(optionsLabel);

                // Créer 3 options par défaut
                for (let i = 0; i < 3; i++) {
                    const optionDiv = document.createElement('div');
                    optionDiv.classList.add('form-group');
                    optionDiv.classList.add('option-div');
                    optionDiv.classList.add('mb-2'); // Ajout de l'espacement entre chaque option

                    const inputType = questionType === 'choice_single' ? 'radio' : 'checkbox';

                    optionDiv.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input" type="${inputType}" name="questions[${questionId}][options][${i}][label]" id="option_${questionId}_${i}" value="option_${i}">
                        <input type="text" class="form-control" name="questions[${questionId}][options_text][${i}][label]" id="option_text_${questionId}_${i}" placeholder="Saisir l'option ici" required>
                        <button type="button" class="btn btn-danger btn-sm mt-1" onclick="removeOption(${questionId}, ${i})">Supprimer l'option</button>
                    </div>
                `;
                    optionsDiv.appendChild(optionDiv);
                }

                // Ajouter un bouton pour ajouter plus d'options
                const addOptionBtn = document.createElement('button');
                addOptionBtn.type = 'button';
                addOptionBtn.classList.add('btn', 'btn-info', 'btn-sm', 'mt-3');
                addOptionBtn.innerHTML = 'Ajouter une option';
                addOptionBtn.onclick = () => addOption(questionId, questionType);
                optionsDiv.appendChild(addOptionBtn);
            } else if (questionType === 'text' || questionType === 'textarea') {
                // Pas besoin d'options pour les questions texte
                const inputLabel = questionType === 'text' ? 'Réponse courte' : 'Réponse longue';
                optionsDiv.innerHTML = ` 
                <label for="question_answer_${questionId}">${inputLabel}</label>
                <input type="text" class="form-control" id="question_answer_${questionId}" name="questions[${questionId}][answer]" placeholder="Votre réponse">
            `;
            }
        }

        // Fonction pour ajouter une option pour les questions de type choix
        function addOption(questionId, questionType) {
            const optionsDiv = document.getElementById(`question_options_${questionId}`);
            const optionCount = optionsDiv.querySelectorAll('.form-group').length;

            const optionDiv = document.createElement('div');
            optionDiv.classList.add('form-group');
            optionDiv.classList.add('option-div');
            optionDiv.classList.add('mb-2'); // Ajout de l'espacement entre les options

            const inputType = questionType === 'choice_single' ? 'radio' : 'checkbox';

            optionDiv.innerHTML = `
            <div class="form-check">
                <input class="form-check-input" type="${inputType}" name="questions[${questionId}][options][${optionCount}][label]" id="option_${questionId}_${optionCount}" value="option_${optionCount}">
                <input type="text" class="form-control" name="questions[${questionId}][options_text][${optionCount}][label]" id="option_text_${questionId}_${optionCount}" placeholder="Saisir l'option ici" required>
                <button type="button" class="btn btn-danger btn-sm mt-1" onclick="removeOption(${questionId}, ${optionCount})">Supprimer l'option</button>
            </div>
        `;
            optionsDiv.appendChild(optionDiv);
        }

        // Fonction pour supprimer une option
        function removeOption(questionId, optionId) {
            const optionDiv = document.querySelector(`#question_options_${questionId}`).querySelectorAll('.option-div')[
                optionId];
            optionDiv.remove();
        }

        // Fonction pour supprimer une question
        function removeQuestion(questionId) {
            const questionDiv = document.querySelector(`[data-question-id="${questionId}"]`);
            questionDiv.remove();
        }

        // Fonction pour basculer l'affichage de chaque question
        function toggleQuestion(questionId) {
            const questionBody = document.getElementById(`question-body-${questionId}`);
            const toggleIcon = document.getElementById(`toggle-icon-${questionId}`);

            if (questionBody.style.display === 'none') {
                questionBody.style.display = 'block';
                toggleIcon.classList.replace('fa-chevron-down', 'fa-chevron-up');
            } else {
                questionBody.style.display = 'none';
                toggleIcon.classList.replace('fa-chevron-up', 'fa-chevron-down');
            }
        }

        // Ajouter la première question
        addQuestion();

        // Ajouter un bouton pour ajouter une nouvelle question
        document.getElementById('add-question-btn').addEventListener('click', addQuestion);

        // Fonction pour soumettre le formulaire via AJAX (API)
        document.getElementById('evaluation-question-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Empêcher le rechargement de la page

            const formData = new FormData(this); // Récupérer les données du formulaire
            const formObject = {}; // Créer un objet pour envoyer à l'API

            // Créer un tableau de questions à partir des données du formulaire
            const questions = [];
            let questionIndex = 1;

            // Collecter toutes les questions du formulaire
            while (document.querySelector(`#question_title_${questionIndex}`)) {
                const question = {
                    title: document.querySelector(`#question_title_${questionIndex}`).value,
                    statement: document.querySelector(`#question_statement_${questionIndex}`).value,
                    type: document.querySelector(`#question_type_${questionIndex}`).value,
                    points: document.querySelector(`#question_points_${questionIndex}`).value || 0,
                    options_text: []
                };

                // Si la question est de type "choix", ajouter les options
                if (question.type === 'choice_single' || question.type === 'choice_multiple') {
                    const options = document.querySelectorAll(
                        `#question_options_${questionIndex} .form-group input[type="text"]`);
                    options.forEach(option => {
                        question.options_text.push({
                            label: option.value
                        });
                    });
                }

                // Ajouter la question dans le tableau des questions
                questions.push(question);
                questionIndex++;
            }

            // Ajouter le tableau de questions à l'objet formObject
            formObject.questions = questions;

            // Envoi de la requête AJAX avec les données sous forme JSON
            fetch('{{ route('enseignants.evaluation.store-evaluation-question', $emploiDuTemp->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                    },
                    body: JSON.stringify(formObject), // Envoi des données en JSON
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Afficher un message de succès
                        document.getElementById('success-message').innerText = data.message;
                        document.getElementById('success-message').style.display = 'block';
                        document.getElementById('error-messages').style.display = 'none';
                    } else {
                        // Afficher les erreurs de validation
                        const errors = data.errors;
                        let errorMessages = '';
                        for (let field in errors) {
                            errorMessages += `<p>${errors[field].join(', ')}</p>`;
                        }
                        document.getElementById('error-messages').innerHTML = errorMessages;
                        document.getElementById('error-messages').style.display = 'block';
                        document.getElementById('success-message').style.display = 'none';
                    }
                })
                .catch(error => {
                    // Afficher un message d'erreur si la requête échoue
                    document.getElementById('error-messages').innerText =
                        'Une erreur est survenue lors de l\'enregistrement.';
                    document.getElementById('error-messages').style.display = 'block';
                    document.getElementById('success-message').style.display = 'none';
                });
        });
    </script>
@endsection
