@extends('auth.etudiants.base', ['title' => 'Réinitialiser votre mot de passe'])
@section('content')
	<form action="{{ route('etudiants.password.store') }}" method="post">
		@csrf
		<input type="hidden" name="email" hidden="" value="{{ $email }}">
		<input type="hidden" name="token" hidden="" value="{{ $token }}">
		<div class="text-center mb-3">
			<a href="{{ route('home') }}" class="b-brand mx-auto">
				<img src="  {{Storage::url(AppGetters::getAppLogo()) ? Storage::url(AppGetters::getAppLogo()) : 'https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg' }}"
						 style="max-height: 40px; max-width: 85px" class="img-fluid logo-lg" alt="logo">
				<span class="text-warning text-2xl" style="letter-spacing: 5px; padding-top: 15px; font-weight: bold">&nbsp;&nbsp;&nbsp;{{ config('app.name') }}</span>
			</a>
		</div>

		<div class="mb-4">
			<h3 class="mb-2"><b>Réinitialiser votre mot de passe</b></h3>
			<p class="text-muted">Veuillez définir votre nouveau mot de passe</p>
		</div>
		<span class="mb-3 text-success text-center">{{ session()->get('status') }}</span>
		<span class="mb-3 text-danger text-center">{{ collect($errors->all())->first() }}</span>
		<div class="form-group mb-3">
			<x-forms.label for="password" content="Mot de passe"/>
			<input type="password" class="form-control" id="password" name="password">
			{!! errorAlert($errors->first('password')) !!}
		</div>
		<div class="form-group mb-3">
			<x-forms.label for="confirm_password" content="Confirmez votre nouveau mot de passe"/>
			<input type="password" class="form-control" id="confirm_password" name="password_confirmation">
		</div>
		<div class="d-grid mt-4">
			<button type="submit" class="btn custom-yellow">Réinitialiser mon mot de passe</button>
		</div>
	</form>
@endsection