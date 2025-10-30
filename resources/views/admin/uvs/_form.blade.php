<form method="post" action="{{ $action }}" enctype="multipart/form-data">
	@csrf
	@isset($edit)
		@method('put')
	@endisset

	<div class="row mb-3">

		<div class="col-lg-6 col-md-11 col-sm-12 mb-3">
			<x-forms.label content="Unité d'enseignement" for="ue_id" required="{{true}}"/>
			<select class="form-control" data-trigger name="ue_id" id="ue_id">
				<option>Attribuez une unité d'enseignement à la matière</option>
				@foreach($ues as $ue)
					<option value="{{ $ue->id }}" @selected(old('ue_id', $uv->unite_enseignement_id) === $ue->id)>
						{{ $ue->nom . ' ('. $ue->code .')' }}
					</option>
				@endforeach
			</select>
				function toggleWeight(id, enabled) {
					const el = document.getElementById(id);
					el.disabled = !enabled;
					if (!enabled) el.value = '0';
				}

		</div>

		<div class="form-group col-12 col-md-6">
			<x-forms.label content="Nom de la matière" for="nom" required="{{true}}"/>
			<input type="text" class="form-control" id="nom" name="nom" aria-describedby="nom"
						 placeholder="Nom de la matière" value="{{ old('nom', $uv->nom) }}">
			{!! errorAlert($errors->first('nom'), 'nom') !!}
		</div>

		<div class="form-group col-12 col-md-6">
			<x-forms.label content="Code de la matière" for="code" required="{{true}}"/>
			<input type="text" class="form-control" id="code" name="code" aria-describedby="code"
						 placeholder="Code de l'UE" value="{{ old('code', $uv->code) }}">

				// checkbox bindings
				document.getElementById('enable_devoir').addEventListener('change', e => toggleWeight('poids_devoir', e.target.checked));
				document.getElementById('enable_interrogation').addEventListener('change', e => toggleWeight('poids_interrogation', e.target.checked));
				document.getElementById('enable_examen').addEventListener('change', e => toggleWeight('poids_examen', e.target.checked));
				document.getElementById('enable_tp').addEventListener('change', e => toggleWeight('poids_tp', e.target.checked));
				document.getElementById('enable_expose').addEventListener('change', e => toggleWeight('poids_expose', e.target.checked));
			</script>
			{!! errorAlert($errors->first('code'), 'code') !!}
		</div>


		<div class="form-group col-12 col-md-6">
			<x-forms.label content="Coefficient de la matière" for="coefficient" required="{{true}}"/>
			<input type="number" min="1" class="form-control" id="coefficient" step="1" name="coefficient" aria-describedby="coefficient"
						 placeholder="Coefficient de la matière" value="{{ old('coefficient', $uv->coefficient) }}">
			{!! errorAlert($errors->first('coefficient'), 'coefficient') !!}
		</div>

		<div class="form-group col-12 col-md-6">
			<x-forms.label content="CM de la matière" for="cm" required="{{true}}"/>
			<input type="number" min="1" class="form-control" id="cm" step="1" name="cm" aria-describedby="cm"
						 placeholder="cm de la matière" value="{{ old('cm', $uv->cm) }}">
			{!! errorAlert($errors->first('cm'), 'cm') !!}
		</div>

		<div class="form-group col-12 col-md-6">
			<x-forms.label content="Volume horaire des Travaux Dirigés de l'UE" for="td" required="{{true}}"/>
			<input type="number" min="1" class="form-control" id="td" step="1" name="td" aria-describedby="td"
						 placeholder="td de la matière" value="{{ old('td', $uv->td) }}">
			{!! errorAlert($errors->first('td'), 'td') !!}
		</div>

		<div class="form-group col-12 col-md-6">
			<x-forms.label content="Volume horaire des Travaux Pratiques de l'UE" for="tp" required="{{true}}"/>
			<input type="number" min="1" class="form-control" id="tp" step="1" name="tp" aria-describedby="tp"
						 placeholder="tp de la matière" value="{{ old('tp', $uv->tp) }}">
			{!! errorAlert($errors->first('tp'), 'tp') !!}
		</div>

		<div class="form-group col-12 col-md-6">
			<x-forms.label content="EC de l'UE" for="ec" required="{{true}}"/>
			<input type="number" min="1" class="form-control" id="ec" name="ec" aria-describedby="ec"
						 placeholder="ec de la matière" value="{{ old('ec', $uv->ec) }}">
			{!! errorAlert($errors->first('ec'), 'ec') !!}
		</div>

		<div class="col-lg-6 col-md-11 col-sm-12">
			<x-forms.label content="Enseignant(s) de la matière" for="enseignant_id" required="{{true}}"/>
			<select class="form-control" data-trigger name="enseignant_id[]" id="enseignant_id" multiple>
				@foreach($enseignants as $enseignant)
					<option value="{{ $enseignant->id }}" 
						@if (!empty($enseignantsSelected))
							@if(in_array($enseignant->id, old('enseignant_id', $enseignantsSelected))) selected @endif
						@endif
						>
						{{ $enseignant->nom . ' ' . $enseignant->prenom }}
					</option>
				@endforeach
			</select>
			
			{!! errorAlert($errors->first('enseignant_id'), 'enseignant_id') !!}
			<small class="text-muted">Maintenez Ctrl/Cmd pour sélectionner plusieurs enseignants.</small>
		</div>
	</div>

	<div class="row mb-4">
		<div class="col-12">
			<h6>Types d'évaluations et pourcentages (cochez les types utilisés, somme = 100)</h6>
		</div>
		<div class="form-group col-12 mb-2">
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="checkbox" id="enable_devoir" checked>
				<label class="form-check-label" for="enable_devoir">Devoir</label>
			</div>
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="checkbox" id="enable_interrogation" checked>
				<label class="form-check-label" for="enable_interrogation">Interrogation</label>
			</div>
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="checkbox" id="enable_examen" checked>
				<label class="form-check-label" for="enable_examen">Examen</label>
			</div>
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="checkbox" id="enable_tp">
				<label class="form-check-label" for="enable_tp">TP</label>
			</div>
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="checkbox" id="enable_expose">
				<label class="form-check-label" for="enable_expose">Exposé</label>
			</div>
		</div>
		<div class="form-group col-6 col-md-2">
			<x-forms.label content="Devoir %" for="poids_devoir" required="{{false}}"/>
			<input type="number" class="form-control" min="0" max="100" name="poids_devoir" id="poids_devoir" value="{{ old('poids_devoir') }}">
		</div>
		<div class="form-group col-6 col-md-2">
			<x-forms.label content="Interrogation %" for="poids_interrogation" required="{{false}}"/>
			<input type="number" class="form-control" min="0" max="100" name="poids_interrogation" id="poids_interrogation" value="{{ old('poids_interrogation') }}">
		</div>
		<div class="form-group col-6 col-md-2">
			<x-forms.label content="Examen %" for="poids_examen" required="{{false}}"/>
			<input type="number" class="form-control" min="0" max="100" name="poids_examen" id="poids_examen" value="{{ old('poids_examen') }}">
		</div>
		<div class="form-group col-6 col-md-2">
			<x-forms.label content="TP %" for="poids_tp" required="{{false}}"/>
			<input type="number" class="form-control" min="0" max="100" name="poids_tp" id="poids_tp" value="{{ old('poids_tp') }}">
		</div>
		<div class="form-group col-6 col-md-2">
			<x-forms.label content="Exposé %" for="poids_expose" required="{{false}}"/>
			<input type="number" class="form-control" min="0" max="100" name="poids_expose" id="poids_expose" value="{{ old('poids_expose') }}">
		</div>
		<div class="form-group col-12">
			<small class="text-muted">Si aucun pourcentage n'est indiqué, les valeurs par défaut seront utilisées (Devoir 30, Interrogation 10, Examen 60).</small>
		</div>
	</div>

	<button type="submit" class="btn btn-primary mb-4">Soumettre</button>
</form>

@section('other-js')
	@include('layouts._select-search-script')
	<script>
		function validateWeightsSum() {
			const vals = ['poids_devoir','poids_interrogation','poids_examen','poids_tp','poids_expose']
				.map(id => parseInt(document.getElementById(id)?.value || '0', 10));
			const sum = vals.reduce((a,b)=>a+b,0);
			if (sum !== 0 && sum !== 100) {
				Swal.fire({icon:'warning', title:'Somme invalide', text:`La somme des pourcentages doit être égale à 100 (actuellement ${sum}).`});
				return false;
			}
			return true;
		}
		document.querySelector('form').addEventListener('submit', function(e){
			if(!validateWeightsSum()) e.preventDefault();
		});
	</script>
@endsection
