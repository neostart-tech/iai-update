<div class="offcanvas offcanvas-end cal-event-offcanvas" tabindex="-1" id="calendar-add_edit_event">
	<div class="offcanvas-header">
		<h3 class="f-w-600 text-truncate">Add Events</h3>
		<a href="#" class="avtar avtar-s btn-link-danger btn-pc-default" data-bs-dismiss="offcanvas">
			<i class="ti ti-x f-20"></i>
		</a>
	</div>
	<div class="offcanvas-body">
		<form id="pc-form-event" novalidate>
			<div class="form-group">
				<label class="form-label">Title</label>
				<input type="email" class="form-control" id="pc-e-title" placeholder="Enter event title" autofocus>
			</div>
			<div class="form-group">
				<label class="form-label">Venue</label>
				<input type="email" class="form-control" id="pc-e-venue" placeholder="Enter event venue">
			</div>
			<div class="form-group m-0">
				<input type="hidden" class="form-control" id="pc-e-sdate">
				<input type="hidden" class="form-control" id="pc-e-edate">
			</div>
			<div class="form-group">
				<label class="form-label">Description</label>
				<textarea class="form-control" placeholder="Enter event description" rows="3"
									id="pc-e-description"></textarea>
			</div>
			<div class="form-group">
				<label class="form-label">Type</label>
				<select class="form-select" id="pc-e-type">
					<option value="empty" selected>Type</option>
					<option value="event-primary">Primary</option>
					<option value="event-secondary">Secondary</option>
					<option value="event-success">Success</option>
					<option value="event-danger">Danger</option>
					<option value="event-warning">Warning</option>
					<option value="event-info">Info</option>
				</select>
			</div>
			<div class="row justify-content-between">
				<div class="col-auto">
					<button type="button" class="btn btn-link-danger btn-pc-default" data-bs-dismiss="offcanvas"><i
							class="align-text-bottom me-1 ti ti-circle-x"></i> Fermer
					</button>
				</div>
				<div class="col-auto">
					<button id="pc_event_add" type="button" class="btn btn-secondary" data-pc-action="add">
						<span id="pc-e-btn-text"><i class="align-text-bottom me-1 ti ti-calendar-plus"></i> Modifier</span>
					</button>
				</div>
			</div>
		</form>
	</div>
</div>