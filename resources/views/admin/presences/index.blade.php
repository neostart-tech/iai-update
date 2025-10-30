@extends('base', [
    'title' => 'Validation des absences',
    'page_name' => 'Absences à valider',
    'breadcrumbs' => ['Administration', 'Présences', 'Validation']
])

@section('content')
<div class="card">
    <div class="card-header">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Recherche</label>
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="nom/prénom/commentaire">
            </div>
            <div class="col-md-3">
                <label class="form-label">Groupe</label>
                <select name="group_id" class="form-select">
                    <option value="">Tous</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" @selected(request('group_id')==$g->id)>{{ $g->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date cours</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Matière</label>
                <select name="uv_id" class="form-select">
                    <option value="">Toutes</option>
                    @foreach($uvs as $u)
                        <option value="{{ $u->id }}" @selected(request('uv_id')==$u->id)>{{ $u->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 text-end">
                <button class="btn btn-outline-secondary w-100">Filtrer</button>
            </div>
        </form>
        <div class="mt-2 text-end">
            <form method="post" action="{{ route('admin.presences.validate-batch') }}" class="d-inline">
                @csrf
                <button class="btn btn-success" onclick="return confirm('Valider les absences sélectionnées ?')">Valider la sélection</button>
                <input type="hidden" name="ids" id="batch-ids">
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th style="width:32px;"><input type="checkbox" id="check-all"></th>
                        <th>Étudiant</th>
                        <th>Cours</th>
                        <th>Commentaire</th>
                        <th>Créé le</th>
                        <th style="width: 140px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($presences as $p)
                    <tr>
                        <td><input type="checkbox" class="row-check" value="{{ $p->id }}"></td>
                        <td>{{ $p->etudiant?->completName() }}</td>
                        <td>
                            <div><strong>{{ $p->cours?->uv?->nom ?? $p->cours?->titre }}</strong></div>
                            <div class="text-muted small">Groupe: {{ $p->cours?->group?->nom ?? '-' }} | Date: {{ $p->cours?->date_cours }}</div>
                        </td>
                        <td>{{ $p->commentaire }}</td>
                        <td>{{ $p->created_at }}</td>
                        <td>
                            <form method="post" action="{{ route('admin.presences.validate-one', $p) }}" onsubmit="return confirm('Valider cette absence ?')">
                                @csrf
                                <button class="btn btn-sm btn-primary">Valider</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">Aucune absence en attente</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-2">{{ $presences->links() }}</div>
    </div>
</div>

<script>
    const checkAll = document.getElementById('check-all');
    const batchIds = document.getElementById('batch-ids');
    checkAll?.addEventListener('change',()=>{
        document.querySelectorAll('.row-check').forEach(cb=>{ cb.checked = checkAll.checked; });
        batchIds.value = Array.from(document.querySelectorAll('.row-check:checked')).map(cb=>cb.value);
    });
    document.querySelectorAll('.row-check').forEach(cb=>cb.addEventListener('change',()=>{
        batchIds.value = Array.from(document.querySelectorAll('.row-check:checked')).map(cb=>cb.value);
    }));
</script>
@endsection
