<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="dt-responsive table-responsive">
                    @if ($candidatures->isNotEmpty())

                    <table id="dom-jquery" class="table table-hover">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nom</th>
                                <th scope="col">Prénoms</th>
                                <th scope="col">Code</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($candidatures as $key => $candidature)
                            <tr>
                                <th scope="row">{{ $key += 1 }}</th>
                                <td>{{ $candidature->nom }}</td>
                                <td>{{ $candidature->prenom }}</td>
                                <td>{{ $candidature->code }}</td>
                                <td class="text-center">

                                    <ul class="list-inline me-auto mb-0">

                                        <li class="list-inline-item align-bottom" data-bs-toggle="tooltip"
                                            title="Finaliser l'inscription">
                                            <form action="{{route('admin.candidatures.inscrire-un-etudiant',$candidature)}}" method="post" id="studentForm">
                                                @csrf
                                                <button id="saveBtn" type="submit"
                                                    class="saveBtn finalize-btn avtar avtar-xs btn-link-success btn-pc-default">
                                                    <i class="ti ti-user-check f-18"></i>
                                                </button>
                                            </form>
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
                                <th scope="col">Code</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </tfoot>
                    </table>

                    @else
                    <div class="alert alert-danger">
                        <div class="media align-items-center">
                            <i class="ti ti-info-circle h2 f-w-400 mb-0"></i>
                            <div class="media-body ms-3"> Aucune candidature a afficher dans cette section</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@include("layouts._scripts")



<script>
    function saveStudent() {
        const form = document.getElementById('studentForm');

        form.submit();
    }
    document.querySelectorAll('saveBtn').forEach(btn => {
        btn.addEventListener('click', saveStudent)
    })
</script>

<style>

</style>