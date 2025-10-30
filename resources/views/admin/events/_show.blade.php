<div class="modal fade modal-animate" id="eventShowModal" tabindex="-1"
		 aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Détails d'un évènement</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body row">
				<div class="form-group">
					<x-forms.label for="nom" content="Nom de l'évènement" required="0"/>
					<input id="nom" readonly class="form-control">
				</div>

				<div class="form-group">
					<x-forms.label for="create-date" content="Date de publication de l'évènement" required="0"/>
					<input id="create-date" readonly class="form-control text-capitalize">
				</div>

				<div class="form-group">
					<x-forms.label for="start_date" content="Date de début de l'évènement" required="0"/>
					<input id="start_date" readonly class="form-control text-capitalize">
				</div>

				<div class="form-group">
					<x-forms.label for="end_date" content="Date de fin de l'évènement" required="0"/>
					<input id="end_date" readonly class="form-control text-capitalize">
				</div>

				<div class="form-group">
					<x-forms.label for="details" content="Détails de publication de l'évènement" required="0"/>
					<div id="details" readonly class="form-control"></div>
				</div>
			</div>
		</div>
	</div>
</div>
