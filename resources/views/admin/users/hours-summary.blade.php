@extends('base', ['title' => 'Récapitulatif heures enseignants', 'page_name' => 'Heures par enseignant'])

@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="get" class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Date début</label>
                        <input type="date" name="date_debut" value="{{ $date_debut }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date fin</label>
                        <input type="date" name="date_fin" value="{{ $date_fin }}" class="form-control">
                    </div>
                    <div class="col-md-3 align-self-end">
                        <button class="btn btn-primary">Filtrer</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Enseignant</th>
                                <th>Heures totales</th>
                                <th>Secondes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($summary as $row)
                                <tr>
                                    <td>{{ $row['user']->nom }} {{ $row['user']->prenom }}</td>
                                    <td>{{ $row['hours'] }}</td>
                                    <td>{{ $row['seconds'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection
