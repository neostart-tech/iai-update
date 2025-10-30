@php use Illuminate\Support\Facades\Storage; @endphp
@extends('base', $meta)

@section('content')
    <div class="card">
        <div class="text-end p-4 pb-sm-2 mb-2">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="ti ti-plus f-18"></i> Ajouter un utilisateur
            </a>
        </div>
        <div class="card-body">
            @if ($users->isNotEmpty())
                <div class="dt-responsive table-responsive">
                    <table id="dom-jquery" class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nom</th>
                                <th scope="col">Prénoms</th>
                                <th scope="col">Rôles</th>
                                <th scope="col">Type Surveillant</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $key => $user)
                                <tr>
                                    <th scope="row">{{ $key += 1 }}</th>
                                    <td>{{ $user->nom_upper }}</td>
                                    <td>{{ $user->prenom }}</td>
                                    <td>
                                        @foreach($user->roles as $role)
                                            <span class="badge bg-primary me-1">{{ $role->nom }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @if($user->supervisor_type === 'interne')
                                            <span class="badge bg-success">Surveillant Interne</span>
                                        @elseif($user->supervisor_type === 'externe')
                                            <span class="badge bg-warning text-dark">Surveillant Externe</span>
                                        @else
                                            <span class="text-muted">Non surveillant</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <ul class="list-inline me-auto mb-0">
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="View">
                                                <a href="#"
                                                    onclick="displayShowModal(
    {{ $user->toJson() }},
    '{{ !empty($user->image) && Storage::exists($user->image)
        ? Storage::url($user->image)
        : asset($user->image ?? 'images/default.png') }}'
)"
                                                    class="avtar avtar-xs btn-link-secondary btn-pc-default"
                                                    data-bs-toggle="modal" data-bs-target="#show-modal">
                                                    <i class="ti ti-eye f-18"></i>
                                                </a>
                                            </li>
                                            @isset($enseignants)
                                                <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                    title="Emploi du temps">
                                                    <a href="{{ route('admin.users.show-edt', $user) }}"
                                                        class="avtar avtar-xs btn-link-success btn-pc-default">
                                                        <i class="ti ti-calendar-event f-18"></i>
                                                    </a>
                                                </li>
                                            @endisset
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Modifier">
                                                <a href="{{ route('admin.users.edit', [$user]) }}"
                                                    class="avtar avtar-xs btn-link-success btn-pc-default">
                                                    <i class="ti ti-edit-circle f-18"></i>
                                                </a>
                                            </li>
                                            <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                                title="Supprimer">
                                                <a href="#" onclick="deleteUser({{ $user->id }})"
                                                    data-bs-target="#exampleModalToggle2" data-bs-toggle="modal"
                                                    class="avtar avtar-xs btn-link-danger btn-pc-default">
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
                                <th scope="col">Prénoms</th>
                                <th scope="col">Rôles</th>
                                <th scope="col">Type Surveillant</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <x-empty-table content="Aucun enseignant n'a encore été enregistré" />
            @endif
        </div>
    </div>
    @include('admin.users.__show-modal')
    @include('admin.users._show')
@endsection

@section('other-js')
    @include('layouts.admin._dt-scripts')
    <script>
        let permissionsTable = document.getElementById('user-roles-table');

        function displayShowModal(user, img) {
            document.getElementById('user-nom').innerHTML = `${user.nom} ${user.prenom}`;
            document.getElementById('user-img').src = `${img}`;
            document.getElementById('user-desc').innerHTML = `${user.biographie ?? "Aucune biographie"}`;

            Array.from(user.roles).forEach((role) => {
                let tr = document.createElement('tr');
                let td = document.createElement('td');
                td.innerText = role.nom;
                tr.appendChild(td);
                permissionsTable.appendChild(tr);
            });
        }

        $('#show-modal').on('hidden.bs.modal', () => {
            for (const element of $('#user-roles-table').children()) {
                element.remove();
            }
        })

        function deleteUser(id) {
            document.getElementById('uderId').value = id;
        }
    </script>
@endsection

@section('other-css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection
