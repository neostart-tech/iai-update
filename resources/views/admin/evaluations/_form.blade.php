<form action="{{ $action }}" method="post">
	@isset($method)
		@method('PUT')
	@endisset
	@csrf
	<div class="row">
		<div class="form-group col-md-6">
			<x-forms.label for="niveau_id" content="Niveau"/>
			<select class="form-control" name="niveau_id" id="niveau_id">
				<option value="">-- Optionnel --</option>
				@foreach(($niveaux ?? []) as $niveau)
					<option value="{{ $niveau->id }}" @selected(old('niveau_id', $evaluation->niveau_id) == $niveau->id)>{{ $niveau->nom ?? ($niveau->code ?? $niveau->id) }}</option>
				@endforeach
			</select>
			{!! errorAlert($errors->first('niveau_id'), 'niveau_id') !!}
		</div>

		<div class="form-group col-md-6">
			<x-forms.label for="semestre" content="Semestre"/>
			<select class="form-control" name="semestre" id="semestre">
				<option value="1" @selected(old('semestre', $evaluation->semestre) == 1)>Semestre 1</option>
				<option value="2" @selected(old('semestre', $evaluation->semestre) == 2)>Semestre 2</option>
			</select>
			{!! errorAlert($errors->first('semestre'), 'semestre') !!}
		</div>
		<div class="form-group col-md-6">
			<x-forms.label for="type" content="Type"/>
			<select class="form-control" name="type" id="type">
				@foreach($types as $type)
					<option value="{{ $type->value }}" @selected($evaluation->type === $type)>{{ $type->value }}</option>
				@endforeach
			</select>
			<small class="text-muted">⚠️ Limite : maximum 2 évaluations par type par matière</small>
			{!! errorAlert($errors->first('type'), 'type') !!}
		</div>

		<div class="form-group col-md-6">
			<x-forms.label for="group" content="Groupe"/>
			<select class="form-control" data-trigger name="group_id" id="group">
				@foreach($groups as $group)
					<option value="{{ $group->slug }}" id="{{ $group->slug }}" @selected($evaluation->group_id === $group->id)>
						{{ $group->nom . ' - '. $group->filiere->code }}
					</option>
				@endforeach
			</select>
			{!! errorAlert($errors->first('group_id'), 'group_id') !!}
		</div>

		<div class="form-group col-md-6">
			<x-forms.label for="unite_valeur" content="Matières"/>
			<select class="form-control" name="unite_valeur_id" id="unite_valeur">
			</select>
			{!! errorAlert($errors->first('unite_valeur_id'), 'unite_valeur_id') !!}
		</div>

		<div class="form-group col-md-6">
			<x-forms.label for="salle_id" content="Salle"/>
			<select class="form-control" data-trigger name="salle_id" id="salle_id">
				@foreach($salles as $salle)
					<option value="{{ $salle->id }}" @selected($evaluation->salle_id === $salle->id)>{{ $salle->nom }}</option>
				@endforeach
			</select>
			{!! errorAlert($errors->first('salle_id'), 'salle_id') !!}
		</div>

		<div class="form-group col-lg-4">
			<x-forms.label for="date" content="Date de l'évaluation"/>
			<input class="form-control" type="date" value="{{ old('date', $evaluation->debut->format('Y-m-d')) }}" name="date" id="date">
			{!! errorAlert($errors->first('date'), 'date') !!}
		</div>

		<div class="form-group col-lg-4">
			<x-forms.label for="debut" content="Heure de début"/>
			<input class="form-control" type="time" value="{{ old('debut', $evaluation->debut->format('H:i')) }}" name="debut" id="debut">
			{!! errorAlert($errors->first('debut'), 'debut') !!}
		</div>

		<div class="form-group col-lg-4">
			<x-forms.label for="fin" content="Heure de Fin"/>
			<input class="form-control" value="{{ old('fin', $evaluation->fin->format('H:i')) }}" type="time" name="fin" id="fin">
			{!! errorAlert($errors->first('fin'), 'fin') !!}
		</div>

		<div class="form-group col-lg-4">
			<x-forms.label for='correction_end_date' content="Date de remise des corrections" required="0"/>
			<input class="form-control" type="date" name='correction_end_date' id='correction_end_date'
				value="{{ old('correction_end_date', $evaluation->correction_end_date->translatedFormat('Y-m-d')) }}">
			{!! errorAlert($errors->first('correction_end_date'), 'correction_end_date') !!}
		</div>
		<div class="form-group">
			<input type="checkbox" @checked(old('published')) class="form-check-input" name="published" id="published">
			<x-forms.label for="published" required="{{0}}" class="form-check-label" content="Publier la programmation auprès des étudiants concernés ?"/>
		</div>
		{!! errorAlert($errors->first('published'), 'published') !!}
	</div>
	<button class="btn btn-primary">Soumettre</button>
</form>

@section('other-js')
	@include('layouts._select-search-script')
	<script>
		let data = [];
		const group = document.getElementById('group');
		const uniteValeur = document.getElementById('unite_valeur');
		const initOption = document.createElement('option');

		initOption.text = "Choisissez un groupe pour en afficher les matiere";
		initOption.setAttribute('id', 'init-option');
		uniteValeur.append(initOption);

		const getMatieres = urlParam => {
			const baseUrl = "{{ url('administration/groups/{group}/get-matieres') }}";
			$.get(baseUrl.replace('{group}', urlParam)).then(response => {
				data = response.data;
				addMatieres()
			}).catch((error) => {
				showToast(error, 'danger')
			});
		}

		new Choices(group).passedElement.element.addEventListener('choice', () => {
			group.value ? getMatieres(document.getElementById('group').value) : uniteValeur.append(initOption);
		})

		function addMatieres() {
			cleanOptions();
			uniteValeur.value = null;
			data.forEach(matiere => addMatiere(matiere));
		}

		function cleanOptions() {
			[...uniteValeur.options].forEach(option => {
				uniteValeur.removeChild(option);
			});
		}

		function addMatiere(matiere) {
			let option = document.createElement('option');
			option.setAttribute('value', matiere.slug);
			option.innerText = matiere.nom;
			uniteValeur.appendChild(option);
		}

		document.getElementById('published').addEventListener('change', evt => {
			evt.preventDefault();
			if(evt.target.checked) {
				evt.preventDefault();
				Swal.fire({
					icon: 'info',
					showCancelButton: true,
					confirmButtonText: "Publier",
					cancelButtonText: 'Ne pas publier',
					html: `Si vous cochez cette case, une notification sera envoyée à tous les étudiants de
					<b>${group.options[group.selectedIndex].innerText}</b> et l'évaluation sera ajoutée à leur emploi du temps.`
				}).then( result => {
					evt.target.checked = result.isConfirmed;
				});
			}
		})
	</script>
@endsection