<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="calendar-create_event"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="calendar-create_event">Nouvelle programmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="new-event-form">
                    @csrf
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label" for="salle-nom">Salle</label>
                            <input type="text" id="salle-nom" class="form-control" value="{{ $salle->nom }}"
                                readonly>
                        </div>
                        <input type="hidden" name="salle_id" value="{{ $salle->slug }}" id="create-salle-id">
                        <div class="form-group col-md-6">
                            <label class="form-label" for="create-date">Début</label>
                            <input type="date" value="{{ today()->format('Y-m-d') }}" class="form-control"
                                name="create_date" id="create-date">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-12 col-md-6">
                            <label class="form-label" for="create-debut">Début</label>
                            <input type="time" class="form-control" name="create_debut" id="create-debut">
                        </div>
                        <div class="form-group m-0 col-12 col-md-6">
                            <label class="form-label" for="create-fin">Fin</label>
                            <input type="time" class="form-control" id="create-fin" name="create_fin">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 text-end">
                            <button type="button" id="check-availability-btn" class="btn btn-outline-secondary btn-sm">
                                Vérifier la disponibilité de la salle
                            </button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label" for="create-type">Type</label>
                            <select class="form-control" data-trigger name="create_type" id="create-type">
                                @foreach ($types as $type)
                                    <option value="{{ $type->value }}" @selected(old('type', $type->value) === $type->value)>
                                        {{ $type->value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label" for="create-uv-id">Matière</label>
                            <select class="form-control" data-trigger name="create_uv_id" id="create-uv-id">
                                <option>Choisissez la matière</option>
                                @foreach ($uvs as $uv)
                                    <option value="{{ $uv->slug }}" @selected(old('ue_id', $uv->unite_enseignement_id) === $uv->id)>
                                        {{ $uv->nom . ' (' . $uv->code . ')' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label" for="create-grade-id">Groupe</label>
                            <select class="form-control" data-trigger name="create_grade_id" id="create-grade-id">
                                @foreach ($groups as $group)
                                    <option value="{{ $group->slug }}" @selected(old('grade_id') === $group->slug)>
                                        {{ $group->nom . ' - ' . $group->filiere->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label" for="create-teacher-id">Enseignant</label>
                            <select class="form-control" data-trigger name="create_teacher_id" id="create-teacher-id">
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->slug }}" @selected(old('create_teacher_id') === $teacher->slug)>
                                        {{ $teacher->nom . ' ' . $teacher->prenom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="create-details">Détails</label>
                        <textarea name="details" class="form-control" id="create-details" cols="30" rows="3">{{ old('detail') }}</textarea>
                    </div>
                    <div class="row justify-content-between">
                        <div class="col-auto">
                            <button type="button" class="btn btn-link-danger btn-pc-default" data-bs-dismiss="modal">
                                <i class="align-text-bottom me-1 ti ti-circle-x"></i> Fermer
                            </button>
                        </div>
                        <div class="col-auto">
                            <button id="handle-event-create-submit-form" type="button" class="btn btn-secondary"
                                data-pc-action="add">
                                <span><i class="align-text-bottom me-1 ti ti-calendar-plus"></i> Ajouter</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Modal   de la modification-->
<div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title h4" id="calendar-create_event">Modification d'une programmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="new-event-form">
                    @csrf
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label" for="salle-nom">Salle</label>
                            <input type="text" id="salle-nom" class="form-control" value="{{ $salle->nom }}"
                                readonly>
                        </div>
                        <input type="text" id='edtId'>
                        <input type="hidden" name="salle_id" value="{{ $salle->slug }}" id="create-salle-id">
                        <div class="form-group col-md-6">
                            <label class="form-label" for="create-date">Début</label>
                            <input type="date" value="{{ today()->format('Y-m-d') }}" class="form-control"
                                name="create_date" id="create-date">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-12 col-md-6">
                            <label class="form-label" for="create-debut">Début</label>
                            <input type="time" class="form-control" name="create_debut" id="create-debut" value="{{ $salle->debut }}">
                        </div>
                        <div class="form-group m-0 col-12 col-md-6">
                            <label class="form-label" for="create-fin">Fin</label>
                            <input type="time" class="form-control" id="create-fin" name="create_fin" value="{{ $salle->fin }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label" for="create-type">Type</label>
                            <select class="form-control" data-trigger name="create_type" id="create-type">
                                @foreach ($types as $type)
                                    <option value="{{ $type->value }}" @selected(old('type', $type->value) === $type->value)>
                                        {{ $type->value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label" for="create-uv-id">Matière</label>
                            <select class="form-control" data-trigger name="create_uv_id" id="create-uv-id">
                                <option>Choisissez la matière</option>
                                @foreach ($uvs as $uv)
                                    <option value="{{ $uv->slug }}" @selected(old('ue_id', $uv->unite_enseignement_id) === $uv->id)>
                                        {{ $uv->nom . ' (' . $uv->code . ')' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label" for="create-grade-id">Groupe</label>
                            <select class="form-control" data-trigger name="create_grade_id" id="create-grade-id">
                                @foreach ($groups as $group)
                                    <option value="{{ $group->slug }}" @selected(old('grade_id') === $group->slug)>
                                        {{ $group->nom . ' - ' . $group->filiere->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label class="form-label" for="create-teacher-id">Enseignant</label>
                            <select class="form-control" data-trigger name="create_teacher_id" id="create-teacher-id">
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->slug }}" @selected(old('create_teacher_id') === $teacher->slug)>
                                        {{ $teacher->nom . ' ' . $teacher->prenom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="create-details">Détails</label>
                        <textarea name="details" class="form-control" id="create-details" cols="30" rows="3">{{ old('detail') }}</textarea>
                    </div>
                    <div class="row justify-content-between">
                        <div class="col-auto">
                            <button type="button" class="btn btn-link-danger btn-pc-default" data-bs-dismiss="modal">
                                <i class="align-text-bottom me-1 ti ti-circle-x"></i> Fermer
                            </button>
                        </div>
                        <div class="col-auto">
                            <button id="handle-event-create-submit-form" type="button" class="btn btn-secondary"
                                data-pc-action="add">
                                <span><i class="align-text-bottom me-1 ti ti-calendar-plus"></i> Ajouter</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>  

<script>
    // Vérifier la disponibilité de la salle avec les infos saisies
    document.getElementById('check-availability-btn')?.addEventListener('click', function() {
        const salle = document.getElementById('create-salle-id')?.value;
        const date = document.getElementById('create-date')?.value;
        const debut = document.getElementById('create-debut')?.value;
        const fin = document.getElementById('create-fin')?.value;

        if (!salle || !date || !debut || !fin) {
            showToast('Veuillez saisir la date, l\'heure de début et de fin.', 'danger');
            return;
        }

        $.get("{{ route('admin.edt.check-availability') }}", { salle, date, debut, fin })
            .then(resp => {
                if (resp.available) showToast(resp.message || 'Salle disponible.', 'success');
                else showToast(resp.message || 'Salle occupée.', 'danger');
            })
            .catch(() => showToast('Vérification impossible pour le moment.', 'danger'));
    });

    function fillModal(eventData) {
    // Remplir les champs
    document.getElementById('edtId').value = eventData.slug;

    // Date au format yyyy-MM-dd
    const dateDebut = eventData.debut.split(' ')[0];
    const heureDebut = eventData.debut.split(' ')[1];
    const heureFin = eventData.fin.split(' ')[1];

    document.getElementById('create-date').value = dateDebut;
    document.getElementById('create-debut').value = heureDebut;
    document.getElementById('create-fin').value = heureFin;
    document.getElementById('create-type').value = eventData.type;
    document.getElementById('create-uv-id').value = eventData.uv_id;
    document.getElementById('create-grade-id').value = eventData.group_id;
    document.getElementById('create-teacher-id').value = eventData.teacher_id;
    document.getElementById('create-salle-id').value = eventData.salle_id;
    document.getElementById('create-details').value = eventData.details;
}

</script>