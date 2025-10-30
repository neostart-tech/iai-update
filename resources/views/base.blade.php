@php use App\Models\{Candidature,Etudiant}; @endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    @include('layouts.admin._head')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('tel/build/css/intlTelInput.css') }}">

</head>

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme_contrast=""
    data-pc-theme="light">

    @php
        $user = request()->user();
        $is_admin = false;
        $is_candidat = false;
        $is_daf = false;
       
        if ($is_etudiant = get_class($user) === App\Models\Etudiant::class) {
            $logoutFormId = 'etudiant-logout-form';
        } elseif ($is_candidat = get_class($user) === App\Models\Candidature::class) {
            $logoutFormId = 'officiel-logout-form';
        } 
         else {
            $is_admin = true;
            $logoutFormId = 'logout-form';
            // Vérifier si c'est un DAF
            $is_daf = $user && $user->roles && $user->roles->contains('nom', 'Directeur des Affaires Financières');
        }
    @endphp

    @include('layouts.admin._preloader')

    @include('layouts.admin._side-bar')

    @include('layouts.admin._nav-bar')

    @include('layouts.admin._announcement')

    <div class="pc-container">
        <div class="pc-content">
            @isset($breadcrumbs)
                <div class="page-header">
                    <div class="page-block">
                        <div class="row align-items-center">
                            <div class="col-md-12">
                                <ul class="breadcrumb">
                                    @foreach ($breadcrumbs as $breadcrumb)
                                        @if ($loop->last)
                                            <li class="breadcrumb-item" aria-current="page">{{ $breadcrumb }}</li>
                                        @else
                                            <li class="breadcrumb-item">
                                                <a
                                                    href="@isset($breadcrumb['url']) {{ $breadcrumb['url'] }} @else javascript:void(0) @endisset">
                                                    @isset($breadcrumb['text'])
                                                        {{ $breadcrumb['text'] }}
                                                    @else
                                                        {{ $breadcrumb }}
                                                    @endisset
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                            <div class="col-md-12">
                                <div class="page-header-title">
                                    <h2 class="mb-0">{{ $page_name ?? 'Blank page name' }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endisset
            <div class="row col-12">
                @include('layouts._error')
                @yield('content')
            </div>
        </div>
    </div>
    @include('layouts.admin._footer')
    @include('layouts._logout-forms')
    @include('layouts._delete-form-js')
    @include('layouts._delete-form')
    @include('layouts._toasts')

    @include('layouts.admin._settings')

    @include('layouts._scripts')
    <!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('admin/assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('admin/assets/js/plugins/choices.min.js') }}"></script>

<script src="{{ asset('tel/build/js/intlTelInput.js') }}"></script>
<script>
	let indicatif = document.getElementById('indicatif');

	let input = document.getElementById('tel-input');

	let flag = document.getElementsByClassName('iti__flag-container');


	const updateIndicatif = () => {
		let elements = document.getElementsByClassName('iti__country iti__standard iti__active');

		if (elements.length > 0) {
			indicatif.value = elements[0].getAttribute('data-dial-code');
		} else {
			indicatif.value = '228';
		}
	}

	window.intlTelInput(input, {
		utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
		initialCountry: 'auto',
		geoIpLookup: callback => {
			fetch("https://ipapi.co/json")
				.then(res => res.json())
				.then(data => callback(data.country_code))
				.catch(() => callback("us"));
		},
	})

	input.addEventListener('input', updateIndicatif);

	input.addEventListener('paste', () => {
		setTimeout(updateIndicatif);
	});
	input.addEventListener('cut', () => {
		setTimeout(updateIndicatif);
	});

</script>

</body>

</html>
