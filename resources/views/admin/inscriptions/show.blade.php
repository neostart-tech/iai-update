@extends('base', $metaData)

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Détail de l'inscription</h5>
        <a href="{{ route('admin.inscriptions.index') }}" class="btn btn-sm btn-outline-secondary">Retour à la liste</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="mb-2"><strong>Numéro de table:</strong> {{ $inscription->numero_table }}</div>
                <div class="mb-2"><strong>Année BAC:</strong> {{ $inscription->annee_bac }}</div>
                <div class="mb-2"><strong>Série:</strong> {{ $inscription->serie }}</div>
                <div class="mb-2"><strong>Acceptation:</strong> {{ $inscription->accepte }}</div>
                <div class="mb-2"><strong>Statut:</strong> <span class="badge bg-{{ $inscription->status === 'pending' ? 'warning' : 'success' }}">{{ ucfirst($inscription->status) }}</span></div>
                <div class="mb-2 text-muted"><strong>Soumis le:</strong> {{ $inscription->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="col-md-6">
                <div class="mb-2"><strong>Tuteur (Lieu):</strong> {{ $inscription->tuteur_lieu }}</div>
                <div class="mb-2"><strong>Téléphones:</strong>
                    <div>{{ $inscription->phone1 }}</div>
                    <div>{{ $inscription->phone2 }}</div>
                    @if($inscription->phone3)
                        <div>{{ $inscription->phone3 }}</div>
                    @endif
                </div>
                @if($inscription->email)
                    <div class="mb-2"><strong>Email:</strong> {{ $inscription->email }}</div>
                @endif
            </div>
        </div>

        <hr>

        <div class="mb-3">
            <strong>Lettre de motivation</strong>
            <div class="border rounded p-2 bg-light" style="white-space: pre-wrap">{{ $inscription->lettre_motivation }}</div>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header">Relevé BAC 1</div>
                    <div class="card-body">
                        @if($inscription->releve_bac1_path)
                            <a class="btn btn-sm btn-primary" target="_blank" href="{{ Storage::url($inscription->releve_bac1_path) }}">Télécharger</a>
                        @else
                            <span class="text-muted">Aucun</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header">Relevé BAC 2</div>
                    <div class="card-body">
                        @if($inscription->releve_bac2_path)
                            <a class="btn btn-sm btn-primary" target="_blank" href="{{ Storage::url($inscription->releve_bac2_path) }}">Télécharger</a>
                        @else
                            <span class="text-muted">Aucun</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header">Certificat médical</div>
                    <div class="card-body">
                        @if($inscription->certificat_medical_path)
                            <a class="btn btn-sm btn-secondary" target="_blank" href="{{ Storage::url($inscription->certificat_medical_path) }}">Télécharger</a>
                        @else
                            <span class="text-muted">Aucun</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Bulletins du lycée</span>
                    <span class="badge bg-info">{{ is_array($inscription->bulletins_lycee_paths) ? count($inscription->bulletins_lycee_paths) : 0 }} fichier(s)</span>
                </div>
                <div class="card-body">
                    @php
                        $bulletins = is_array($inscription->bulletins_lycee_paths) ? $inscription->bulletins_lycee_paths : [];
                        $byLevel = [
                            'seconde' => [],
                            'premiere' => [],
                            'terminale' => [],
                            'autres' => [],
                        ];
                        foreach ($bulletins as $p) {
                            $pp = strtolower($p);
                            if (\Illuminate\Support\Str::contains($pp, '/seconde/')) { $byLevel['seconde'][] = $p; }
                            elseif (\Illuminate\Support\Str::contains($pp, '/premiere/')) { $byLevel['premiere'][] = $p; }
                            elseif (\Illuminate\Support\Str::contains($pp, '/terminale/')) { $byLevel['terminale'][] = $p; }
                            else { $byLevel['autres'][] = $p; }
                        }
                    @endphp

                    @if(count($bulletins))
                        <div class="row g-3">
                            @foreach(['seconde' => 'Seconde', 'premiere' => 'Première', 'terminale' => 'Terminale', 'autres' => 'Autres'] as $key => $label)
                                <div class="col-md-4">
                                    <div class="border rounded h-100 p-2">
                                        <div class="fw-semibold mb-2">{{ $label }}</div>
                                        @if(count($byLevel[$key]))
                                            <div class="row g-2">
                                                @foreach($byLevel[$key] as $i => $path)
                                                    @php $url = Storage::url($path); @endphp
                                                    <div class="col-6">
                                                        <div class="border rounded p-2 h-100 d-flex flex-column">
                                                            <div class="small mb-2">Bulletin {{ $i+1 }}</div>
                                                            @if(\Illuminate\Support\Str::endsWith(strtolower($path), ['.jpg','.jpeg','.png','.webp']))
                                                                <a href="{{ $url }}" target="_blank">
                                                                    <img src="{{ $url }}" alt="Bulletin {{ $i+1 }}" class="img-fluid rounded" />
                                                                </a>
                                                            @else
                                                                <div class="flex-fill d-flex align-items-center justify-content-center text-muted" style="min-height:80px;">
                                                                    <i class="bi bi-file-earmark-pdf" style="font-size:24px"></i>
                                                                </div>
                                                                <a class="btn btn-sm btn-outline-success mt-2" href="{{ $url }}" target="_blank">Ouvrir</a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">Aucun bulletin</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted">Aucun bulletin</span>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
