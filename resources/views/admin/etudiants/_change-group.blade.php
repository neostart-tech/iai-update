<div class="modal fade modal-animate" id="animateModal" tabindex="-1"
		 aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="#" method="post" id="change-group-form">
				<div class="modal-header">
					<h5 class="modal-title">Changer de groupe</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					@csrf
					@method('put')
					<div class="form-group">
						<x-forms.label for="group-id" content="Groupe actuel" required="0"/>
						<input type="text" class="form-control" id="old-group" readonly>
					</div>
					<div class="form-group">
						<x-forms.label for="group-id" content="Groupe"/>
						<select class="form-control" data-trigger name="group_id" id="group-id">
							@foreach($groups as $group)
								<option value="{{ $group->slug }}" @selected(old('group_id', $group->slug))>
									{{ $group->nom }}
								</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
					<button type="submit" class="btn btn-primary shadow-2" id="submit-form-button">Changer de group</button>
				</div>
			</form>
		</div>
	</div>
</div>
