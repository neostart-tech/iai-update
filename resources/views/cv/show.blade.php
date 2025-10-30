@extends('base', [
	'title' => 'Mon Cv',
	'page_name' => 'Mon Curriculum Vitae',
	'breadcrumbs' => [
		'Mon dossier',
		'Mon Curriculum Vitae'
	]
])

@section('content')
	<div class="card">
		<div class="card-header text-end gap-5">
			<a class="btn btn-primary" href="#" onclick="alertToStudent('{{ route('etudiants.cv.convert') }}')">
				<i class="fa fa-file-pdf"></i> Convertir en PDF
			</a>
			<a class="btn btn-primary" href="{{ route('etudiants.cv.edit') }}">
				<i class="fa fa-edit"></i> Éditer
			</a>
		</div>
		<div class="card-body">
			<div class="form-group">
				<div class="form-control" id="content">{!!  $content ?? 'CV Pas encore rédigé' !!}</div>
			</div>
		</div>
	</div>
@endsection

@section('other-js')
	<script>
		const fileUrl = '{{ route('etudiants.my-space.my-files') }}';

		function alertToStudent(url) {
			Swal.fire({
				icon: 'info',
				title: 'Conversion du CV en PDF',
				html: 'La conversion du CV en PDF de votre CV peut prendre du temps. <br> Rendez-vous sur "Mes fichiers" pour voir le fichier pdf généré.'
			}).then(result => {
				result.isConfirmed && (document.location.href = url);
			});
		}
	</script>
@endsection

