@extends('base', $metaData)

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Liste des inscriptions</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-2 mb-3">
                <div class="col-md-2">
                    <label class="form-label small">Série</label>
                    <select name="serie" class="form-select">
                        <option value="">Toutes</option>
                        @foreach(['C','D'] as $s)
                            <option value="{{ $s }}" {{ ($filters['serie'] ?? '')===$s ? 'selected':'' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Année BAC</label>
                    <input type="number" name="annee_bac" class="form-control" value="{{ $filters['annee_bac'] ?? '' }}" placeholder="2024">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        @foreach(['pending','approved','rejected'] as $st)
                            <option value="{{ $st }}" {{ ($filters['status'] ?? '')===$st ? 'selected':'' }}>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Recherche</label>
                    <input type="text" name="q" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="N° table, téléphone, email">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button class="btn btn-primary w-100" type="submit">Filtrer</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.inscriptions.index') }}">Réinitialiser</a>
                </div>
            </form>

            <div class="table-responsive">
            <table class="table table-striped" id="dt-inscriptions">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Numéro table</th>
                    <th>Année BAC</th>
                    <th>Série</th>
                    <th>Tuteur (Lieu)</th>
                    <th>Téléphones</th>
                    <th>Docs</th>
                    <th>Statut</th>
                    <th>Soumis le</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($inscriptions as $item)
                    <tr>
                        <td>{{ $loop->iteration + ($inscriptions->currentPage()-1)*$inscriptions->perPage() }}</td>
                        <td>{{ $item->numero_table }}</td>
                        <td>{{ $item->annee_bac }}</td>
                        <td>{{ $item->serie }}</td>
                        <td>{{ $item->tuteur_lieu }}</td>
                        <td>
                            <div>{{ $item->phone1 }}</div>
                            <div>{{ $item->phone2 }}</div>
                            @if($item->phone3)
                                <div>{{ $item->phone3 }}</div>
                            @endif
                            @if($item->email)
                                <div class="text-muted small">{{ $item->email }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                @if($item->releve_bac1_path)
                                    <a href="{{ Storage::url($item->releve_bac1_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">BAC 1</a>
                                @endif
                                @if($item->releve_bac2_path)
                                    <a href="{{ Storage::url($item->releve_bac2_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">BAC 2</a>
                                @endif
                                @if($item->certificat_medical_path)
                                    <a href="{{ Storage::url($item->certificat_medical_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Certificat médical</a>
                                @endif
                                @if(is_array($item->bulletins_lycee_paths))
                                    @foreach($item->bulletins_lycee_paths as $i => $path)
                                        <a href="{{ Storage::url($path) }}" target="_blank" class="btn btn-sm btn-outline-success">Bulletin {{ $i+1 }}</a>
                                    @endforeach
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $item->status === 'pending' ? 'warning' : 'success' }}">{{ ucfirst($item->status) }}</span>
                        </td>
                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a class="btn btn-sm btn-outline-dark" href="{{ route('admin.inscriptions.show', $item) }}">Détails</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $inscriptions->links() }}
            </div>
            </div>
        </div>
    </div>
@endsection

@section('other-css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection

@section('other-js')
    @include('layouts.admin._dt-scripts')
    <script>
        $(function () {
            $('#dt-inscriptions').DataTable({
                pageLength: 20,
                order: [[0, 'asc']],
                language: {
                    url: '{{ asset('admin/assets/js/plugins/i18n/fr_fr.json') }}'
                }
            });
        })
    </script>
@endsection
