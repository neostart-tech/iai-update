@php
    use App\Models\{Candidature, Etudiant};
    use Illuminate\Support\Str;
@endphp
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
                                class="avtar bg-primary text-white">{{ Str::limit($user->nom, 1, '') . ' ' . Str::limit($user->prenom, 1, '') }}
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3 me-2">
                            <h6 class="mb-0">{{ $user->nom . ' ' . $user->prenom }}</h6>
                        </div>
                        <a class="btn btn-icon btn-link-secondary avtar" data-bs-toggle="collapse"
                            href="#pc_sidebar_userlink">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-sort-outline"></use>
                            </svg>
                        </a>
                    </div>
                    <div class="collapse pc-user-links" id="pc_sidebar_userlink">
                        <div class="pt-3">
                            <a href="javascript:void(0)"
                                onclick="document.getElementById('{{ $logoutFormId }}').submit();">
                                <i class="ti ti-power"></i>
                                <span>Déconnexion</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @includeWhen($is_candidat, 'layouts.sidebars._candidature-side-bar')
            @includeWhen($is_admin, 'layouts.sidebars._admin-side-bar')
            @includeWhen($is_etudiant, 'layouts.sidebars._etudiants-sidebar')
           


            {{--			@include('layouts.sidebars._side-bar-unused-elements') --}}
        </div>
    </div>
</nav>
