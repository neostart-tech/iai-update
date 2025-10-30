@extends('base', [
    'title' => 'Gestion des Surveillants',
    'page_name' => 'Gestion des Surveillants',
    'breadcrumbs' => ['Administration', 'Gestion des Surveillants']
])

@section('content')
    <div class="row">
        <!-- Statistiques -->
        <div class="col-12 mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="ti ti-shield-check f-40"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0">{{ $surveillantsInternes }}</h3>
                                    <p class="mb-0">Surveillants Internes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="ti ti-shield-half f-40"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0">{{ $surveillantsExternes }}</h3>
                                    <p class="mb-0">Surveillants Externes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="ti ti-shield f-40"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0">{{ $surveillants->count() }}</h3>
                                    <p class="mb-0">Total Surveillants</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Actions Rapides</h5>
                            <p class="text-muted mb-0">Gérer les types de surveillants et leurs affectations</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary me-2">
                                <i class="ti ti-users"></i> Tous les Utilisateurs
                            </a>
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus"></i> Ajouter un Utilisateur
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des surveillants -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Liste des Surveillants</h5>
                </div>
                <div class="card-body">
                    @if($surveillants->isNotEmpty())
                        <div class="dt-responsive table-responsive">
                            <table id="surveillants-table" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nom Complet</th>
                                        <th>Type</th>
                                        <th>Rôles</th>
                                        <th>Téléphone</th>
                                        <th>Notes</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($surveillants as $key => $surveillant)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <img src="{{ asset($surveillant->image ?? 'images/default.png') }}" 
                                                             class="img-radius wid-40 hei-40" alt="User image">
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-0">{{ $surveillant->nom_complet }}</h6>
                                                        <small class="text-muted">{{ $surveillant->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($surveillant->supervisor_type === 'interne')
                                                    <span class="badge bg-success">
                                                        <i class="ti ti-shield-check"></i> Interne
                                                    </span>
                                                @elseif($surveillant->supervisor_type === 'externe')
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="ti ti-shield-half"></i> Externe
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @foreach($surveillant->roles as $role)
                                                    <span class="badge bg-primary me-1">{{ $role->nom }}</span>
                                                @endforeach
                                            </td>
                                            <td>{{ $surveillant->tel }}</td>
                                            <td>
                                                @if($surveillant->supervisor_notes)
                                                    <span class="text-truncate" style="max-width: 150px;" 
                                                          title="{{ $surveillant->supervisor_notes }}">
                                                        {{ Str::limit($surveillant->supervisor_notes, 50) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Aucune note</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                            type="button" data-bs-toggle="dropdown">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" 
                                                               href="{{ route('admin.surveillants.show', $surveillant) }}">
                                                                <i class="ti ti-eye"></i> Voir Détails
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" 
                                                               href="{{ route('admin.users.edit', $surveillant) }}">
                                                                <i class="ti ti-edit"></i> Modifier
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        @if($surveillant->supervisor_type !== 'interne')
                                                            <li>
                                                                <form method="POST" 
                                                                      action="{{ route('admin.surveillants.make-interne', $surveillant) }}" 
                                                                      class="d-inline">
                                                                    @csrf
                                                                    <button class="dropdown-item text-success">
                                                                        <i class="ti ti-shield-check"></i> Marquer Interne
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                        @if($surveillant->supervisor_type !== 'externe')
                                                            <li>
                                                                <form method="POST" 
                                                                      action="{{ route('admin.surveillants.make-externe', $surveillant) }}" 
                                                                      class="d-inline">
                                                                    @csrf
                                                                    <button class="dropdown-item text-warning">
                                                                        <i class="ti ti-shield-half"></i> Marquer Externe
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <form method="POST" 
                                                                  action="{{ route('admin.surveillants.remove', $surveillant) }}" 
                                                                  class="d-inline"
                                                                  onsubmit="return confirm('Retirer {{ $surveillant->nom_complet }} de la surveillance ?')">
                                                                @csrf
                                                                <button class="dropdown-item text-danger">
                                                                    <i class="ti ti-shield-x"></i> Retirer Surveillance
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-shield-off f-60 text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun surveillant configuré</h5>
                            <p class="text-muted">
                                Les utilisateurs peuvent être configurés comme surveillants lors de leur création ou modification.
                            </p>
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                <i class="ti ti-plus"></i> Ajouter un Utilisateur
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('other-js')
    @if($surveillants->isNotEmpty())
        <script>
            // Initialisation de la datatable
            $('#surveillants-table').DataTable({
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
                },
                columnDefs: [
                    { orderable: false, targets: [5, 6] }
                ],
                order: [[1, 'asc']]
            });
        </script>
    @endif
@endsection