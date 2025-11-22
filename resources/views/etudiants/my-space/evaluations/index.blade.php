@extends('base', [
    'title' => 'Liste des Évaluations',
    'breadcrumbs' => ['Administration', 'Évaluations', 'Liste'],
    'page_name' => 'Liste des Évaluations',
])
@section('content')
    <div class="card">
        <div class="card-body">
            @if (!empty($evaluations))
                <div class="dt-responsive table-responsive">
                    <table id="dom-jquery" class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Matière</th>
                                <th>Jour</th>
                                <th>Début</th>
                                <th>Fin</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($evaluations as $key => $evaluation)

                                @php

                                    $now = now();
                                    $dateDebut = Carbon\Carbon::parse($evaluation->debut);
                                    $dateFin   = Carbon\Carbon::parse($evaluation->fin);
                                @endphp

                                <tr class="{{ $dateFin->isBefore($now) ? 'table-secondary' : '' }}">
                                    <th>{{ $key + 1 }}</th>
                                    <td>{{ $evaluation->type }}</td>
                                    <td>{{ $evaluation->matiere->nom }}</td>
                                    <td>{{ Carbon\Carbon::parse($evaluation->date)->format('d/m/Y') }}</td>
                                    <td>{{ Carbon\Carbon::parse($evaluation->debut)->format('H:i') }}</td>
                                    <td>{{ Carbon\Carbon::parse($evaluation->fin)->format('H:i') }}</td>

                                    <td class="text-center">

                
                                        @if ($now->lt($dateDebut))
                                            <a href="{{ route('etudiants.evaluation.start-view', $evaluation->id) }}" class="btn btn-outline-secondary btn-sm" disabled>
                                                <i class="fa fa-clock"></i> À venir
                                            </a>
                            
                                        @elseif ($now->between($dateDebut, $dateFin))
                                            <a href="{{ route('etudiants.evaluation.start-view', $evaluation->id) }}"
                                               class="btn btn-success btn-sm">
                                                <i class="fa fa-play"></i> Commencer
                                            </a>
                                        @else
                                            <a  href="{{ route('etudiants.evaluation.start-view', $evaluation->id) }}" class="btn btn-primary btn-sm" disabled>
                                                <i class="fa fa-check"></i> Terminé
                                            </a>
                                        @endif

                                    </td>
                                </tr>

                            @endforeach
                        </tbody>

                    </table>
                </div>
            @else
                <x-empty-table />
            @endif
        </div>
    </div>
@endsection


@section('other-css')
<link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection

@section('other-js')
@endsection
