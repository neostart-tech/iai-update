<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
	@include('layouts.admin._head', [$title])
	<style>
		.custom-yellow {
			background-color: #fdf296;
			color: #155e31;
		}

		.custom-yellow:hover {
			background-color: #155e31;
			color: #e0cb2b;
		}

		style.custom-yellow:target {
			background-color: #155e31;
			border-color: #fdf296;
		}

		.input-yellow {
			border-color: #e0cb2b;
		}
	</style>
</head>

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme_contrast=""
			data-pc-theme="light">
<div class="loader-bg">
	<div class="loader-track">
		<div class="loader-fill"></div>
	</div>
</div>

<div class="auth-main">
	<div class="auth-wrapper v2">
		<div class="auth-form">
			<div class="card my-5">
				<div class="card-body">
					@yield('content')
				</div>
			</div>
		</div>
		<div class="auth-sidecontent" style="background-color: #155e31">
			<img src="{{ asset(config('images.login.officiel')) }}" alt="images"
					 class="img-fluid img-auth-side">
		</div>
	</div>
</div>
@include('layouts._scripts')

</body>

</html>