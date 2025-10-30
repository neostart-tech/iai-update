@extends('base', [
	'title' => 'Changer mon mot de passe',
	'breadcrumbs' => [
		'Mon compte',
		'Changer mon mot de passe'
	],
	'page_name' => 'Changer mon mot de passe'
])

@section('content')
		<div class="card">
			<div class="card-header">
				<h5>Changer mon mot de passe</h5>
			</div>
			<form action="{{ route('my-space.update-password') }}" method="post" id="change-password-form">
				@csrf
				<div class="card-body">
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<label class="form-label">Ancien mot de passe</label>
								<input type="password" class="form-control" name="old_password" required>
								{!! errorAlert($errors->first('old_password')) !!}
							</div>
							<div class="form-group">
								<label class="form-label">Nouveau mot de passe</label>
								<input type="password" class="form-control" id="new_password" name="new_password" required>
								{!! errorAlert($errors->first('new_password')) !!}
							</div>
							<div class="form-group">
								<label class="form-label">Confirmer le nouveau mot de passe</label>
								<input type="password" class="form-control" name="new_password_confirmation" required>
								{!! errorAlert($errors->first('new_password')) !!}
							</div>
						</div>
						<div class="col-sm-6">
							<h5>Le nouveau mot de passe doit contenir :</h5>
							<ul class="list-group list-group-flush">
								<li class="list-group-item"><i class="ti ti-circle-x text-danger f-16 me-2" id="min-char"></i> Au moins
									8
									caractères
								</li>
								<li class="list-group-item"><i class="ti ti-circle-x text-danger f-16 me-2" id="lower-char"></i> Au
									moins
									1 lettre
									minuscule (a-z)
								</li>
								<li class="list-group-item"><i class="ti ti-circle-x text-danger f-16 me-2" id="upper-char"></i> Au
									moins
									1 lettre
									majuscule (A-Z)
								</li>
								<li class="list-group-item"><i class="ti ti-circle-x text-danger f-16 me-2" id="number-char"></i> Au
									moins
									1 chiffre
									(0-9)
								</li>
								<li class="list-group-item"><i class="ti ti-circle-x text-danger f-16 me-2" id="special-char"></i> Au
									moins 1 caractère
									spécial
								</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="card-footer text-end btn-page">
					<button class="btn btn-outline-secondary" type="reset">Annuler</button>
					<button class="btn btn-primary" type="submit" id="submit-button">Valider</button>
				</div>
			</form>
		</div>
@endsection

@section('other-js')
	<script>
		let valideForm = false;

		const validatePassword = event => {
			let password = event.target.value;
			const minLength = 8;
			const lowerCasePattern = /[a-z]/;
			const upperCasePattern = /[A-Z]/;
			const numberPattern = /[0-9]/;
			const specialCharPattern = /[!@#$%^&*(),.?":{}|<>]/;

			let minLengthCondition, lowerChar, upperChar, numberChar, specialChar = false;

			document.getElementById('min-char').setAttribute('class', (minLengthCondition = password.length < minLength) ? 'ti ti-circle-x text-danger f-16 me-2' : 'ti f-16 me-2 ti-circle-check text-success');
			document.getElementById('lower-char').setAttribute('class', (lowerChar = !lowerCasePattern.test(password)) ? 'ti ti-circle-x text-danger f-16 me-2' : 'ti f-16 me-2 ti-circle-check text-success');
			document.getElementById('upper-char').setAttribute('class', (upperChar = !upperCasePattern.test(password)) ? 'ti ti-circle-x text-danger f-16 me-2' : 'ti f-16 me-2 ti-circle-check text-success');
			document.getElementById('number-char').setAttribute('class', (numberChar = !numberPattern.test(password)) ? 'ti ti-circle-x text-danger f-16 me-2' : 'ti f-16 me-2 ti-circle-check text-success');
			document.getElementById('special-char').setAttribute('class', (specialChar = !specialCharPattern.test(password)) ? 'ti ti-circle-x text-danger f-16 me-2' : 'ti f-16 me-2 ti-circle-check text-success');

			if (!minLengthCondition && !lowerChar && !upperChar && !numberChar && !specialChar) {
				valideForm = true;
			}
		}

		document.getElementById('new_password').addEventListener('input', validatePassword)
		document.getElementById('submit-button').addEventListener('click', event => {
			event.preventDefault();
			valideForm && document.getElementById('change-password-form').submit();
		})

	</script>

@endsection