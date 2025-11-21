<ul class="pc-navbar">

    {{-- Emploi du temps	--}}
    <li class="pc-item">
        <a href="{{ route("enseignants.index") }}" class="pc-link">
            <span class="pc-micon">
                <i class="fa fa-calendar-day"></i>
            </span>
            <span class="pc-mtext">Emploi du temps</span>
        </a>
    </li>
    <li class="pc-item pc-hasmenu">
        <a href="#!" class="pc-link">
            <span class="pc-micon">
                <svg class="pc-icon">
                    <use xlink:href="#custom-status-up"></use>
                </svg>
            </span>
            <span class="pc-mtext">Cours & Evaluations</span>
            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
        </a>
        <ul class="pc-submenu">
            <li class="pc-item">
                <a class="pc-link" href="{{ route('enseignants.cours.du.jour') }}">
                    Liste des cours
                </a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="{{ route('enseignants.mes-evaluations') }}">
                    Liste des évaluations
                </a>
            </li>
        </ul>
    </li>

    {{-- administration --}}
    {{-- <li class="pc-item pc-caption">
        <label>administration</label>
    </li> --}}



    {{-- Gestion des notes --}}
    {{-- <li class="pc-item">
    <a href="" class="pc-link">
        <span class="pc-micon">
            <i class="ti ti-file-pencil"></i>
        </span>
        <span class="pc-mtext">Gestion des notes</span>
    </a>
</li> --}}





</ul>
