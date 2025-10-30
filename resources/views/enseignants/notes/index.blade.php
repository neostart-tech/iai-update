@extends('professeurs.base', [
    'title' => "Saisie des notes",
    'page_name' => "Saisie des notes",
    'breadcrumbs' => [
        ['text' => 'Mes évaluations', 'url' => route('enseignants.index')],
        'Saisie des notes',
    ],
])

@section('bases')
<div class="card">
  <div class="card-header">
    <strong>{{ $evaluation->matiere->nom }}</strong> — {{ $evaluation->group->nom }}
  </div>
  <div class="card-body">
    @if ($correctionEnable)
    <form method="post" action="{{ route('enseignants.notes.store', $evaluation) }}">
      @csrf
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Matricule</th>
              <th>Nom & Prénoms</th>
              <th>Anonymat</th>
              <th>Note (0-20) ou R</th>
            </tr>
          </thead>
          <tbody>
          @foreach ($notes->sortBy('anonymat') as $i => $note)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $note->etudiant->matricule }}</td>
              <td>{{ $note->etudiant->nom }} {{ $note->etudiant->prenom }}</td>
              <td>{{ $note->anonymat }}</td>
              <td>
      <input type="text" class="form-control"
        name="notes[]" value="{{ $note->notation === 'R' ? 'R' : $note->note }}" placeholder="0-20 ou R">
                <input type="hidden" name="etudiants[]" value="{{ $note->etudiant->slug }}">
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      <div class="text-end">
        <button class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
    @else
      <div class="alert alert-warning">
        La période de saisie/correction des notes est clôturée pour cette évaluation.
      </div>
    @endif
  </div>
</div>
@endsection
