@extends('base', [
	'title' => 'Éditer une évaluation',
	'breadcrumbs' => ['Administration', ['text' => 'Évaluations', 'url' => route('admin.evaluations.index')], 'Éditer une évaluation'],
	'page_name' => 'Éditer une évaluation'
])

@section('content')
	@include('admin.evaluations._form', [
		'action' => route('admin.evaluations.update', $evaluation),
		'method' => true
	])

	<hr class="my-4"/>
	<div class="card">
		<div class="card-header"><h5 class="mb-0">Répartition des salles et surveillants</h5></div>
		<div class="card-body">
			<div class="row g-3 align-items-end">
				<div class="col-md-6">
					<label class="form-label">Salles</label>
					<select class="form-select" multiple id="room_ids">
						@foreach($salles as $s)
							<option value="{{ $s->id }}">{{ $s->nom }} (cap: {{ $s->effectif }})</option>
						@endforeach
					</select>
					<small class="text-muted">Sélectionnez 1+ salles. La répartition est aléatoire et par capacité.</small>
				</div>
				<div class="col-md-3">
					<label class="form-label">Limite par salle (optionnel)</label>
					<input type="number" min="1" class="form-control" id="limit_per_room" placeholder="ex: 30" />
				</div>
				<div class="col-md-3 text-end">
					<button class="btn btn-primary" id="btn-allocate">Répartir automatiquement</button>
					<button class="btn btn-outline-danger" id="btn-reset">Réinitialiser</button>
				</div>
			</div>

			<hr/>
			<div class="d-flex justify-content-between align-items-center">
				<div id="allocation-summary"></div>
				<a class="btn btn-outline-secondary ms-3" href="{{ route('admin.evaluations.rooms.export', $evaluation) }}">Exporter CSV</a>
			</div>
		</div>
	</div>

	@push('scripts')
	<script>
		const routes = {
			allocate: "{{ route('admin.evaluations.rooms.allocate', $evaluation) }}",
			reset: "{{ route('admin.evaluations.rooms.reset', $evaluation) }}",
			summary: "{{ route('admin.evaluations.rooms.summary', $evaluation) }}",
			setSupervisors: (id) => "{{ route('admin.evaluations.rooms.set-supervisors', [$evaluation, ':id']) }}".replace(':id', id),
		};

		function fetchSummary() {
			fetch(routes.summary).then(r=>r.json()).then(data => {
				const wrap = document.getElementById('allocation-summary');
				if (!data.rooms?.length) { wrap.innerHTML = '<div class="text-muted">Aucune répartition pour le moment.</div>'; return; }
				wrap.innerHTML = data.rooms.map(r => `
					<div class="border rounded p-3 mb-3">
						<div class="d-flex justify-content-between">
							<strong>${r.salle}</strong>
							<span>Etudiants: ${r.students_count} / Capacité: ${r.capacity}</span>
						</div>
						<div class="mt-2">
							<label class="form-label">Surveillants (1–3)</label>
							<select class="form-select" multiple data-er="${r.id}">
								@foreach($enseignants as $u)
									<option value="{{ $u->id }}">{{ $u->nom }} {{ $u->prenom }}</option>
								@endforeach
							</select>
							<button class="btn btn-sm btn-outline-primary mt-2" data-save="${r.id}">Enregistrer les surveillants</button>
							<div class="small text-muted mt-1">Actuels: ${r.supervisors.join(', ') || 'Aucun'}</div>
						</div>
					</div>
				`).join('');

				[...wrap.querySelectorAll('button[data-save]')].forEach(btn => {
					btn.addEventListener('click', () => {
						const id = btn.getAttribute('data-save');
						const sel = wrap.querySelector(`select[data-er="${id}"]`);
						const vals = [...sel.options].filter(o => o.selected).map(o => o.value);
						if (vals.length === 0 || vals.length > 3) { alert('Choisir entre 1 et 3 surveillants'); return; }
						fetch(routes.setSupervisors(id), { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: JSON.stringify({user_ids: vals})})
							.then(()=> fetchSummary());
					});
				});
			});
		}

		document.getElementById('btn-allocate').addEventListener('click', () => {
			const roomIds = [...document.getElementById('room_ids').options].filter(o=>o.selected).map(o=>o.value);
			const limit = document.getElementById('limit_per_room').value || null;
			if (roomIds.length === 0) { alert('Sélectionnez au moins une salle'); return; }
			fetch(routes.allocate, { method:'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: JSON.stringify({ room_ids: roomIds, limit_per_room: limit }) })
				.then(()=> fetchSummary());
		});

		document.getElementById('btn-reset').addEventListener('click', () => {
			fetch(routes.reset, { method:'DELETE', headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}'} })
				.then(()=> fetchSummary());
		});

		fetchSummary();
	</script>
	@endpush
@endsection
