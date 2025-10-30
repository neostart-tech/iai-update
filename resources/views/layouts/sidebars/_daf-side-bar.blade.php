{{-- Sidebar DAF (Directeur des Affaires Financières) --}}
<ul class="pc-navbar">
    <li class="pc-item pc-caption">
        <label>🏦 DAF - Affaires Financières</label>
    </li>
    
    {{-- Dashboard DAF --}}
    <li class="pc-item">
        <a href="{{ route('dashboard') }}" class="pc-link">
            <span class="pc-micon">
                <i class="ti ti-dashboard"></i>
            </span>
            <span class="pc-mtext">Tableau de Bord</span>
        </a>
    </li>

    {{-- Configuration des frais par genre --}}
    <li class="pc-item pc-hasmenu">
        <a href="#!" class="pc-link">
            <span class="pc-micon">
                <i class="ti ti-settings-2"></i>
            </span>
            <span class="pc-mtext">Frais par Genre</span>
            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
        </a>
        <ul class="pc-submenu">
            <li class="pc-item">
                <a class="pc-link" href="{{ route('daf.frais-genre.index') }}">
                    <i class="ti ti-adjustments"></i>
                    Configuration
                </a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="{{ route('daf.frais-genre.rapport') }}">
                    <i class="ti ti-chart-bar"></i>
                    Rapport & Analyse
                </a>
            </li>
        </ul>
    </li>

    {{-- Gestion classique des frais --}}
    <li class="pc-item pc-hasmenu">
        <a href="#!" class="pc-link">
            <span class="pc-micon">
                <i class="ti ti-coins"></i>
            </span>
            <span class="pc-mtext">Gestion Frais</span>
            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
        </a>
        <ul class="pc-submenu">
            <li class="pc-item">
                <a class="pc-link" href="{{ route('comptable.frais.index') }}">
                    <i class="ti ti-list"></i>
                    Tous les Frais
                </a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="{{ route('comptable.paiement.index') }}">
                    <i class="ti ti-credit-card"></i>
                    Paiements
                </a>
            </li>
        </ul>
    </li>

    {{-- Validation et Autorisations --}}
    <li class="pc-item pc-hasmenu">
        <a href="#!" class="pc-link">
            <span class="pc-micon">
                <i class="ti ti-shield-check"></i>
            </span>
            <span class="pc-mtext">Validations</span>
            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
        </a>
        <ul class="pc-submenu">
            <li class="pc-item">
                <a class="pc-link" href="#!">
                    <i class="ti ti-calendar-time"></i>
                    Échéanciers
                </a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="#!">
                    <i class="ti ti-user-check"></i>
                    Autorisations Spéciales
                </a>
            </li>
        </ul>
    </li>

    {{-- Rapports Financiers --}}
    <li class="pc-item pc-hasmenu">
        <a href="#!" class="pc-link">
            <span class="pc-micon">
                <i class="ti ti-report-analytics"></i>
            </span>
            <span class="pc-mtext">Rapports</span>
            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
        </a>
        <ul class="pc-submenu">
            <li class="pc-item">
                <a class="pc-link" href="#!">
                    <i class="ti ti-chart-line"></i>
                    Évolution Paiements
                </a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="#!">
                    <i class="ti ti-users"></i>
                    Statistiques Étudiants
                </a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="#!">
                    <i class="ti ti-file-export"></i>
                    Exports Comptables
                </a>
            </li>
        </ul>
    </li>

    {{-- Paramètres DAF --}}
    <li class="pc-item pc-caption">
        <label>⚙️ Paramètres</label>
    </li>
    
    <li class="pc-item">
        <a href="#!" class="pc-link">
            <span class="pc-micon">
                <i class="ti ti-settings"></i>
            </span>
            <span class="pc-mtext">Configuration Système</span>
        </a>
    </li>
</ul>