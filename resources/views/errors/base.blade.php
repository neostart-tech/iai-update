<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
	@include('layouts.admin._head', compact('title'))
</head>

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme_contrast=""
			data-pc-theme="light">
<div class="loader-bg">
	<div class="loader-track">
		<div class="loader-fill"></div>
	</div>
</div>
<div class="maintenance-block">
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<div class="card error-card">
					<div class="card-body">
						<div class="error-image-block">
							<div class="row justify-content-center">
								<div class="col-10">
									<img class="img-fluid" src="{{ asset('images/errors/403.png') }}"
											 style="max-width: 325px; max-height: 329px" alt="403 error">
								</div>

							</div>
						</div>
						<div class="text-center">
							<h1 class="mt-4"><b>Vous n'êtes pas autorisé à accéder à cette ressource!</b></h1>
							<p class="mt-2 mb-4 text-sm text-muted">Ce message signifie que vous essayez d'accéder à une ressource à
								laquelle vous n'avez normalement pas accès. Le bouton ci-dessous vous permettra de revenir à votre
								espace de navigation. <br> En cas d'erreur, veuillez en informer les chargés de maintenance du site.</p>
							<button type="button" onclick="history.go(-1);" class="btn btn-primary mb-3">Revenir dans mon espace
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@include('layouts._scripts')

</body>
</html>
