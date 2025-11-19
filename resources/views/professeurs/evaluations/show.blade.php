@extends('base')
@section('content')
<h3>{{ $evaluation->title }}</h3>
<form id="evaluation-form">
{{-- //Blade etudiant affichage et autosave --}}

    @csrf
    @foreach($evaluation->questions as $question)
        <div class="question" data-id="{{ $question->id }}">
            <p>{{ $question->order + 1 }}. {{ $question->title }}</p>
            @if($question->type == 'text' || $question->type == 'textarea')
                <textarea name="answers[{{ $question->id }}][text]" rows="3">{{ optional($submission->answers->where('question_id', $question->id)->first())->answer_text }}</textarea>
            @elseif($question->type == 'choice_single')
                @foreach($question->options as $option)
                    <label>
                        <input type="radio" name="answers[{{ $question->id }}][options][]" value="{{ $option->id }}"
                            @if(in_array($option->id, optional($submission->answers->where('question_id', $question->id)->first())->answer_options ?? [])) checked @endif>
                        {{ $option->label }}
                    </label><br>
                @endforeach
            @elseif($question->type == 'choice_multiple')
                @foreach($question->options as $option)
                    <label>
                        <input type="checkbox" name="answers[{{ $question->id }}][options][]" value="{{ $option->id }}"
                            @if(in_array($option->id, optional($submission->answers->where('question_id', $question->id)->first())->answer_options ?? [])) checked @endif>
                        {{ $option->label }}
                    </label><br>
                @endforeach
            @endif
        </div>
    @endforeach
    <button type="submit">Soumettre l'évaluation</button>
</form>

<script>
const form = document.getElementById('evaluation-form');

form.addEventListener('submit', function(e) {
    e.preventDefault();
    fetch("{{ route('etudiants.evaluations.submit', $evaluation) }}", {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: new FormData(form)
    }).then(r=>r.json()).then(res=>location.href='{{ route("etudiants.dashboard") }}');
});

// Autosave toutes les 30s
setInterval(() => {
    fetch("{{ route('etudiants.evaluations.save', $evaluation) }}", {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: new FormData(form)
    }).then(r=>r.json()).then(res=>console.log(res.message));
}, 30000);
</script>
@endsection
