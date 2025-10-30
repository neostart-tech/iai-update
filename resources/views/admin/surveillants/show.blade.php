@extends('base', [
    'title' => 'Détails Surveillant - ' . $user->nom_complet,
    'page_name' => 'Détails Surveillant',
    'breadcrumbs' => ['Administration', ['url' => route('admin.surveillants.index'), 'text' => 'Surveillants'], $user->nom_complet]
])

@section('content')
    <div class="row">
        <!-- Informations du surveillant -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ asset($user->image ?? 'images/default.png') }}" 
                         class="img-radius mb-3" width="100" height="100" alt="Avatar">
                    <h5 class="mb-2">{{ $user->nom_complet }}</h5>
                    
                    @if($user->supervisor_type === 'interne')
                        <span class="badge bg-success mb-3">
                            <i class="ti ti-shield-check"></i> Surveillant Interne
                        </span>
                    @elseif($user->supervisor_type === 'externe')
                        <span class="badge bg-warning text-dark mb-3">
                            <i class="ti ti-shield-half"></i> Surveillant Externe
                        </span>
                    @endif

                    <div class="row text-center mt-3">
                        <div class="col-6">
                            <h6 class="mb-0">{{ $evaluations->total() }}</h6>
                            <small class="text-muted">Surveillances</small>
                        </div>
                        <div class="col-6">
                            <h6 class="mb-0">{{ $user->roles->count() }}</h6>
                            <small class="text-muted">Rôles</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations de contact -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations de Contact</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Email :</strong><br>
                        <span class="text-muted">{{ $user->email }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Téléphone :</strong><br>
                        <span class="text-muted">{{ $user->tel }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Genre :</strong><br>
                        <span class="text-muted">{{ $user->genre }}</span>
                    </div>
                    <div class="mb-0">
                        <strong>Rôles :</strong><br>
                        @foreach($user->roles as $role)
                            <span class="badge bg-primary me-1">{{ $role->nom }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Notes de surveillance -->
            @if($user->supervisor_notes)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Notes de Surveillance</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $user->supervisor_notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Historique des surveillances -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Historique des Surveillances</h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary dropdown-toggle btn-sm" 
                                type="button" data-bs-toggle="dropdown">
                            Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.users.edit', $user) }}">
                                    <i class="ti ti-edit"></i> Modifier Utilisateur
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            @if($user->supervisor_type !== 'interne')
                                <li>
                                    <form method="POST" action="{{ route('admin.surveillants.make-interne', $user) }}" class="d-inline">
                                        @csrf
                                        <button class="dropdown-item text-success">
                                            <i class="ti ti-shield-check"></i> Marquer Interne
                                        </button>
                                    </form>
                                </li>
                            @endif
                            @if($user->supervisor_type !== 'externe')
                                <li>
                                    <form method="POST" action="{{ route('admin.surveillants.make-externe', $user) }}" class="d-inline">
                                        @csrf
                                        <button class="dropdown-item text-warning">
                                            <i class="ti ti-shield-half"></i> Marquer Externe
                                        </button>
                                    </form>
                                </li>
                            @endif
                            <li>
                                <form method="POST" action="{{ route('admin.surveillants.remove', $user) }}" 
                                      class="d-inline"
                                      onsubmit="return confirm('Retirer {{ $user->nom_complet }} de la surveillance ?')">
                                    @csrf
                                    <button class="dropdown-item text-danger">
                                        <i class="ti ti-shield-x"></i> Retirer Surveillance
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    @if($evaluations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Horaire</th>
                                        <th>Salle</th>
                                        <th>Détails</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($evaluations as $emploi)
                                        <tr>
                                            <td>
                                                <strong>{{ $emploi->debut->translatedFormat('d M Y') }}</strong>
                                            </td>
                                            <td>
                                                {{ $emploi->debut->format('H:i') }} - 
                                                {{ $emploi->fin->format('H:i') }}
                                                <br>
                                                <small class="text-muted">
                                                    {{ $emploi->debut->diffInHours($emploi->fin) }}h
                                                </small>
                                            </td>
                                            <td>
                                                @if($emploi->salle)
                                                    <span class="badge bg-info">{{ $emploi->salle->nom }}</span>
                                                @else
                                                    <span class="text-muted">Non spécifié</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $emploi->details }}</span>
                                            </td>
                                            <td>
                                                @if($user->supervisor_type === 'interne')
                                                    <span class="badge bg-success">Interne</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Externe</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $evaluations->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-calendar-off f-60 text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune surveillance enregistrée</h5>
                            <p class="text-muted">Ce surveillant n'a pas encore été affecté à des évaluations.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de modification rapide du type -->
    <div class="modal fade" id="editTypeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.surveillants.update-type', $user) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier Type de Surveillant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="form-label">Type de Surveillant</label>
                            <select class="form-select" name="supervisor_type" required>
                                <option value="interne" @selected($user->supervisor_type === 'interne')>
                                    Surveillant Interne
                                </option>
                                <option value="externe" @selected($user->supervisor_type === 'externe')>
                                    Surveillant Externe
                                </option>
                                <option value="non_surveillant" @selected($user->supervisor_type === 'non_surveillant')>
                                    Non Surveillant
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Notes de Surveillance</label>
                            <textarea class="form-control" name="supervisor_notes" rows="3" 
                                      placeholder="Notes concernant les aptitudes de surveillance, disponibilités, etc.">{{ $user->supervisor_notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Sauvegarder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection