@extends('auth.etudiants.base', ['title' => 'Me connecter'])
@section('content')

	<form action="{{ route('enseignant.auth.store') }}" method="post">
		@csrf
		<div class="text-center mb-3">
			<a href="{{ route('home') }}" class="b-brand mx-auto">
				<img src="{{Storage::url(AppGetters::getAppLogo())}}"
						 style="max-height: 40px; max-width: 85px" class="img-fluid logo-lg" alt="logo">
			</a>
		</div>
		<h4 class="text-center f-w-500 mb-3">Entrez vos identifiants pour vous accéder à votre espace enseignant</h4>
		@if($errors->has('message'))
			<h5 class="text-danger danger">{{ $errors->first('message') }}</h5>
		@endif
		<span class="mb-3 text-success text-center">{{ session()->pull('status') }}</span>
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
				<input class="form-check-input input-success" type="checkbox" name="remember" id="customCheckc1" checked="">
				<label class="form-check-label text-muted" for="customCheckc1">Se souvenir de moi ?</label>
			</div>
			{{-- <a href="{{ route('etudiants.password.request') }}">
				<h6 class="f-w-400 mb-0" style="color: #155e31">
					Mot de passe oublié ?
				</h6>
			</a> --}}
		</div>
		<div class="d-grid mt-4">
			<button type="submit" class="btn custom-yellow">Me connecter</button>
		</div>
{{--		<div class="d-flex justify-content-between align-items-end mt-4">--}}
{{--			<h6 class="f-w-500 mb-0" style="color: #155e31">Je n'ai pas encore de compte ?</h6>--}}
{{--			<a href="{{ route('candidatures.create') }}" style="color: #155e31">Me créer un compte</a>--}}
{{--		</div>--}}
	</form>
@endsection