@extends('base', $metaData)

@section('content')
	@includeWhen($viewContent == '_simple-candidatures', 'admin.candidatures._simple-candidatures')
	@includeWhen($viewContent == '_payement-validation', 'admin.candidatures._payement-validation')
	@includeWhen($viewContent == '_presence-validation', 'admin.candidatures._presence-validation')
	@includeWhen($viewContent == '_admission-validation', 'admin.candidatures._admission-validation')
	@includeWhen($viewContent == '_liste-admis', 'admin.candidatures._liste-admis')
@endsection

@section('other-css')
	<link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection

@section('other-js')
	<script>
		let admisList = [];
		let recaleList = [];
		const token = '{{ csrf_token() }}'

		function manageListSubmission() {
			const admis = document.getElementsByClassName('admis');
			const recales = document.getElementsByClassName('recale');

			[...admis].forEach(candidat => {
				if (candidat.checked)
					admisList.push(candidat.value)
			});

			[...recales].forEach(candidat => {
				if (candidat.checked)
					recaleList.push(candidat.value);
			});

			if (admisList.length === 0 && recaleList.length === 0) {
				notification.error('La liste ne peut être vide');
				return;
			}
			document.getElementById('recales').value = recaleList;
			document.getElementById('admis').value = admisList;

			Swal.fire({
				icon: 'info',
				showCancelButton: true,
				confirmButtonText: "Oui, confirmer",
				cancelButtonText: 'Annuler',
				html: `En cliquant sur oui, vous appliquez la décision d'admission et de recalage sur ces candidats et vous faites d'eux de nouveaux étudiants`
			}).then(result => {
				result.isConfirmed && document.getElementById('admission-form').submit();
			});
		}

		function handlePresenceFormSubmit() {
			const absents = document.getElementsByClassName('absents');
			let absentsList = [];

			[...absents].forEach(candidat => {
				if (candidat.checked)
					absentsList.push(candidat.value)
			});

			let timerInterval;
			Swal.fire({
				icon: 'info',
				showCancelButton: true,
				confirmButtonText: "Oui, confirmer",
				cancelButtonText: 'Annuler',
				html: `En cliquant sur oui, vous marquez ${absentsList.length} comme absents et ${absents.length - absentsList.length} comme présents.`
			}).then(result => {
				if (result.isConfirmed) {

					Swal.fire({
						title: "Traitement de la liste de présence en cours",
						html: "Cela peut prendre quelques instants",
						timerProgressBar: true,
						timer: 8000,
						didOpen: () => {
							Swal.showLoading();
							timerInterval = setInterval(() => {
							}, 100);
						},
						willClose: () => {
							clearInterval(timerInterval);
						}
					}).then((result) => {
						if (result.dismiss === Swal.DismissReason.timer) {
						}
					});

					$.post(
						document.getElementById('presence-form').getAttribute('action'),
						{
							_token: document.getElementsByName('_token')[0].value,
							candidats: absentsList
						}
					).then(() => {
						Swal.fire({
							title: "Opération terminée avec succès!",
							text: "Liste de présence soumise avec succès",
							icon: "success"
						}).then(() => window.location.href = window.location.href);
					}).catch(error => {
						console.log('error: ', error);
						Swal.fire({
							icon: 'error',
							title: 'Désolé, une erreur est survenue durant l\'opération.'
						})
					});
				}
			});
		}

		function handlePayeFormSubmit() {
			let payeList = 0;
			[...document.getElementsByName('paye[]')].forEach(element => {
				if (element.checked) {
					payeList += 1;
				}
			});
			if (payeList <= 0)
				notification.error('La sélection ne doit pas être vide');
			else {
				const slot = payeList === 1 ? 'l\'étudiant.e' : `les ${payeList} étudiants`;
				Swal.fire({
					icon: 'info',
					showCancelButton: true,
					confirmButtonText: "Oui, confirmer",
					cancelButtonText: 'Annuler',
					html: `En cliquant sur oui, vous confirmez que ${slot} que vous avez choisi ont payé leur frais de participation au concours d'entrée .`
				}).then(result => {
					result.isConfirmed && document.getElementById('paye-form').submit();
				});
			}
		}
	</script>
	@include('layouts.admin._dt-scripts')
@endsection
