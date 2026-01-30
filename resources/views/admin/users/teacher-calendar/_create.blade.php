<div class="modal fade bd-example-modal-lg" tabindex="-1" id="calendar-create_event" role="dialog"
     aria-labelledby="calendar-create_event" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title h4">Nouvelle programmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="new-event-form">
                    @csrf

                    <!-- Enseignant & Date -->
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label">Enseignant</label>
                            <input type="text" class="form-control"
                                   value="{{ $user->nom.' '.$user->prenom }}" readonly>
                        </div>

                        <input type="hidden" id="create-teacher-id" value="{{ $user->slug }}">

                        <div class="form-group col-md-6">
                            <label class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="create-date"
                                   value="{{ today()->format('Y-m-d') }}">
                        </div>
                    </div>

                    <!-- Heures -->
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label">Heure début</label>
                            <input type="time" class="form-control" id="create-debut">
                        </div>

                        <div class="form-group col-md-6">
                            <label class="form-label">Heure fin</label>
                            <input type="time" class="form-control" id="create-fin">
                        </div>
                    </div>

                    <!-- Type programme & matière -->
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label">Type</label>
                            <select class="form-control" id="create-type">
                                @foreach($types as $type)
                                    <option value="{{ $type->value }}">{{ $type->value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label class="form-label">Matière</label>
                            <select class="form-control" id="create-uv-id">
                                @foreach($uvs as $uv)
                                    <option value="{{ $uv->slug }}">
                                        {{ $uv->nom }} ({{ $uv->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Groupe & salle -->
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label">Groupe</label>
                            <select class="form-control" id="create-grade-id">
                                @foreach($groups as $group)
                                    <option value="{{ $group->slug }}">
                                        {{ $group->niveau->libelle }} - {{ $group->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label class="form-label">Salle</label>
                            <select class="form-control" id="create-salle-id">
                                @foreach($salles as $salle)
                                    <option value="{{ $salle->slug }}">{{ $salle->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!--  TYPE DE PROGRAMMATION -->
                    <hr>
                    <div class="form-group">
                        <label class="form-label">Type de programmation</label>
                        <select class="form-control" id="recurrence-type" name="recurrence_type">
                            <option value="aucune">Ponctuelle</option>
                            <option value="hebdomadaire">Récurrente (hebdomadaire)</option>
                        </select>
                    </div>

                    <!-- JOURS -->
                    <div class="form-group d-none" id="recurrence-days">
                        <label class="form-label">Jours</label>
                        <div class="d-flex flex-wrap gap-3">
                            <label><input type="checkbox" value="mo" name="recurrence_days[]"> Lundi</label>
                            <label><input type="checkbox" value="tu" name="recurrence_days[]"> Mardi</label>
                            <label><input type="checkbox" value="we" name="recurrence_days[]"> Mercredi</label>
                            <label><input type="checkbox" value="th" name="recurrence_days[]"> Jeudi</label>
                            <label><input type="checkbox" value="fr" name="recurrence_days[]"> Vendredi</label>
                            <label><input type="checkbox" value="sa" name="recurrence_days[]"> Samedi</label>
                        </div>
                    </div>

                    <!-- FIN RECURRENCE -->
                    <div class="form-group d-none" id="recurrence-end">
                        <label class="form-label">Date de fin</label>
                        <input type="date" class="form-control" id="recurrence-end-date">
                    </div>

                    <!-- Détails -->
                    <div class="form-group">
                        <label class="form-label">Détails</label>
                        <textarea class="form-control" id="create-details" rows="3"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="row justify-content-between">
                        <div class="col-auto">
                            <button type="button" class="btn btn-link-danger" data-bs-dismiss="modal">
                                Fermer
                            </button>
                        </div>
                        <div class="col-auto">
                            <button type="button" id="handle-event-create-submit-form"
                                    class="btn btn-secondary">
                                Ajouter
                            </button>
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
<script>
document.getElementById('recurrence-type').addEventListener('change', function () {
    const show = this.value === 'hebdomadaire';
    document.getElementById('recurrence-days').classList.toggle('d-none', !show);
    document.getElementById('recurrence-end').classList.toggle('d-none', !show);
});
</script>
