@extends('auth.base', [
	'title' => 'Page de connection',
	'pageTitle' => 'Me connecter'
])

@section('form')
	<form action="{{ route('login') }}" method="post">
		@csrf
		<span class="mb-3 text-success text-center">{{ session()->pull('status') }}</span>
		<div class="form-group mb-3">
			<x-forms.label for="email" content="Adresse email"/>
			<input type="email" class="form-control" id="email" name="email" placeholder="Votre adresse mail">
			{!! errorAlert($errors->first('email')) !!}
		</div>
		<div class="form-group mb-3">
			<x-forms.label for="mot-de-passe" content="Mot de passe"/>
			<input type="password" class="form-control" id="mot-de-passe" name="password" placeholder="Mot de passe">
			{!! errorAlert($errors->first('password')) !!}
		</div>
		<div class="d-flex mt-1 justify-content-between align-items-center">
			<div class="form-check">
				<input class="form-check-input input-primary" name="remember" type="checkbox" id="remember" checked="">
				<label class="form-check-label text-muted" for="remember">Se souvenir de moi</label>
			</div>
			<a href="{{ route('password.request') }}">
				<h6 class="text-secondary f-w-400 mb-0">J'ai oublié mon mot de passe</h6>
			</a>
		</div>
		<div class="d-grid mt-4">
			<button type="submit" class="btn btn-primary">Me connecter</button>
		</div>
	</form>
@endsection

@section('form-head')
	<div class="d-flex justify-content-between align-items-end mb-4">
		<h3 class="mb-0 text-center"><b>Page de connexion</b></h3>
	</div>
	<div class="text-danger">{{ Str::ucfirst($errors->first()) }}</div>
@endsection
