<div class="modal fade bd-example-modal-lg" tabindex="-1" id="calendar-create_event" role="dialog"
		 aria-labelledby="calendar-create_event" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title h4">Nouvelle programmation</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form id="new-event-form">
					@csrf
					<div class="row">
						<div class="form-group col-md-6">
							<label class="form-label" for="salle-nom">Enseignant</label>
							<input type="text" id="salle-nom" class="form-control" value="{{ $user->nom . ' '. $user->prenom}}"
										 readonly>
						</div>
						<input type="hidden" value="{{ $user->slug }}" id="create-teacher-id">
						<div class="form-group col-md-6">
							<label class="form-label" for="create-date">Début</label>
							<input type="date" value="{{ today()->format('Y-m-d') }}" class="form-control" name="create_date"
										 id="create-date">
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
					<div class="row">
						<div class="form-group col-md-6">
							<label class="form-label" for="create-type">Type</label>
							<select class="form-control" data-trigger name="create_type" id="create-type">
								@foreach($types as $type)
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
								@foreach($uvs as $uv)
									<option value="{{ $uv->slug }}" @selected(old('ue_id', $uv->unite_enseignement_id) === $uv->id)>
										{{ $uv->nom . ' ('. $uv->code .')' }}
									</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-6">
							<label class="form-label" for="create-grade-id">Groupe</label>
							<select class="form-control" data-trigger name="create_grade_id" id="create-grade-id">
								@foreach($groups as $group)
									<option value="{{ $group->slug }}" @selected(old('grade_id') === $group->slug)>
										{{ $group->nom . ' - '. $group->filiere->code}}
									</option>
								@endforeach
							</select>
						</div>
						<div class="form-group col-md-6">
							<label class="form-label" for="create-salle-id">Salle</label>
							<select class="form-control" data-trigger name="create_teacher_id" id="create-salle-id">
								@foreach($salles as $salle)
									<option value="{{ $salle->slug }}" @selected(old('salle_id') === $salle->slug)>
										{{ $salle->nom }}
									</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="form-label" for="create-details">Détails</label>
						<textarea name="details" class="form-control" id="create-details" cols="30"
											rows="3">{{ old('detail') }}</textarea>
					</div>
					<div class="row justify-content-between">
						<div class="col-auto">
							<button type="button" class="btn btn-link-danger btn-pc-default" data-bs-dismiss="modal">
								<i class="align-text-bottom me-1 ti ti-circle-x"></i> Fermer
							</button>
						</div>
						<div class="col-auto">
							<button id="handle-event-create-submit-form" type="button" class="btn btn-secondary" data-pc-action="add">
						<span><i
								class="align-text-bottom me-1 ti ti-calendar-plus"></i> Ajouter</span>
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
