@extends('auth.etudiants.base', [
	'title' => 'Me connecter'
])

@section('content')
	<form action="{{ route('officiel.login') }}" method="post">
		@csrf
		<div class="text-center mb-3">
			<a href="{{ route('home') }}" class="b-brand mx-auto">
				<img src="{{Storage::url(AppGetters::getAppLogo()) ? Storage::url(AppGetters::getAppLogo()) : 'https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg'}}"
						 style="max-height: 40px; max-width: 85px" class="img-fluid logo-lg" alt="logo">
				<span class="text-warning text-2xl" style="letter-spacing: 5px; padding-top: 15px; font-weight: bold">&nbsp;&nbsp;&nbsp;{{ config('app.name') }}</span>
			</a>
			
		</div>
		<h4 class="text-center f-w-500 mb-3">Entrez vos identifiants pour vous accéder à votre espace</h4>
		@if($errors->has('message'))
			<h5 class="text-danger danger">{{ $errors->first('message') }}</h5>
		@endif
		<div class="form-group mb-3 my-3">
			<label class="form-label" for="email">Email
				<x-forms.required-field/>
			</label>
			<input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}"
						 placeholder="Adresse mail">
			{!! errorAlert($errors->first('email')) !!}
		</div>
		<div class="form-group mb-3">
			<label class="form-label" for="password">Mot de passe
				<x-forms.required-field/>
			</label>
			<input type="password" class="form-control" id="password" name="password"
						 placeholder="Mot de passe">
			{!! errorAlert($errors->first('password')) !!}
		</div>
		<div class="d-flex mt-1 justify-content-between align-items-center">
			<div class="form-check">
				<input class="form-check-input input-success" name="remember" type="checkbox" id="remember" checked="">
				<label class="form-check-label text-muted" for="remember">Remember me?</label>
			</div>
			<a href="{{ route('officiel.password.request') }}">
				<h6 class="f-w-400 mb-0" style="color: #155e31">Mot de passe oublié?</h6>
			</a>
		</div>
		<div class="d-grid mt-4">
			<button type="submit" class="btn custom-yellow">Me connecter</button>
		</div>
		<div class="d-flex justify-content-between align-items-end mt-4">
			<h6 class="f-w-500 mb-0" style="color: #155e31">Je n'ai pas encore de compte ?</h6>
			<a href="{{ route('candidatures.create') }}" style="color: #155e31">Me créer un compte</a>
		</div>
	</form>
@endsection
