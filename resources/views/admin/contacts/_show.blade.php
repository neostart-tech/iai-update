<div class="modal fade modal-animate" id="contactShowModal" tabindex="-1"
		 aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Détails d'un message</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body row">
				<div class="form-group">
					<x-forms.label for="nom" content="Nom de l'auteur" required="0"/>
					<input id="nom" readonly class="form-control">
				</div>

				<div class="form-group">
					<x-forms.label for="create-date" content="Email de l'auteur" required="0"/>
					<input id="email" readonly class="form-control text-capitalize">
				</div>

				<div class="form-group">
					<x-forms.label for="start_date" content="Téléphone de l'auteur" required="0"/>
					<input id="tel" readonly class="form-control text-capitalize">
				</div>

				<div class="form-group col-12 col-md-6">
					<x-forms.label for="details" content="Date de réception" required="0"/>
					<input id="date" readonly class="form-control">
				</div>

				<div class="form-group col-12 col-md-6">
					<x-forms.label for="details" content="Statut" required="0"/>
					<input id="status" readonly class="form-control">
				</div>

				<div class="form-group">
					<x-forms.label for="details" content="Contenu du message" required="0"/>
					<div id="message" readonly class="form-control"></div>
				</div>

			</div>
		</div>
	</div>
</div>
