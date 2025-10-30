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
					<x-forms.label for="card-nom" content="Nom de l'unité d'enseignement" required="0"/>
					<input id="card-nom" readonly class="form-control">
				</div>
                <div class="form-group">
					<x-forms.label for="ue-code" content="Code de l'unité d'enseignement" required="0"/>
					<input id="ue-code" readonly class="form-control text-capitalize">
				</div>

                <div class="form-group">
					<x-forms.label for="ue-credit" content="Nombre de crédit" required="0"/>
					<input id="ue-credit" readonly class="form-control text-capitalize">
				</div>

                <div class="form-group">
					<x-forms.label for="ue-filiere-nom" content="Nom de la filiere" required="0"/>
					<input id="ue-filiere-nom" readonly class="form-control text-capitalize">
				</div>

				<div class="form-group">
					<x-forms.label for="ue-periode" content="Période" required="0"/>
					<div id="ue-periode" readonly class="form-control"></div>
				</div>
			</div>
		</div>
	</div>
</div>
