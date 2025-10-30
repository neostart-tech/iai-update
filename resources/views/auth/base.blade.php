@php use Illuminate\Support\Str; @endphp
	<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<!-- [Head] start -->

<head>
	@include('layouts.admin._head')
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
<!-- [Head] end -->
<!-- [Body] Start -->

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme_contrast=""
			data-pc-theme="light">

<div class="auth-main">
	<div class="auth-wrapper v1">
		<div class="auth-form">
			<div class="card my-5">
				<div class="card-body">
					<div class=" mb-3 text-center">
						<div class="text-center mb-3">
							<a href="{{ route('home') }}" class="b-brand mx-auto">
								<img src="{{Storage::url(AppGetters::getAppLogo()) ? Storage::url(AppGetters::getAppLogo()) : 'https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg'}}"
										 style="max-height: 40px; max-width: 85px" class="img-fluid logo-lg" alt="logo">
								<span class="text-warning text-2xl" style="letter-spacing: 5px; padding-top: 15px; font-weight: bold">&nbsp;&nbsp;&nbsp;{{AppGetters::getAppName() ? AppGetters::getAppName() : config('app.name') }}</span>
							</a>
						</div>
					</div>
					@yield('form-head')
					@yield('form')
				</div>
			</div>
		</div>
	</div>
</div>
<!-- [ Main Content ] end -->
<!-- Required Js -->
@include('layouts._scripts')


</body>
<!-- [Body] end -->

</html>
