@extends('base', [
    'title' => 'Évaluation en Cours',
    'breadcrumbs' => ['Évaluation', 'En Cours'],
    'page_name' => 'Évaluation en Cours',
])

@section('content')
<div class="card">
    <div class="card-body">
        @if ($evaluation)
            <h4>{{ $evaluation->type }} : {{ $evaluation->matiere->nom }}</h4>
            <p><strong>Durée :</strong>
                {{ $evaluation->debut->format('d/m/Y H:i') }} — {{ $evaluation->fin->format('d/m/Y H:i') }}
            </p>

            <!-- TIMER -->
            <div id="timer" class="mb-4">
                <strong>Temps restant :</strong>
                <span id="countdown">00:00</span>
            </div>

            @php
                $isDisabled = $isSubmitted ? 'disabled' : '';
            @endphp

            <!-- FORMULAIRE -->
            <form id="evaluationForm" 
                  action="{{ route('etudiants.evaluation.evaluation.submit', $evaluation->id) }}"
                  method="POST">
                @csrf

                @foreach ($evaluation->questions as $key => $question)
                    <div class="question mb-4 p-3" style="background:#f9f9f9;border-radius:8px">
                        <h5>{{ $key + 1 }}. {{ $question->title }}</h5>
                        <p>{{ $question->statement }}</p>

                        @php
                            $userAnswerText = $question->user_answer ?? '';
                            $userAnswerOptions = $question->user_answer_options ?? [];
                        @endphp

                        @if ($question->type == 'text' || $question->type == 'textarea')
                            @if ($question->is_long_text == 1)
                                <textarea class="form-control autosave-field"
                                          name="question_{{ $question->id }}" 
                                          rows="5"
                                          placeholder="Votre réponse ici..."
                                          {{ $isDisabled }}>{{ $userAnswerText }}</textarea>
                            @else
                                <input type="text" 
                                       class="form-control autosave-field"
                                       name="question_{{ $question->id }}"
                                       value="{{ $userAnswerText }}"
                                       placeholder="Votre réponse ici..."
                                       {{ $isDisabled }}>
                            @endif

                        @elseif ($question->type == 'choice_single')
                            @foreach ($question->options as $option)
                                <div class="form-check">
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

                        @elseif ($question->type == 'choice_multiple')
                            @foreach ($question->options as $option)
                                <div class="form-check">
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
                        @endif
                    </div>
                @endforeach

                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" {{ $isDisabled }}>
                        Soumettre l'Évaluation
                    </button>
                </div>
            </form>
        @else
            <p>Évaluation non trouvée.</p>
        @endif
    </div>
</div>
@endsection

@section('other-js')
<script>
    let autosaveEnabled = {{ $isSubmitted ? 'false' : 'true' }};
    let formLocked = {{ $isSubmitted ? 'true' : 'false' }};
    const countdownElem = document.getElementById("countdown");
    const timerElem = document.getElementById("timer");
    let countdownTime = new Date("{{ $evaluation->fin->format('Y-m-d H:i:s') }}").getTime();

    function updateTimer() {
        if(formLocked) return;

        let now = new Date().getTime();
        let distance = countdownTime - now;

        if(distance <= 0) {
            formLocked = true;
            autosaveEnabled = false;
            countdownElem.innerHTML = "Terminé";
            timerElem.style.color = "red";

            document.querySelectorAll("#evaluationForm input, #evaluationForm textarea, #evaluationForm button")
                .forEach(el => el.setAttribute("disabled", true));

            clearInterval(timerInterval);
            clearInterval(autosaveInterval);
            return;
        }

        let min = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        let sec = Math.floor((distance % (1000 * 60)) / 1000);
        countdownElem.innerHTML = min + ":" + (sec < 10 ? "0" + sec : sec);

        if(distance <= 5*60*1000) { // < 5 min
            timerElem.style.color = "red";
            timerElem.classList.add("blink");
        } else if(distance <= 15*60*1000) { // 5-15 min
            timerElem.style.color = "orange";
            timerElem.classList.remove("blink");
        } else { // >15 min
            timerElem.style.color = "green";
            timerElem.classList.remove("blink");
        }
    }

    let timerInterval = setInterval(updateTimer, 1000);

    function autosave() {
        if (!autosaveEnabled) return;
        let form = document.getElementById("evaluationForm");
        let formData = new FormData(form);

        fetch("{{ route('etudiants.evaluation.evaluation.autosave', $evaluation->id) }}", {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: formData
        });
    }

    let autosaveInterval = setInterval(() => {
        if (autosaveEnabled) autosave();
    }, 10000);

    document.querySelectorAll('.autosave-field').forEach(elem => {
        elem.addEventListener('change', autosave);
        elem.addEventListener('keyup', autosave);
    });

    // Confirmation avant soumission
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('evaluationForm');

    submitBtn.addEventListener('click', function(e){
        e.preventDefault(); 
        Swal.fire({
            title: 'Confirmer la soumission',
            text: "Êtes-vous sûr de vouloir soumettre l'évaluation ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, soumettre',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit(); 
            }
        })
    });

    // Afficher les messages flash
    @if(session('succes'))
        Swal.fire('Succès', '{{ session("succes") }}', 'succes');
    @endif

    @if(session('error'))
        Swal.fire('Erreur', '{{ session("error") }}', 'error');
    @endif

    @if(session('warning'))
        Swal.fire('Attention', '{{ session("warning") }}', 'warning');
    @endif

    // Si déjà soumis, timer “Terminé”
    @if($isSubmitted)
        countdownElem.innerHTML = "Terminé";
        timerElem.style.color = "red";
    @endif
</script>
@endsection

@section('other-css')
<style>
    #timer {
        font-size: 1.6rem;
        font-weight: bold;
        transition: color 0.3s;
    }

    .blink {
        animation: blinker 1s linear infinite;
    }

    @keyframes blinker {
        50% { opacity: 0; }
    }

    .question h5 {
        font-size: 1.2rem;
        font-weight: 600;
    }

    textarea,
    input[type="text"] {
        margin-top: 8px;
    }
</style>
@endsection
