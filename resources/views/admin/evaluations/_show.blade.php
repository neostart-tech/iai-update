<div class="modal fade" id="show-modal" data-bs-keyboard="false" tabindex="-1"
		 aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header border-0 pb-0">
				<h5 class="mb-0">Détails d'une évaluation</h5>
				<a href="#" class="avtar avtar-s btn-link-danger btn-pc-default" data-bs-dismiss="modal">
					<i class="ti ti-x f-20"></i>
				</a>
			</div>
			<div class="modal-body">
				<form>
					<div class="row">
						<div class="form-group col-md-6">
							<x-forms.label for="show-type" content="Type" required="{{0}}"/>
							<input class="form-control" type="text" id="show-type" readonly>
						</div>

						<div class="form-group col-md-6">
							<x-forms.label for="show-group" content="Groupe" required="{{0}}"/>
							<input class="form-control" type="text" id="show-group" readonly>
						</div>

						<div class="form-group col-md-6">
							<x-forms.label for="show-matiere" content="Matière" required="{{0}}"/>
							<input class="form-control" type="text" id="show-matiere" readonly>
						</div>

						<div class="form-group col-md-6">
							<x-forms.label for="show-salle" content="Salle" required="{{0}}"/>
							<input class="form-control" type="text" id="show-salle" readonly>
						</div>

						<div class="form-group col-md-4">
							<x-forms.label for="show-date" content="Date" required="{{0}}"/>
							<input class="form-control text-capitalize" type="text" id="show-date" readonly>
						</div>

						<div class="form-group col-md-4">
							<x-forms.label for="show-debut" content="Début" required="{{0}}"/>
							<input class="form-control" type="text" id="show-debut" readonly>
						</div>

						<div class="form-group col-md-4">
							<x-forms.label for="show-fin" content="Fin" required="{{0}}"/>
							<input class="form-control" type="text" id="show-fin" readonly>
						</div>

						<div class="form-group col-md-6">
							<x-forms.label for="show-published" content="Statut de publication" required="{{0}}"/>
							<input class="form-control" type="text" id="show-published" readonly>
						</div>

						<div class="form-group col-md-6">
							<x-forms.label for="show-passed" content="Statut de tenue" required="{{0}}"/>
							<input class="form-control" type="text" id="show-passed" readonly>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>