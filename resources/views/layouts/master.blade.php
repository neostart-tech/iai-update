<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>IAI - Institut Africain d'Informatique</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<script src="https://unpkg.com/flowbite@1.5.3/dist/flowbite.js" async></script>
	<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.7.3/dist/alpine.min.js" async></script>

	<link rel="prefetch" href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" as="style"
				onload="this.onload=null;this.rel='stylesheet'">
	<noscript>
		<link rel="stylesheet" href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css">
	</noscript>

	<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.js" defer></script>

	<style>
		[x-cloak] {
			display: none;
		}

		[type="checkbox"] {
			box-sizing: border-box;
			padding: 0;
		}

		.form-checkbox,
		.form-radio {
			-webkit-appearance: none;
			-moz-appearance: none;
			appearance: none;
			-webkit-print-color-adjust: exact;
			color-adjust: exact;
			display: inline-block;
			vertical-align: middle;
			background-origin: border-box;
			-webkit-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
			user-select: none;
			flex-shrink: 0;
			color: currentColor;
			background-color: #fff;
			border-color: #e2e8f0;
			border-width: 1px;
			height: 1.4em;
			width: 1.4em;
		}

		.form-checkbox {
			border-radius: 0.25rem;
		}

		.form-radio {
			border-radius: 50%;
		}

		.form-checkbox:checked {
			background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M5.707 7.293a1 1 0 0 0-1.414 1.414l2 2a1 1 0 0 0 1.414 0l4-4a1 1 0 0 0-1.414-1.414L7 8.586 5.707 7.293z'/%3e%3c/svg%3e");
			border-color: transparent;
			background-color: currentColor;
			background-size: 100% 100%;
			background-position: center;
			background-repeat: no-repeat;
		}

		.form-radio:checked {
			background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3ccircle cx='8' cy='8' r='3'/%3e%3c/svg%3e");
			border-color: transparent;
			background-color: currentColor;
			background-size: 100% 100%;
			background-position: center;
			background-repeat: no-repeat;
		}
	</style>

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"/>

	<link href="/js/app.js" rel="stylesheet">

	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
	<link href="{{ asset('css/aos.css') }}" rel="stylesheet">
	<link rel="icon" type="image/x-icon" href="https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg">

	@yield('other-css')


	<style>
		@keyframes path0 {
			0% {
				transform: rotate(-10deg);
			}
			100% {
				transform: rotate(10deg);
			}
		}

		@keyframes path1 {
			0% {
				transform: rotate(-30deg);
			}
			100% {
				transform: rotate(30deg);
			}
		}

		@keyframes path2 {
			0% {
				transform: rotate(40deg);
			}
			100% {
				transform: rotate(-40deg);
			}
		}

		html {
			scroll-behavior: smooth;
		}

		.line-clamp-2 {
			overflow: hidden;
			text-overflow: ellipsis;
			-webkit-box-orient: vertical;
			display: block;
			display: -webkit-box;
			-webkit-line-clamp: 2;
		}

		.wrapper {
			display: grid;
			/*height:100vh;*/
			place-items: center
		}

		#btn-back-to-top {
			position: fixed;
			display: none;
		}

		[x-cloak] {
			display: none;
		}
		@yield('head')
	</style>
</head>

<!--Start of Tawk.to Script-->
<script type="text/javascript" async>
	let Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
	(function () {
		var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
		s1.async = true;
		s1.src = 'https://embed.tawk.to/63bb3a0b47425128790c57f0/1gm9lli1u';
		s1.charset = 'UTF-8';
		s1.setAttribute('crossorigin', '*');
		s0.parentNode.insertBefore(s1, s0);
	})();
</script>
<!--End of Tawk.to Script-->

<body class="bg-white w-full relative overflow-x-hidden">


<header class="w-full sticky top-0" style="z-index: 998">
	@include('partials.head')
</header>


<!--    hero-->
@yield('content')

<!--    Footer-->
@include('partials.footer')


<script>
	import('preline')

	// Get the button
	let mybutton = document.getElementById("btn-back-to-top");

	// When the user scrolls down 20px from the top of the document, show the button
	window.onscroll = function () {
		scrollFunction();
	};

	function scrollFunction() {
		if (
			document.body.scrollTop > 20 ||
			document.documentElement.scrollTop > 20
		) {
			mybutton.style.display = "block";
		} else {
			mybutton.style.display = "none";
		}
	}

	// When the user clicks on the button, scroll to the top of the document
	mybutton.addEventListener("click", () => {
		document.body.scrollTop = 0;
		document.documentElement.scrollTop = 0;
	});

	@yield('other-js')

</script>
    @stack("stepForm")
    
    @yield('scripts')
    </body>


</html>
