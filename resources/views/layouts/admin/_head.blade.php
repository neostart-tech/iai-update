<title>{{ config('app.name') }} - {{ $title ?? 'Blank title' }}</title>
<!-- [Meta] -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="description"
			content="">
<meta name="keywords"
			content="Bootstrap admin template, Dashboard UI Kit, Dashboard Template, Backend Panel, react dashboard, angular dashboard">
<meta name="author" content="Phoenixcoded">

<!-- [Favicon] icon -->
<link rel="icon" href="https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg" type="image/x-icon">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">

@yield('other-css')

<link rel="stylesheet" href="{{ asset('admin/assets/fonts/inter/inter.css') }}" id="main-font-link"/>
<!-- [Tabler Icons] https://tablericons.com -->
<link rel="stylesheet" href="{{ asset('admin/assets/fonts/tabler-icons.min.css') }}">
<!-- [Feather Icons] https://feathericons.com -->
<link rel="stylesheet" href="{{ asset('admin/assets/fonts/feather.css') }}">
<!-- [Font Awesome Icons] https://fontawesome.com/icons -->
<link rel="stylesheet" href="{{ asset('admin/assets/fonts/fontawesome.css') }}">
<!-- [Material Icons] https://fonts.google.com/icons -->
<link rel="stylesheet" href="{{ asset('admin/assets/fonts/material.css') }}">
<!-- [Template CSS Files] -->
<link rel="stylesheet" href="{{ asset('admin/assets/css/style.css') }}" id="main-style-link">
<link rel="stylesheet" href="{{ asset('admin/assets/css/style-preset.css') }}">
<link rel="stylesheet" href="{{ asset('tel/build/css/intlTelInput.css') }}">
<link href="//fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href='//fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700' rel='stylesheet'>

<style>
	.flashy {
			font-family: "Source Sans Pro", Arial, sans-serif;
			padding: 11px 30px;
			border-radius: 4px;
			font-weight: 400;
			position: fixed;    
			z-index: 99999999;
			height: 50px;
			top: 20px;
			right: 20px;
			font-size: 16px;
			color: #fff;
	}
</style>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-14K1GBX9FG"></script>
	<script>
		window.dataLayer = window.dataLayer || [];

		function gtag() {
			dataLayer.push(arguments);
		}

		gtag('js', new Date());

		gtag('config', 'G-14K1GBX9FG');
	</script>
