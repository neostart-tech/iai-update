@php use App\Models\{Candidature,Etudiant}; @endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    @include('layouts.admin._head')
</head>

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme_contrast=""
    data-pc-theme="light">



    @include('layouts.admin._preloader')

    <nav class="pc-sidebar">
        <div class="navbar-wrapper">
            <div class="m-header">
                <a href="{{ route('home') }}" class="b-brand mx-auto">
                    @php
                        $logoPath = AppGetters::getAppLogo();
                    @endphp

                    <img src="{{ $logoPath && Storage::disk('public')->exists($logoPath)
                        ? Storage::url($logoPath)
                        : 'https://www.iai-togo.tg/wp-content/uploads/2017/06/logo.jpeg' }}"
                        style="max-height: 60px; max-width: 85px" class="img-fluid logo-lg" alt="logo">


                </a>
            </div>
            <div class="navbar-content">

                <div class="card pc-user-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <span
                                    class="avtar bg-primary text-white">{{ Str::limit(request()->user()->nom, 1, '') . ' ' . Str::limit(request()->user()->prenom, 1, '') }}
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3 me-2">
                                <h6 class="mb-0">{{ request()->user()->nom . ' ' . request()->user()->prenom }}</h6>
                            </div>
                            <a class="btn btn-icon btn-link-secondary avtar" data-bs-toggle="collapse"
                                href="#pc_sidebar_userlink">
                                <svg class="pc-icon">
                                    <use xlink:href="#custom-sort-outline"></use>
                                </svg>
                            </a>
                        </div>
                        <div class="collapse pc-user-links" id="pc_sidebar_userlink">
                            @php
                                $logoutFormId = 'enseignant-logout-form';
                            @endphp
                            <div class="pt-3">
                                <form action="{{ route('enseignants.auth.logout') }}" method='post'>
                                    @csrf
                                    @method('DELETE')
                                   
                                    <button style='outline:none;border:none;background:none;'>
                                        <i class="ti ti-power"></i>
                                        <span>Déconnexion</span>
                                        </a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @include('layouts.sidebars._professeur-side-bar')
            </div>
        </div>
    </nav>
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
                @yield('bases')
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
</body>

</html>
