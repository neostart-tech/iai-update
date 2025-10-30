<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
	@include('layouts.admin._head', ['title' => "Page en Construction"])
</head>

<body data-pc-preset="preset-7" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme_contrast=""
			data-pc-theme="light">

<div class="loader-bg">
	<div class="loader-track">
		<div class="loader-fill"></div>
	</div>
</div>

<div class="maintenance-block construction-card-2">
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<div class="card construction-card">
					<div class="card-body">
						<div class="construction-image-block">
							<div class="row justify-content-center align-items-center construction-card-bottom">
								<div class="col-md-6">
									<div class="text-center">
										<h1 class="mt-4"><b>Page en Construction</b></h1>
										<p class="mt-4 text-muted">Nous travaillons actuellement sur cette section de notre site pour vous
											offrir une expérience encore meilleure. Revenez bientôt pour découvrir nos nouvelles
											fonctionnalités et contenus. <br> En attendant, vous pouvez explorer les autres sections de notre
											site ou nous contacter pour toute question ou besoin d'assistance. <br>
											Nous vous remercions pour votre compréhension et votre patience.</p>
										<button onclick="history.back();" type="button" class="btn btn-primary mb-3">Revenir en arrière
										</button>
									</div>
								</div>
								<div class="col-md-6">
									<img class="img-fluid" src="{{asset('admin/assets/images/pages/img-cunstruct-1.svg')}}" alt="img">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@include('layouts._theme-script')
{{--TODO appliquer le thème de l'utilisateur à la page--}}
</body>
</html>
