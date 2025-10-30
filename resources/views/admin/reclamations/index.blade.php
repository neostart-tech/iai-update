@extends('base', [
    'title' => 'Réclamations',
    'page_name' => 'Réclamations',
    'breadcrumbs' => ['Réclamations']
])

@section('content')
<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Étudiant</th>
            <th>Évaluation</th>
            <th>Motif</th>
            <th>Justificatif</th>
            <th>Statut</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($reclamations as $i => $r)
          <tr>
            <td>{{ $reclamations->firstItem() + $i }}</td>
            <td>{{ $r->etudiant->matricule }} — {{ $r->etudiant->nom }} {{ $r->etudiant->prenom }}</td>
            <td>{{ optional($r->evaluation)->matiere->nom ?? '-' }} — {{ optional($r->evaluation)->group->nom ?? '' }}</td>
            <td style="max-width: 360px">{{ $r->motif }}</td>
            <td>
              @if ($r->fichier_justificatif)
                <a href="{{ Storage::disk('public')->url($r->fichier_justificatif) }}" target="_blank">Voir</a>
              @else
                —
              @endif
            </td>
            <td>
              <span class="badge bg-secondary">{{ $r->statut }}</span>
              @if ($r->commentaire_admin)
                <div class="text-muted small">{{ $r->commentaire_admin }}</div>
              @endif
            </td>
            <td>
              <form action="{{ route('admin.reclamations.update', $r) }}" method="post" class="d-flex gap-2">
                @csrf
                @method('put')
                <select name="statut" class="form-select form-select-sm" style="width:auto">
                  <option value="en_attente" @selected($r->statut==='en_attente')>En attente</option>
                  <option value="approuvee" @selected($r->statut==='approuvee')>Approuvée</option>
                  <option value="rejetee" @selected($r->statut==='rejetee')>Rejetée</option>
                </select>
                <input type="text" name="commentaire_admin" class="form-control form-control-sm" maxlength="500" placeholder="Commentaire (500 max)" value="{{ $r->commentaire_admin }}" />
                <button class="btn btn-sm btn-primary">Mettre à jour</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="mt-3">{{ $reclamations->links() }}</div>
  </div>
</div>
@endsection
