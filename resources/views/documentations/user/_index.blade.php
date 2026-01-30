@extends('base')
@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Mes documents</h1>
            <p class="text-muted mb-0">Consultez les documents auxquels vous avez accès</p>
        </div>
        {{-- Pas de bouton ajouter ici si c'est un utilisateur --}}
    </div>

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

                    <select class="form-select" style="width:auto;" id="accessFilter">
                        <option value="">Tous les documents</option>
                        <option value="public">Publics</option>
                        <option value="private">Privés</option>
                    </select>
                </div>

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
            </div>
        </div>
    </div>

    <div id="documentsContent">

        {{-- Grille --}}
        <div id="gridViewContent" class="view-content">
            <div class="row g-4">
                @forelse($documents as $document)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100 document-card">
                        <div class="card-body p-3 text-center">
                            @php $ext = strtolower(pathinfo($document->path, PATHINFO_EXTENSION)); @endphp
                            <div class="file-icon-wrapper bg-light rounded-3 p-4 d-inline-block mb-3">
                                @if(in_array($ext,['pdf'])) <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                @elseif(in_array($ext,['doc','docx'])) <i class="fas fa-file-word fa-3x text-primary"></i>
                                @elseif(in_array($ext,['xls','xlsx'])) <i class="fas fa-file-excel fa-3x text-success"></i>
                                @elseif(in_array($ext,['ppt','pptx'])) <i class="fas fa-file-powerpoint fa-3x text-warning"></i>
                                @elseif(in_array($ext,['jpg','jpeg','png','gif'])) <i class="fas fa-file-image fa-3x text-info"></i>
                                @elseif(in_array($ext,['zip','rar'])) <i class="fas fa-file-archive fa-3x text-secondary"></i>
                                @else <i class="fas fa-file fa-3x text-muted"></i>
                                @endif
                            </div>

                            <h6 class="mb-2 text-truncate" title="{{ $document->title }}">{{ $document->title }}</h6>
                            <div class="mb-3 small text-muted">
                                <div><i class="fas fa-calendar"></i> {{ $document->created_at->format('d/m/Y') }}</div>
                                <div><i class="fas fa-file-alt"></i>
                                    @php
                                    $filePath = storage_path('app/' . $document->path);
                                    if(file_exists($filePath)) {
                                    $size = filesize($filePath);
                                    $units = ['B','KB','MB','GB']; $i=0;
                                    while($size>=1024 && $i<count($units)-1){ $size/=1024; $i++; }
                                        echo round($size,2).' '.$units[$i];
                                    } else { echo ' 0 MB'; }
                                        @endphp
                                        </div>
                                </div>

                                <a href="{{ route('documentation.download', $document) }}" class="btn btn-primary w-100">
                                    <i class="fas fa-download me-1"></i> Télécharger
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                                <h5>Aucun document disponible</h5>
                            </div>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Liste --}}
            <div id="listViewContent" class="view-content d-none">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom du document</th>
                                        <th>Taille</th>
                                        <th>Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $document)
                                    <tr>
                                        <td>{{ $document->title }}</td>
                                        <td>
                                            @php
                                            $filePath = storage_path('app/' . $document->path);
                                            if(file_exists($filePath)) {
                                            $size = filesize($filePath);
                                            $units = ['B','KB','MB','GB']; $i=0;
                                            while($size>=1024 && $i<count($units)-1){ $size/=1024; $i++; }
                                                echo round($size,2).' '.$units[$i];
                                        } else { echo ' 0 MB'; }
                                                @endphp
                                                </td>
                                        <td>{{ $document->created_at->format('d/m/Y') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('documentation.download', $document) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    {{ $documents->links() }}
                </div>
            </div>
        </div>


        @section('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const gridView = document.getElementById('gridView');
                const listView = document.getElementById('listView');
                const gridViewContent = document.getElementById('gridViewContent');
                const listViewContent = document.getElementById('listViewContent');
                const searchInput = document.getElementById('searchInput');
                const accessFilter = document.getElementById('accessFilter');

                gridView.addEventListener('click', () => {
                    gridViewContent.classList.remove('d-none');
                    listViewContent.classList.add('d-none');
                });
                listView.addEventListener('click', () => {
                    gridViewContent.classList.add('d-none');
                    listViewContent.classList.remove('d-none');
                });

                function fetchDocuments() {
                    const search = searchInput.value;
                    const access = accessFilter.value;
                    const url = `{{ route('documentation.mes-documents') }}?search=${search}&access=${access}`;
                    window.location.href = url; // simple reload avec filtre
                }

                searchInput.addEventListener('keyup', () => {
                    setTimeout(fetchDocuments, 300);
                });
                accessFilter.addEventListener('change', fetchDocuments);
            });
        </script>
        @endsection

        @endsection