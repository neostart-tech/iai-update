@extends('auth.base', [
	'title' => 'Réinitialiser mon mot de passe'
])

@section('form')
	<form action="{{ route('password.store') }}" method="post">
		<input type="hidden" name="email" value="{{ $email }}">
		<input type="hidden" name="token" value="{{ $token }}">
		@csrf
		<div class="form-group mb-3">
			<x-forms.label for="password" content="Mot de passe"/>
			<input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="password">
			{!! errorAlert($errors->first('password')) !!}
		</div>
		<div class="form-group mb-3">
			<x-forms.label for="password_confirm" content="Confirmation du mot de passe"/>
			<input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" id="password_confirm"
						 placeholder="">
		</div>
		<div class="d-grid mt-4">
			<button type="submit" class="btn custom-yellow">Réinitialiser mon mot de passe</button>
		</div>
	</form>
@endsection

@section('form-head')
	<div class="mb-4">
		<h3 class="mb-2"><b> Réinitialiser votre mot de passe</b></h3>
		<p class="text-muted text-center">Veuillez définir votre nouveau mot de passe</p>
	</div>
@endsection
