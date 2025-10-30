@php use Illuminate\Database\Eloquent\Casts\Json; @endphp
@extends('base', [
    'title' => 'Liste des Unités de valeur',
    'breadcrumbs' => ['Administration', 'Unités de valeur', 'Liste'],
    'page_name' => 'Liste des Unités de valeur',
])

@section('content')
    <div class="card">
        <div class="text-end p-4 pb-sm-2 mb-2">
            <a href="{{ route('admin.uvs.create') }}" class="btn btn-primary">
                <i class="ti ti-plus f-18"></i> Ajouter une UV
            </a>
        </div>
        <div class="card-body">
            {{-- {{dd($uvs)}} --}}

            {{-- {{ dd(Illuminate\Database\Eloquent\Casts\Json::encode($uvs)) }} --}}
            @if ($uvs->isNotEmpty())
                <div class="dt-responsive table-responsive">
                    <table id="dom-jquery" class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nom</th>
                                <th scope="col">Code</th>
                                <th scope="col">Coef</th>
                                <th scope="col">UE</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($uvs as $key => $uv)
                                <tr>
                                    <th scope="row">{{ $key += 1 }}</th>
                                    <td>{{ $uv->nom }}</td>
                                    <td>{{ $uv->code }}</td>
                                    <td>{{ $uv->coefficient }}</td>
                                    <td>{{ $uv->ue->code }}</td>
                                    <td class="text-center">
                                        {{--									<button type="button" onclick="displayShowModal({{ Json::encode($uv) }})" data-bs-toggle="modal" data-bs-target="#show-modal"class="btn btn-icon btn-outline-info"> --}}
                                        {{--										<i class="ti ti-info-circle"></i> --}}
                                        {{--									</button> --}}
                                        <ul class="list-inline me-auto mb-0">
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Détails">
                                                <a href="#"
                                                    onclick="displayShowModal({{ Illuminate\Database\Eloquent\Casts\Json::encode($uv) }})"
                                                    data-bs-toggle="modal" data-bs-target="#show-modal"
                                                    class="avtar avtar-xs btn-link-secondary btn-pc-default">
                                                    <i class="ti ti-eye f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Modifier">
                                                <a href="{{ route('admin.uvs.edit', [$uv]) }}"
                                                    class="avtar avtar-xs btn-link-success btn-pc-default">
                                                    <i class="ti ti-edit-circle f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Supprimer">
                                                <a href="#" data-bs-target="#exampleModalToggle2"
                                                    data-bs-toggle="modal" onclick="deleteUv({{ $uv->id }})"
                                                    id='btn' class="avtar avtar-xs btn-link-danger btn-pc-default">
                                                    <i class="ti ti-trash f-18"></i>

                                                </a>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nom</th>
                                <th scope="col">Code</th>
                                <th scope="col">Coef</th>
                                <th scope="col">UE</th>
                                <th scope="col">Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="alert alert-warning">
                    <div class="media align-items-center">
                        <i class="ti ti-info-circle h2 f-w-400 mb-0"></i>
                        <div class="media-body ms-3"> Aucune donnée à afficher</div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    @include('admin.uvs._show-modal')
    @include('admin.uvs.___show-modal')
@endsection

@section('other-js')
    @include('layouts.admin._dt-scripts')
    <script>
        function deleteUv(uv) {
            document.getElementById('iduv').value = uv;
        }

        function displayShowModal(uv) {
            console.log(uv.ue.filiere.nom)
            document.getElementById('ue').value = uv.ue.nom;
            document.getElementById('nom').value = uv.nom;
            document.getElementById('code').value = uv.code;
            document.getElementById('coef').value = uv.coefficient;
            document.getElementById('cm').value = uv.cm;
            document.getElementById('td').value = uv.td;
            document.getElementById('tp').value = uv.tp;
            document.getElementById('ec').value = uv.ec;


            document.getElementById('filiere').value = uv.ue.filiere.nom;

            let profs = '';
            uv.user.forEach(function(us) {
                profs += us.nom + ' ' + us.prenom + ', ';
            });

            profs = profs.slice(0, -2); // Retire la dernière virgule
            document.getElementById('teacher').value = profs;
        }
    </script>
@endsection

@section('other-css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection
