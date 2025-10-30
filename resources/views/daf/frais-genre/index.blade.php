@extends('base', [
    'title' => 'Configuration des frais par genre - DAF',
    'breadcrumbs' => ['DAF', 'Paramétrage', 'Frais par genre'],
    'page_name' => 'Configuration des frais différenciés par genre',
])

@section('content')
<div class="row">
    <!-- Statistiques -->
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">👨 Frais Hommes</h5>
                        <h2>{{ $statistiques['frais_hommes'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-pink text-white">
                    <div class="card-body">
                        <h5 class="card-title">👩 Frais Femmes</h5>
                        <h2>{{ $statistiques['frais_femmes'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body">
                        <h5 class="card-title">👥 Frais Mixtes</h5>
                        <h2>{{ $statistiques['frais_mixtes'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">📊 Total</h5>
                        <h2>{{ $statistiques['total_frais'] }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de configuration -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="ti ti-settings"></i> Nouvelle Configuration</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('daf.frais-genre.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Niveau <span class="text-danger">*</span></label>
                        <select name="niveau_id" class="form-control" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($niveaux as $niveau)
                                <option value="{{ $niveau->id }}">{{ $niveau->libelle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="alert alert-info">
                        <small><i class="ti ti-info-circle"></i> Laissez vide les montants que vous ne voulez pas configurer</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">💰 Montant pour les Hommes</label>
                        <input type="number" name="montant_hommes" class="form-control" placeholder="Ex: 150000">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">💰 Montant pour les Femmes</label>
                        <input type="number" name="montant_femmes" class="form-control" placeholder="Ex: 120000">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">💰 Montant Mixte (Tous)</label>
                        <input type="number" name="montant_mixte" class="form-control" placeholder="Ex: 130000">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">📝 Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Ex: Frais de scolarité avec différenciation par genre"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-check"></i> Configurer les Frais
                    </button>
                </form>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="card mt-3">
            <div class="card-header">
                <h5><i class="ti ti-bolt"></i> Actions Rapides</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('daf.frais-genre.rapport') }}" class="btn btn-outline-info w-100 mb-2">
                    <i class="ti ti-chart-bar"></i> Voir le Rapport
                </a>
                <a href="{{ route('comptable.frais.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="ti ti-list"></i> Gestion Classique
                </a>
            </div>
        </div>
    </div>

    <!-- Liste des configurations actuelles -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="ti ti-list"></i> Configurations Actuelles</h5>
                <span class="badge bg-primary">{{ $frais->total() }} configurations</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Année</th>
                                <th>Niveau</th>
                                <th>Genre</th>
                                <th>Montant</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($frais as $f)
                                <tr>
                                    <td>{{ $f->anneeScolaire->nom ?? 'N/A' }}</td>
                                    <td>{{ $f->niveau->libelle ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $f->genre == 'Masculin' ? 'bg-primary' : ($f->genre == 'Féminin' ? 'bg-pink' : 'bg-secondary') }}">
                                            {{ $f->genre == 'Masculin' ? '👨 Hommes' : ($f->genre == 'Féminin' ? '👩 Femmes' : '👥 Tous') }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ number_format($f->montant, 0, ',', ' ') }} F</strong>
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($f->description ?? 'Aucune description', 30) }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-warning" onclick="editFrais({{ $f->id }})">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteFrais({{ $f->id }})">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ti ti-inbox" style="font-size: 48px;"></i>
                                            <p class="mt-2">Aucune configuration trouvée</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($frais->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $frais->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('other-js')
<script>
    function editFrais(id) {
        // TODO: Implémenter l'édition
        Swal.fire('Info', 'Fonctionnalité d\'édition à implémenter', 'info');
    }
    
    function deleteFrais(id) {
        Swal.fire({
            title: 'Confirmer la suppression',
            text: 'Cette action est irréversible',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                // TODO: Implémenter la suppression
                Swal.fire('Info', 'Fonctionnalité de suppression à implémenter', 'info');
            }
        });
    }
    
    // Notification de succès si présente
    @if(session('success'))
        Swal.fire('Succès', '{{ session('success') }}', 'success');
    @endif
    
    @if(session('error'))
        Swal.fire('Erreur', '{{ session('error') }}', 'error');
    @endif
</script>
@endsection