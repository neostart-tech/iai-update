<div class="modal fade" id="calendar-modal" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="calendar-modal-title f-w-600 text-truncate">Modal title</h3>
				<a href="#" class="avtar avtar-s btn-link-danger btn-pc-default" data-bs-dismiss="modal">
					<i class="ti ti-x f-20"></i>
				</a>
			</div>
			<div class="modal-body">
				<div class="d-flex">
					<div class="flex-shrink-0">
						<div class="avtar avtar-xs bg-light-secondary">
							<i class="ti ti-question-mark f-20"></i>
						</div>
					</div>
					<div class="flex-grow-1 ms-3">
						<h5 class="mb-1"><b id="type-ev"></b></h5>
						<p class="pc-event-title text-muted"></p>
					</div>
				</div>
				<div class="d-flex">
					<div class="flex-shrink-0">
						<div class="avtar avtar-xs bg-light-warning">
							<i class="ti ti-map-pin f-20"></i>
						</div>
					</div>
					<div class="flex-grow-1 ms-3">
						<h5 class="mb-1"><b>Salle</b></h5>
						<p class="pc-event-venue text-muted"></p>
					</div>
				</div>
				<div class="d-flex">
					<div class="flex-shrink-0">
						<div class="avtar avtar-xs bg-light-warning">
							<i class="ti ti-map-pin f-20"></i>
						</div>
					</div>
					<div class="flex-grow-1 ms-3">
						<h5 class="mb-1"><b>Groupe d'étudiants</b></h5>
						<p class="text-muted" id="show-group-id"></p>
					</div>
				</div>
				<div class="d-flex">
					<div class="flex-shrink-0">
						<div class="avtar avtar-xs bg-light-danger">
							<i class="ti ti-calendar-event f-20"></i>
						</div>
					</div>
					<div class="flex-grow-1 ms-3">
						<h5 class="mb-1"><b>Plage horaire</b></h5>
						<p class="pc-event-date text-muted text-capitalize"></p>
					</div>
				</div>
				<div class="d-flex">
					<div class="flex-shrink-0">
						<div class="avtar avtar-xs bg-light-primary">
							<i class="ti ti-user f-20"></i>
						</div>
					</div>
					<div class="flex-grow-1 ms-3">
						<h5 class="mb-1"><b>Chargé du cours</b></h5>
						<p class="text-muted" id="charge-du-cours"></p>
					</div>
				</div>
				<div class="d-flex">
					<div class="flex-shrink-0">
						<div class="avtar avtar-xs bg-light-primary">
							<i class="ti ti-file-text f-20"></i>
						</div>
					</div>
					<div class="flex-grow-1 ms-3">
						<h5 class="mb-1"><b>Description</b></h5>
						<p class="pc-event-description text-muted"></p>
					</div>
				</div>
			</div>
			<div class="modal-footer justify-content-between">
				<div class="flex-grow-1 text-start" id="control-button" style="display: none">
					<button type="button" class="btn btn-success" data-bs-dismiss="modal">Faire le contrôle</button>
				</div>
				<div class="flex-grow-1 text-end">
					<button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
				</div>
			</div>
		</div>
	</div>
</div>