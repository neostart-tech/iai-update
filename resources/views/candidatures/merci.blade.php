<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Dossier déposé — {{ config('app.name') }}</title>
	@php
		$logoPath = \App\Helpers\ConfigHelper::getAppLogo();
		$fallbackLogo = 'https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg';
		$logoUrl = $logoPath && Storage::disk('public')->exists($logoPath) ? Storage::url($logoPath) : $fallbackLogo;
	@endphp
	<link rel="icon" href="{{ $logoUrl }}" type="image/x-icon">
	@include('candidatures._styles')
</head>
<body class="depot-body confirm-body">

<div class="confirm-page">
	<header class="confirm-masthead">
		<a href="{{ route('home') }}" class="depot-brand">
			<img src="{{ $logoUrl }}" class="depot-logo" alt="Logo IAI-Togo">
			<span>
				<strong>IAI-TOGO</strong>
				<small>Institut Africain d'Informatique</small>
			</span>
		</a>
	</header>

	<div class="confirm-card">
		<div class="confirm-badge-ring" aria-hidden="true">
			<div class="confirm-badge">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
			</div>
		</div>

		<p class="panel-kicker confirm-kicker">Dossier reçu</p>
		<h1 class="confirm-title">
			Merci{{ $prenom ? ' ' . $prenom : '' }}, votre candidature <span>a été déposée</span>
		</h1>
		<p class="confirm-lede">
			@if($email)
				Un email de confirmation contenant vos identifiants a été envoyé à <strong>{{ $email }}</strong>.
			@else
				Un email de confirmation contenant vos identifiants vous a été envoyé.
			@endif
		</p>

		<div class="confirm-divider"><span>Prochaines étapes</span></div>

		<ol class="confirm-steps">
			<li>
				<span class="confirm-step-num">1</span>
				<div class="confirm-step-body">
					<strong>Étude de votre dossier</strong>
					<p>La commission d'admission examine les informations et les pièces que vous avez transmises.</p>
				</div>
			</li>
			<li>
				<span class="confirm-step-num">2</span>
				<div class="confirm-step-body">
					<strong>Notification par email</strong>
					<p>Vous serez informé(e) par email à chaque étape importante du traitement de votre candidature.</p>
				</div>
			</li>
			<li>
				<span class="confirm-step-num">3</span>
				<div class="confirm-step-body">
					<strong>Résultat de l'admission</strong>
					<p>La décision finale de la commission vous sera communiquée directement par email.</p>
				</div>
			</li>
		</ol>

		<a href="{{ route('home') }}" class="btn-refined btn-refined--primary confirm-cta">
			<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
			Retour à l'accueil
		</a>
	</div>

	<footer class="confirm-footer">
		<span>Une question ? Contactez le service des admissions de l'IAI-Togo.</span>
	</footer>
</div>

</body>
</html>
