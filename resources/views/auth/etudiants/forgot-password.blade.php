@extends('auth.etudiants.base', ['title' => 'Mot de passe oublié'])
@section('content')
	<form action="#" method="post">
		@csrf
		<div class="text-center mb-3">
			<a href="{{ route('home') }}" class="b-brand mx-auto">
				<img src="{{Storage::url(AppGetters::getAppLogo()) ? Storage::url(AppGetters::getAppLogo()) : 'https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg'}}"
						 style="max-height: 40px; max-width: 85px" class="img-fluid logo-lg" alt="logo">
				<span class="text-warning text-2xl" style="letter-spacing: 5px; padding-top: 15px; font-weight: bold">&nbsp;&nbsp;&nbsp;{{ AppGetters::getAppName() ? AppGetters::getAppName() : "Laravel" }}</span>
			</a>
		</div>
		<div class="d-flex justify-content-between align-items-end mb-4">
			<h3 class="mb-0"><b>Mot de passe oublié</b></h3>
			<a href="{{ route('etudiants.auth.login') }}" class="link-warning" style="color: #e0cb2b;">Me connecter</a>
		</div>
		<span class="mb-3 text-success text-center">{{ session()->get('status') }}</span>
		<div class="form-group mb-3">
			<label class="form-label" for="email">Votre adresse email</label>
			<input type="email" class="form-control input-yellow @error('email') is-invalid @enderror"
						 name="email" id="email" placeholder="Adresse email" value="{{ old('email') }}">
			{!! errorAlert($errors->first('email')) !!}
		</div>
		<p class="mt-4 text-sm text-muted">N'oubliez pas de vérifier dans vos SPAM</p>
		<div class="d-grid mt-3">
			<button type="submit" class="btn custom-yellow">Envoyer le mail de réinitialisation de mot de passe</button>
		</div>
	</form>
@endsection
