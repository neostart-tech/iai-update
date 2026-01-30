@extends('base')
@section('content')
@include('documentations.modal')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Gestion des documents</h1>
            <p class="text-muted mb-0">Consultez et gérez tous les documents de l'application</p>
        </div>
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
            <i class="fas fa-plus"></i>
            Ajouter un document
        </button>
    </div>

    {{-- Statistiques --}}
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-primary mb-2">Documents totaux</h6>
                            <h3 class="fw-bold mb-0">{{ $documents->count() }}</h3>
                        </div>
                        <div class="bg-primary text-white rounded-circle p-3">
                            <i class="fas fa-file-alt fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-success mb-2">Rôles configurés</h6>
                            <h3 class="fw-bold mb-0">{{ $roles->count() }}</h3>
                        </div>
                        <div class="bg-success text-white rounded-circle p-3">
                            <i class="fas fa-user-tag fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-info mb-2">Filières actives</h6>
                            <h3 class="fw-bold mb-0">{{ $filieres->count() }}</h3>
                        </div>
                        <div class="bg-info text-white rounded-circle p-3">
                            <i class="fas fa-graduation-cap fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-warning mb-2">Groupes disponibles</h6>
                            <h3 class="fw-bold mb-0">{{ $groupes->count() }}</h3>
                        </div>
                        <div class="bg-warning text-white rounded-circle p-3">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Barre de contrôle --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="search" class="form-control border-start-0" placeholder="Rechercher un document..." id="searchInput">
                    </div>

                    <select class="form-select" style="width: auto;" id="sortSelect">
                        <option selected>Trier par</option>
                        <option value="newest">Plus récent</option>
                        <option value="oldest">Plus ancien</option>
                        <option value="name">Nom (A-Z)</option>
                    </select>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="viewMode" id="gridView" autocomplete="off" checked>
                        <label class="btn btn-outline-secondary" for="gridView" data-bs-toggle="tooltip" title="Vue grille">
                            <i class="fas fa-th-large"></i>
                        </label>

                        <input type="radio" class="btn-check" name="viewMode" id="listView" autocomplete="off">
                        <label class="btn btn-outline-secondary" for="listView" data-bs-toggle="tooltip" title="Vue liste">
                            <i class="fas fa-list"></i>
                        </label>
                    </div>

                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-filter"></i> Filtrer
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" data-filter="all">Tous les documents</a></li>
                        <li><a class="dropdown-item" href="#" data-filter="mine">Mes documents</a></li>
                        <li><a class="dropdown-item" href="#" data-filter="shared">Partagés avec moi</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#" data-filter="month">Ce mois-ci</a></li>
                        <li><a class="dropdown-item" href="#" data-filter="year">Cette année</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Vue Grille (Par défaut) --}}
    <div id="gridViewContent" class="view-content">
        <div class="row g-4">
            @forelse($documents as $document)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm h-100 document-card">
                    <div class="card-body p-3">
                        {{-- En-tête du document --}}
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="form-check">
                                <input class="form-check-input document-check" type="checkbox" value="{{ $document->id }}">
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('documentation.download', $document) }}">
                                            <i class="fas fa-download"></i> Télécharger
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#detailsModal{{ $document->id }}">
                                            <i class="fas fa-eye"></i> Voir les détails
                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item d-flex align-items-center gap-2" onclick="DocumentEdit(@json($document))">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                    </li>

                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('documentation.delete', $document) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    </li>

                                </ul>
                            </div>
                        </div>

                        {{-- Icône du document --}}
                        <div class="text-center mb-3">
                            <div class="file-icon-wrapper bg-light rounded-3 p-4 d-inline-block">
                                @php
                                $extension = strtolower(pathinfo($document->path, PATHINFO_EXTENSION));
                                @endphp
                                @if(in_array($extension, ['pdf']))
                                <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                @elseif(in_array($extension, ['doc', 'docx']))
                                <i class="fas fa-file-word fa-3x text-primary"></i>
                                @elseif(in_array($extension, ['xls', 'xlsx']))
                                <i class="fas fa-file-excel fa-3x text-success"></i>
                                @elseif(in_array($extension, ['ppt', 'pptx']))
                                <i class="fas fa-file-powerpoint fa-3x text-warning"></i>
                                @elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                                <i class="fas fa-file-image fa-3x text-info"></i>
                                @elseif(in_array($extension, ['zip', 'rar']))
                                <i class="fas fa-file-archive fa-3x text-secondary"></i>
                                @else
                                <i class="fas fa-file fa-3x text-muted"></i>
                                @endif
                            </div>
                        </div>

                        {{-- Informations du document --}}
                        <h6 class="mb-2 text-truncate" title="{{ $document->title }}">{{ $document->title }}</h6>
                        <div class="d-flex justify-content-between align-items-center text-muted small mb-3">
                            <div class="d-flex align-items-center gap-1">
                                <i class="fas fa-calendar"></i>
                                <span>{{ $document->created_at->format('d/m/Y') }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <i class="fas fa-file-alt"></i>
                                <span>
                                    @php
                                    $filePath = storage_path('app/' . $document->path);
                                    if(file_exists($filePath)) {
                                    $size = filesize($filePath);
                                    $units = ['B', 'KB', 'MB', 'GB'];
                                    $i = 0;
                                    while ($size >= 1024 && $i < count($units) - 1) {
                                        $size /=1024;
                                        $i++;
                                        }
                                        echo round($size, 2) . ' ' . $units[$i];
                                        } else {
                                        echo '0 MB' ;
                                        }
                                        @endphp
                                        </span>
                            </div>
                        </div>

                        {{-- Badges d'accès --}}
                        <div class="mb-3">
                            @php
                            $roleCount = $document->accesses->where('access_type', 'ROLE')->count();
                            $groupeCount = $document->accesses->where('access_type', 'GROUPE')->count();
                            $filiereCount = $document->accesses->where('access_type', 'FILIERE')->count();
                            $niveauCount = $document->accesses->where('access_type', 'NIVEAU')->count();
                            @endphp

                            @if($roleCount > 0)
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-1">
                                <i class="fas fa-user-tag me-1"></i>{{ $roleCount }} rôle(s)
                            </span>
                            @endif
                            @if($groupeCount > 0)
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 mb-1">
                                <i class="fas fa-users me-1"></i>{{ $groupeCount }} groupe(s)
                            </span>
                            @endif
                            @if($filiereCount > 0)
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 mb-1">
                                <i class="fas fa-graduation-cap me-1"></i>{{ $filiereCount }} filière(s)
                            </span>
                            @endif
                            @if($niveauCount > 0)
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 mb-1">
                                <i class="fas fa-layer-group me-1"></i>{{ $niveauCount }} niveau(x)
                            </span>
                            @endif

                            @if($roleCount === 0 && $groupeCount === 0 && $filiereCount === 0 && $niveauCount === 0)
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                <i class="fas fa-globe me-1"></i>Accès public
                            </span>
                            @endif
                        </div>

                        {{-- Bouton d'action principal --}}
                        <a href="{{ route('documentation.download', $document) }}"
                            class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-download"></i>
                            Télécharger
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="text-muted mb-3">
                            <i class="fas fa-folder-open fa-4x"></i>
                        </div>
                        <h5 class="mb-2">Aucun document disponible</h5>
                        <p class="text-muted mb-4">Commencez par ajouter votre premier document</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDocumentModal">
                            <i class="fas fa-plus me-2"></i>Ajouter un document
                        </button>
                    </div>
                </div>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Vue Liste --}}
    <div id="listViewContent" class="view-content d-none">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50" class="ps-4">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </th>
                                <th>Nom du document</th>
                                <th>Taille</th>
                                <th>Date d'ajout</th>
                                <th>Accès</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $document)
                            <tr>
                                <td class="ps-4">
                                    <input class="form-check-input document-check" type="checkbox" value="{{ $document->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="file-icon-sm bg-light rounded-2 p-2">
                                            @php
                                            $extension = strtolower(pathinfo($document->path, PATHINFO_EXTENSION));
                                            @endphp
                                            @if(in_array($extension, ['pdf']))
                                            <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                            @elseif(in_array($extension, ['doc', 'docx']))
                                            <i class="fas fa-file-word fa-2x text-primary"></i>
                                            @elseif(in_array($extension, ['xls', 'xlsx']))
                                            <i class="fas fa-file-excel fa-2x text-success"></i>
                                            @elseif(in_array($extension, ['ppt', 'pptx']))
                                            <i class="fas fa-file-powerpoint fa-2x text-warning"></i>
                                            @elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                                            <i class="fas fa-file-image fa-2x text-info"></i>
                                            @elseif(in_array($extension, ['zip', 'rar']))
                                            <i class="fas fa-file-archive fa-2x text-secondary"></i>
                                            @else
                                            <i class="fas fa-file fa-2x text-muted"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $document->title }}</h6>
                                            <small class="text-muted">{{ $document->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        @php
                                        $filePath = storage_path('app/' . $document->path);
                                        if(file_exists($filePath)) {
                                        $size = filesize($filePath);
                                        $units = ['B', 'KB', 'MB', 'GB'];
                                        $i = 0;
                                        while ($size >= 1024 && $i < count($units) - 1) {
                                            $size /=1024;
                                            $i++;
                                            }
                                            echo round($size, 2) . ' ' . $units[$i];
                                            } else {
                                            echo '0 MB' ;
                                            }
                                            @endphp
                                            </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span>{{ $document->created_at->format('d/m/Y') }}</span>
                                        <small class="text-muted">{{ $document->created_at->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td>
                                    @php
                                    $accessCount = $document->accesses->count();
                                    @endphp
                                    @if($accessCount > 0)
                                    <div class="d-flex gap-1 flex-wrap" style="max-width: 150px;">
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            {{ $accessCount }} accès
                                        </span>
                                    </div>
                                    @else
                                    <span class="text-muted small">Public</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('documentation.download', $document) }}"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="tooltip"
                                            title="Télécharger">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="#"
                                            class="btn btn-sm btn-outline-info"
                                            data-bs-toggle="tooltip"
                                            title="Voir les détails"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detailsModal{{ $document->id }}">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="{{ route('documentation.edit', $document) }}"
                                            class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="tooltip"
                                            title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>


                                        <form method="POST" action="{{ route('documentation.delete', $document) }}" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="tooltip"
                                                title="Supprimer"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Pagination --}}

</div>

{{-- Modals de détails pour chaque document --}}
@foreach($documents as $document)
<div class="modal fade" id="detailsModal{{ $document->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-info-circle me-2"></i>Détails du document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-4 mb-md-0">
                        <div class="file-icon-large bg-light rounded-3 p-5 d-inline-block mb-3">
                            @php
                            $extension = strtolower(pathinfo($document->path, PATHINFO_EXTENSION));
                            @endphp
                            @if(in_array($extension, ['pdf']))
                            <i class="fas fa-file-pdf fa-5x text-danger"></i>
                            @elseif(in_array($extension, ['doc', 'docx']))
                            <i class="fas fa-file-word fa-5x text-primary"></i>
                            @elseif(in_array($extension, ['xls', 'xlsx']))
                            <i class="fas fa-file-excel fa-5x text-success"></i>
                            @elseif(in_array($extension, ['ppt', 'pptx']))
                            <i class="fas fa-file-powerpoint fa-5x text-warning"></i>
                            @elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'gif']))
                            <i class="fas fa-file-image fa-5x text-info"></i>
                            @elseif(in_array($extension, ['zip', 'rar']))
                            <i class="fas fa-file-archive fa-5x text-secondary"></i>
                            @else
                            <i class="fas fa-file fa-5x text-muted"></i>
                            @endif
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-secondary">
                                {{ strtoupper($extension) }}
                                @php
                                $filePath = storage_path('app/' . $document->path);
                                if(file_exists($filePath)) {
                                $size = filesize($filePath);
                                $units = ['B', 'KB', 'MB', 'GB'];
                                $i = 0;
                                while ($size >= 1024 && $i < count($units) - 1) {
                                    $size /=1024;
                                    $i++;
                                    }
                                    echo '• ' . round($size, 2) . ' ' . $units[$i];
                                    }
                                    @endphp
                                    </span>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <h4 class="mb-3">{{ $document->title }}</h4>
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-3 text-muted mb-2">
                                <div>
                                    <i class="fas fa-calendar me-1"></i>
                                    <strong>Date d'ajout :</strong> {{ $document->created_at->format('d/m/Y à H:i') }}
                                </div>
                                <div>
                                    <i class="fas fa-history me-1"></i>
                                    <strong>Dernière modification :</strong> {{ $document->updated_at->format('d/m/Y à H:i') }}
                                </div>
                            </div>
                        </div>

                        <h6 class="mb-3">Accès autorisés :</h6>

                        @php
                        $roles = $document->accesses->where('access_type', 'ROLE');
                        $groupes = $document->accesses->where('access_type', 'GROUPE');
                        $filieres = $document->accesses->where('access_type', 'FILIERE');
                        $niveaux = $document->accesses->where('access_type', 'NIVEAU');
                        @endphp

                        @if($roles->count() > 0)
                        <div class="mb-3">
                            <label class="form-label text-primary mb-2 d-flex align-items-center gap-2">
                                <i class="fas fa-user-tag"></i>
                                Rôles autorisés ({{ $roles->count() }})
                            </label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($roles as $role)
                                <span class="badge bg-primary bg-opacity-25 text-primary p-2">
                                    <i class="fas fa-user-circle me-1"></i>{{ $role->nom }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($groupes->count() > 0)
                        <div class="mb-3">
                            <label class="form-label text-success mb-2 d-flex align-items-center gap-2">
                                <i class="fas fa-users"></i>
                                Groupes autorisés ({{ $groupes->count() }})
                            </label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($groupes as $groupe)

                                <span class="badge bg-success bg-opacity-25 text-success p-2">
                                    <i class="fas fa-layer-group me-1"></i> {{ $groupe->nom }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($filieres->count() > 0)
                        <div class="mb-3">
                            <label class="form-label text-info mb-2 d-flex align-items-center gap-2">
                                <i class="fas fa-graduation-cap"></i>
                                Filières autorisées ({{ $filieres->count() }})
                            </label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($filieres as $filiere)
                                <span class="badge bg-info bg-opacity-25 text-info p-2">
                                    <i class="fas fa-university me-1"></i>{{ $filiere->nom }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($niveaux->count() > 0)
                        <div class="mb-3">
                            <label class="form-label text-warning mb-2 d-flex align-items-center gap-2">
                                <i class="fas fa-layer-group"></i>
                                Niveaux autorisés ({{ $niveaux->count() }})
                            </label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($niveaux as $niveau)
                                <span class="badge bg-warning bg-opacity-25 text-warning p-2">
                                    <i class="fas fa-signal me-1"></i>{{ $niveau->libelle }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($document->accesses->count() === 0)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Ce document est accessible à tous les utilisateurs (accès public).
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <a href="{{ route('documentation.download', $document) }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="fas fa-download"></i>Télécharger
                </a>
                @can('update', $document)
                <a href="{{ route('documentation.edit', $document) }}" class="btn btn-outline-primary d-flex align-items-center gap-2">
                    <i class="fas fa-edit"></i>Modifier
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>
@endforeach



@push('styles')
@include('documentations.styles.style')
@endpush

@push('scripts')
<script>
 function DocumentEdit(doc) {

    // 1. Ouvrir le modal
    const modal = new bootstrap.Modal(document.getElementById('addDocumentModal'));
    modal.show();

    // 2. Changer le titre du modal
    document.querySelector('#addDocumentModal .modal-title').innerHTML =
        '<i class="fas fa-edit me-2"></i>Modifier le document';

    // 3. Changer l'action du formulaire
    const form = document.getElementById('uploadForm');
    form.action = `/documentation/${doc.id}`; // route document.update
    document.getElementById('formMethod').value = 'PUT';

    // 4. Remplir le titre
    document.getElementById('title').value = doc.title;

    // 5. Reset Select2
    $('.select2').val(null).trigger('change');

    // 6. Remplir les accès
    if (doc.accesses) {

        let roles = [];
        let groupes = [];
        let filieres = [];
        let niveaux = [];

        doc.accesses.forEach(access => {
            switch (access.access_type) {
                case 'ROLE':
                    roles.push(access.access_id);
                    break;
                case 'GROUPE':
                    groupes.push(access.access_id);
                    break;
                case 'FILIERE':
                    filieres.push(access.access_id);
                    break;
                case 'NIVEAU':
                    niveaux.push(access.access_id);
                    break;
            }
        });

        $('[name="roles[]"]').val(roles).trigger('change');
        $('[name="groupes[]"]').val(groupes).trigger('change');
        $('[name="filieres[]"]').val(filieres).trigger('change');
        $('[name="niveaux[]"]').val(niveaux).trigger('change');
    }

    // 7. Fichier non obligatoire en modification
    document.getElementById('file').removeAttribute('required');
}
</script>
<script>
    // Gestion du drag & drop
    const fileUploadArea = document.querySelector('.file-upload-area');
    const fileInput = document.getElementById('file');
    const uploadPlaceholder = document.querySelector('.file-upload-placeholder');
    const uploadPreview = document.querySelector('.file-upload-preview');
    const fileName = document.querySelector('.file-name');
    const fileSize = document.querySelector('.file-size');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        fileUploadArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
        fileUploadArea.classList.add('dragover');
    }

    function unhighlight() {
        fileUploadArea.classList.remove('dragover');
    }

    fileUploadArea.addEventListener('drop', handleDrop, false);
    fileInput.addEventListener('change', handleFileSelect, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }

    function handleFileSelect(e) {
        const files = e.target.files;
        handleFiles(files);
    }

    function handleFiles(files) {
        if (files.length > 0) {
            const file = files[0];
            if (file.size > 10 * 1024 * 1024) {
                alert('Le fichier ne doit pas dépasser 10MB');
                return;
            }

            fileName.textContent = file.name;
            fileSize.textContent = formatBytes(file.size);
            uploadPlaceholder.classList.add('d-none');
            uploadPreview.classList.remove('d-none');
        }
    }

    document.querySelector('.remove-file').addEventListener('click', function() {
        fileInput.value = '';
        uploadPlaceholder.classList.remove('d-none');
        uploadPreview.classList.add('d-none');
    });

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // Gestion du changement de vue
    document.getElementById('gridView').addEventListener('change', function() {
        document.getElementById('gridViewContent').classList.remove('d-none');
        document.getElementById('listViewContent').classList.add('d-none');
    });

    document.getElementById('listView').addEventListener('change', function() {
        document.getElementById('listViewContent').classList.remove('d-none');
        document.getElementById('gridViewContent').classList.add('d-none');
    });

    // Sélection multiple
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.document-check');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Initialisation de Select2
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            dropdownParent: $('#addDocumentModal')
        });
    });

    // Recherche en temps réel
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.document-card');

        cards.forEach(card => {
            const title = card.querySelector('h6').textContent.toLowerCase();
            if (title.includes(searchTerm) || searchTerm === '') {
                card.parentElement.style.display = '';
            } else {
                card.parentElement.style.display = 'none';
            }
        });
    });

    // Tri des documents
    document.getElementById('sortSelect').addEventListener('change', function(e) {
        const sortValue = e.target.value;
        // Implémentez ici la logique de tri selon le besoin
        console.log('Tri sélectionné :', sortValue);
    });

    // Filtrage des documents
    document.querySelectorAll('[data-filter]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.getAttribute('data-filter');
            // Implémentez ici la logique de filtrage selon le besoin
            console.log('Filtre sélectionné :', filter);
        });
    });

    // Initialisation des tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endpush

@if(!empty($documents))
@push('scripts')
<script>
    // Statistiques dynamiques
    document.addEventListener('DOMContentLoaded', function() {
        let totalSize = 0;
        let fileTypes = {};

        @foreach($documents as $document)
        @php
        $filePath = storage_path('app/'.$document->path);
        if (file_exists($filePath)) {
            $size = filesize($filePath);
            echo "totalSize += $size;";
        }
        $ext = strtolower(pathinfo($document-> path, PATHINFO_EXTENSION));
        echo "if(!fileTypes['$ext']) fileTypes['$ext'] = 0;";
        echo "fileTypes['$ext']++;";
        @endphp
        @endforeach

        // Vous pouvez utiliser ces statistiques pour enrichir l'interface
        console.log('Taille totale des documents :', formatBytes(totalSize));
        console.log('Types de fichiers :', fileTypes);
    });
</script>
@endpush
@endif

@endsection