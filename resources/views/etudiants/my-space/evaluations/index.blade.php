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
                                <th scope="col">#</th>
                                <th scope="col">Type</th>
                                <th scope="col">Matière</th>
                                <th scope="col">Jour</th>
                                 <th scope="col">Début</th>
                                <th scope="col">Fin</th>
                                <th scope="col" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($evaluations as $key => $evaluation)
                                <tr
                                    class="{{ \Carbon\Carbon::parse($evaluation['fin'])->isBefore(now()) ? 'table-secondary' : '' }}">
                                    <th scope="row">{{ $key + 1 }}</th>
                                    <td>{{ $evaluation['type'] }}</td>
                                    <td>{{ $evaluation['matiere']['nom'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($evaluation['date'])->format('d/m/Y') }}</td>
                                    <td>{{ $evaluation->debut->format("h:i:s")}}</td>
                                    <td>{{ $evaluation->fin->format("h:i:s")}}</td>
                                    <td class="text-center">
                                        <ul class="list-inline me-auto mb-0">
                                            <li class="list-inline-item align-bottom">
                                                <a href="{{ route('etudiants.evaluation.start-view',$evaluation->id) }}" class="btn btn-success">
                                                    <i class="fa fa-play"></i>
                                                </a>
                                            </li>
                                        </ul>
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

@section('other-js')
    <script>
        // Si tu veux ajouter un peu de JS supplémentaire pour tes boutons, par exemple.
    </script>
@endsection

@section('other-css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection
