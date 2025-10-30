@extends('auth.base', ['title' => 'Mot de passe oublié'])

@section('form')
	<form action="{{ route('password.email') }}" method="post">
		@csrf
		<span class="mb-3 text-success text-center">{{ session()->get('status') }}</span>
		<div class="form-group mb-3">
			<x-forms.label for="email" content="Votre adresse email"/>
			<input type="email" class="form-control" name="email" id="email" placeholder="Votre adresse mail" required>
			{!! errorAlert($errors->first('email')) !!}
		</div>
		<p class="mt-4 text-sm text-muted">N'oubliez pas de vérifier dans vos SPAM.</p>
		<div class="d-grid mt-3">
			<button type="submit" class="btn btn-primary">Envoyer le mail de réinitialisation de mot de passe</button>
		</div>
	</form>
@endsection

@section('form-head')
	<div class="d-flex justify-content-between align-items-end mb-4">
		<h3 class="mb-0"><b>Mot de passe oublié</b></h3>
		<a href="{{ route('login') }}" style="color: #e0cb2b;">Page de connexion</a>
	</div>
@endsection
