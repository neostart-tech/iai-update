@extends('base', [
    'title' => 'Codes Anonymes - ' . $evaluation->matiere->nom,
    'page_name' => 'Codes Anonymes pour l\'évaluation',
    'breadcrumbs' => [
        'Administration',
        'Évaluations' => route('admin.evaluations.index'),
        'Codes Anonymes'
    ]
])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Codes Anonymes</h5>
                    <small class="text-muted">
                        {{ $evaluation->matiere->nom }} - {{ $evaluation->debut->format('d/m/Y H:i') }}
                    </small>
                </div>
                <div class="btn-group">
                    <a href="{{ route('admin.evaluations.anonymous.print', $evaluation) }}" 
                       class="btn btn-primary" target="_blank">
                        <i class="fas fa-print"></i> Imprimer PDF
                    </a>
                    <a href="{{ route('admin.evaluations.anonymous.export', $evaluation) }}" 
                       class="btn btn-success">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                    <a href="{{ route('admin.evaluations.show', $evaluation) }}" 
                       class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($anonymousCodesBySalle->isEmpty())
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Aucun code anonyme généré pour cette évaluation.
                    </div>
                @else
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card">
                                <strong>Total des codes :</strong> {{ $anonymousCodesBySalle->flatten()->count() }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <strong>Salles :</strong> {{ $anonymousCodesBySalle->keys()->count() }}
                            </div>
                        </div>
                    </div>

                    @foreach($anonymousCodesBySalle as $salle => $codes)
                        <div class="mt-4">
                            <h6 class="text-primary border-bottom pb-2">
                                <i class="fas fa-door-open"></i> {{ $salle }} ({{ $codes->count() }} étudiants)
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Code Anonyme</th>
                                            <th>Matricule</th>
                                            <th>Nom & Prénom</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($codes as $code)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-dark fs-6">{{ $code->anonymous_code }}</span>
                                                </td>
                                                <td>{{ $code->etudiant->id ?? 'N/A' }}</td>
                                                <td>
                                                    {{ $code->etudiant->nom ?? 'N/A' }} 
                                                    {{ $code->etudiant->prenom ?? '' }}
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="copyToClipboard('{{ $code->anonymous_code }}')">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.info-card {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}
</style>
@endpush

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Afficher une notification de succès
        Swal.fire({
            icon: 'success',
            title: 'Copié !',
            text: 'Code copié dans le presse-papier',
            timer: 1500,
            showConfirmButton: false
        });
    });
}
</script>
@endpush