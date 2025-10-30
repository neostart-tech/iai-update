@extends('base', [
    'title' => 'Comité de classe',
    'page_name' => 'Gestion du comité de classe',
    'breadcrumbs' => ['Administration', 'Comité de classe']
])

@section('content')
<div class="card">
    <div class="card-header">
        <form method="get" class="row g-2">
            <div class="col-md-6">
                <label class="form-label">Groupe</label>
                <select name="group_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Choisir un groupe --</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" @selected(optional($selectedGroup)->id===$g->id)>{{ $g->nom }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    <div class="card-body">
        @if($selectedGroup)
            <div class="row">
                <div class="col-md-6">
                    <h6>Membres actuels</h6>
                    <table class="table table-sm">
                        <thead><tr><th>Rôle</th><th>Étudiant</th><th class="text-end">Action</th></tr></thead>
                        <tbody>
                            @forelse($members as $m)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_',' ', $m->role)) }}</td>
                                    <td>
                                        @php $etu = $selectedGroup->etudiants->firstWhere('id', $m->etudiant_id); @endphp
                                        {{ $etu?->completName() ?? ('#'.$m->etudiant_id) }}
                                    </td>
                                    <td class="text-end">
                                        <form method="post" action="{{ route('admin.committee.delete') }}" onsubmit="return confirm('Retirer ce membre ?')">
                                            @csrf @method('delete')
                                            <input type="hidden" name="id" value="{{ $m->id }}">
                                            <button class="btn btn-sm btn-outline-danger">Retirer</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">Aucun membre</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Assigner un rôle</h6>
                    <form method="post" action="{{ route('admin.committee.store') }}" class="row g-2">
                        @csrf
                        <input type="hidden" name="group_id" value="{{ $selectedGroup->id }}">
                        <div class="col-12">
                            <label class="form-label">Étudiant</label>
                            <select name="etudiant_id" class="form-select" required>
                                <option value="">-- Choisir --</option>
                                @foreach($selectedGroup->etudiants as $etu)
                                    <option value="{{ $etu->id }}">{{ $etu->completName() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Rôle</label>
                            <select name="role" class="form-select" required>
                                <option value="delegue">Délégué</option>
                                <option value="delegue_adjoint">Délégué adjoint</option>
                                <option value="secretaire">Secrétaire</option>
                                <option value="secretaire_adjoint">Secrétaire adjoint</option>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <button class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="text-muted">Choisissez un groupe pour gérer son comité.</div>
        @endif
    </div>
</div>
@endsection
