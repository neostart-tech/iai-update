@php use App\Models\Salle; @endphp
<div id="editSalleModal" class="modal fade" tabindex="-1" role="dialog"
		 aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<form action="{{ route('admin.salles.store') }}" method="post">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalCenterTitle">Formulaire de création d'une salle</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					@php($salle = new App\Models\Salle())
					@csrf
					<div class="form-group text-start">
						<x-forms.label content="Nom de la salle" for="nom" required="{{true}}"/>
						<input type="text" class="form-control uppercase" id="nom" name="nom" aria-describedby="nom"
									 placeholder="Nom de la filière" value="{{ old('nom', $salle->nom) }}">
						{!! errorAlert($errors->first('nom'), 'nom') !!}
					</div>

					<div class="form-group">
						<x-forms.label content="Nombre de place" for="effectif" required="{{true}}"/>
						<input type="number" step="1" min="1" class="form-control" id="effectif" name="effectif" aria-describedby="nom"
									 placeholder="Nombre de place" value="{{ old('effectif', $salle->effectif) }}">
						{!! errorAlert($errors->first('effectif'), 'effectif') !!}
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
					<button type="submit" class="btn btn-primary">Enregistrer</button>
				</div>
			</form>
		</div>
	</div>
</div>