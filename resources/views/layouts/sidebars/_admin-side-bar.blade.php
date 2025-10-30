<ul class="pc-navbar">

	{{-- Emploi du temps	--}}
	<li class="pc-item">
		<a href="{{ route('my-calendar') }}" class="pc-link">
              <span class="pc-micon">
                <i class="fa fa-calendar-day"></i>
              </span>
			<span class="pc-mtext">Emploi du temps</span>
		</a>
	</li>

	<li class="pc-item">
		<a href="{{ route('admin.presences.index') }}" class="pc-link">
			<span class="pc-micon"><i class="fa fa-user-check"></i></span>
			<span class="pc-mtext">Présences à valider</span>
			@php($__pending = \App\Models\CoursPresence::where('statut','absent')->where('needs_validation', true)->count())
			@if($__pending > 0)
				<span class="pc-badge">{{ $__pending }}</span>
			@endif
		</a>
	</li>

	{{-- administration --}}
	<li class="pc-item pc-caption">
		<label>administration</label>
	</li>

	{{-- Filières	--}}
	<li class="pc-item pc-hasmenu">
		<a href="#!" class="pc-link">
              <span class="pc-micon">
                <svg class="pc-icon">
                  <use xlink:href="#custom-status-up"></use>
                </svg>
              </span>
			<span class="pc-mtext">Filières</span>
			<span class="pc-arrow"><i data-feather="chevron-right"></i></span>
		</a>
		<ul class="pc-submenu">
			<li class="pc-item"><a class="pc-link" href="{{ route('admin.filieres.index') }}">Liste des
					filières</a>
			</li>
			<li class="pc-item"><a class="pc-link" href="{{ route('admin.filieres.create') }}">Ajouter une
					filière</a>
			</li>
		</ul>
	</li>

	{{-- Ues	--}}
	<li class="pc-item pc-hasmenu">
		<a href="#!" class="pc-link">
              <span class="pc-micon">
                <svg class="pc-icon">
                  <use xlink:href="#custom-status-up"></use>
                </svg>
              </span>
			<span class="pc-mtext">Unités d'enseignement</span>
			<span class="pc-arrow"><i data-feather="chevron-right"></i></span>
		</a>
		<ul class="pc-submenu">
			<li class="pc-item"><a class="pc-link" href="{{ route('admin.ues.index') }}">Liste des Ues</a>
			</li>
			<li class="pc-item"><a class="pc-link" href="{{ route('admin.ues.create') }}">Ajouter une Ue</a>
			</li>
		</ul>
	</li>


	{{-- Uvs	--}}
	<li class="pc-item pc-hasmenu">
		<a href="#!" class="pc-link">
              <span class="pc-micon">
                <svg class="pc-icon">
                  <use xlink:href="#custom-status-up"></use>
                </svg>
              </span>
			<span class="pc-mtext">Unité de valeur</span>
			<span class="pc-arrow"><i data-feather="chevron-right"></i></span>
		</a>
		<ul class="pc-submenu">
			<li class="pc-item"><a class="pc-link" href="{{ route('admin.uvs.index') }}">Liste des Matières</a>
			</li>
			<li class="pc-item"><a class="pc-link" href="{{ route('admin.uvs.create') }}">Ajouter une Matière</a>
			</li>
		</ul>
	</li>

	{{-- Periodes	--}}
	<li class="pc-item pc-hasmenu">
		<a href="#!" class="pc-link">
              <span class="pc-micon">
                <svg class="pc-icon">
                  <use xlink:href="#custom-status-up"></use>
                </svg>
              </span>
			<span class="pc-mtext">Périodes</span>
			<span class="pc-arrow"><i data-feather="chevron-right"></i></span>
		</a>
		<ul class="pc-submenu">
			<li class="pc-item"><a class="pc-link" href="{{ route('admin.periodes.index') }}">Liste des
					périodes</a>
			</li>
			<li class="pc-item"><a class="pc-link" href="{{ route('admin.periodes.create') }}">Ajouter une
					période</a>
			</li>
		</ul>
	</li>

	{{-- Salles	--}}
	<li class="pc-item">
		<a href="{{ route('admin.salles.index') }}" class="pc-link">
              <span class="pc-micon">
                <i class="ti ti-building"></i>
              </span>
			<span class="pc-mtext">Gestion des salles</span>
		</a>
	</li>

	{{-- Groupes --}}
	<li class="pc-item">
		<a href="{{ route('admin.groups.index') }}" class="pc-link">
              <span class="pc-micon">
                <i class="ti ti-users"></i>
              </span>
			<span class="pc-mtext">Gestion des Groupes</span>
		</a>
	</li>


	{{-- Rôles	--}}
	<li class="pc-item pc-hasmenu">
		<a href="#!" class="pc-link">
              <span class="pc-micon">
                <svg class="pc-icon">
                  <use xlink:href="#custom-status-up"></use>
                </svg>
              </span>
			<span class="pc-mtext">Rôles</span>
			<span class="pc-arrow"><i data-feather="chevron-right"></i></span>
		</a>
		<ul class="pc-submenu">
			<li class="pc-item"><a class="pc-link" href="{{ route('admin.roles.index') }}">Liste des rôles</a>
			</li>
			{{--						<li class="pc-item"><a class="pc-link" href="{{ route('admin.roles.create') }}">Ajouter un rôle</a></li> --}}
		</ul>
	</li>

	{{-- Évaluations	--}}
	<li class="pc-item pc-hasmenu">
		<a href="#!" class="pc-link">
              <span class="pc-micon">
                <svg class="pc-icon">
                  <use xlink:href="#custom-status-up"></use>
                </svg>
              </span>
			<span class="pc-mtext">Évaluations</span>
			<span class="pc-arrow"><i data-feather="chevron-right"></i></span>
		</a>
		<ul class="pc-submenu">
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.evaluations.index') }}">Liste des évaluations</a>
			</li>
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.evaluations.create') }}">Ajouter évaluation</a>
			</li>
		</ul>
	</li>

	{{-- Personnel	--}}
	<li class="pc-item pc-hasmenu">
		<a href="#!" class="pc-link">
              <span class="pc-micon">
                <svg class="pc-icon">
                  <use xlink:href="#custom-status-up"></use>
                </svg>
              </span>
			<span class="pc-mtext">Personnel</span>
			<span class="pc-arrow"><i data-feather="chevron-right"></i></span>
		</a>
		<ul class="pc-submenu">
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.users.index') }}">Liste des Utilisateurs</a>
			</li>
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.users.create') }}">Ajouter un Utilisateur</a>
			</li>
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.users.index', ['profil' => 'enseignants']) }}">Liste des
					enseignants</a>
			</li>
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.surveillants.index') }}">
					<i class="ti ti-shield"></i> Gestion Surveillants
				</a>
			</li>
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.users.teachers.hours-summary') }}">Récap heures enseignants</a>
			</li>
		</ul>
	</li>

	{{-- administration --}}
	<li class="pc-item pc-caption">
		<label>Candidatures</label>
	</li>

	{{-- Candidatures	--}}
	<li class="pc-item pc-hasmenu">
		<a href="#!" class="pc-link">
              <span class="pc-micon">
                <svg class="pc-icon">
                  <use xlink:href="#custom-status-up"></use>
                </svg>
              </span>
			<span class="pc-mtext">Candidatures</span>
			<span class="pc-arrow"><i data-feather="chevron-right"></i></span>
		</a>
		<ul class="pc-submenu">
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.candidatures.index') }}">
					Étude de dossier
				</a>
			</li>
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.candidatures.payement-des-frais-de-participation') }}">
					Payement
				</a>
			</li>
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.candidatures.participation-au-concours') }}">
					Contrôle de présence
				</a>
			</li>
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.candidatures.admission') }}">
					Déclaration d'admission
				</a>
			</li>
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.candidatures.choose-class-assignment-group-view') }}">
					Attribution de groupe
				</a>
			</li>
		</ul>
	</li>

	{{-- Autres --}}
	<li class="pc-item pc-caption">
		<label>Autres</label>
	</li>

    @can('manage-gallery')
    {{-- Galerie --}}
    <li class="pc-item pc-caption">
        <label>Galerie</label>
    </li>
    <li class="pc-item pc-hasmenu">
        <a href="#" class="pc-link">
            <span class="pc-micon">
                <i class="ti ti-photo"></i>
            </span>
            <span class="pc-mtext">Galerie</span>
            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
        </a>
        <ul class="pc-submenu">
            <li class="pc-item">
                <a class="pc-link" href="{{ route('admin.gallery.albums.index') }}">Albums</a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="{{ route('admin.gallery.albums.create') }}">Ajouter un album</a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="{{ route('admin.gallery.photos.index') }}">Photos</a>
            </li>
            <li class="pc-item">
                <a class="pc-link" href="{{ route('admin.gallery.photos.create') }}">Ajouter une photo</a>
            </li>
        </ul>
    </li>
    @endcan

	{{-- Messages	--}}
	<li class="pc-item">
		<a href="{{ route('admin.messages.index') }}" class="pc-link">
			<span class="pc-micon">
				<i class="material-icons-two-tone"> message</i>
			</span>
			<span class="pc-mtext">
				Messages
				<span class="pc-badge">{{ unreadMessagesCount() }}</span>
			</span>
		</a>
	</li>

	{{-- Réclamations --}}
	<li class="pc-item">
		<a href="{{ route('admin.reclamations.index') }}" class="pc-link">
			<span class="pc-micon">
				<i class="ti ti-alert-circle"></i>
			</span>
			<span class="pc-mtext">Réclamations</span>
		</a>
	</li>


	{{-- Blogs	--}}
	<li class="pc-item pc-hasmenu">
		<a href="#!" class="pc-link">
			<span class="pc-micon">
				<i class="fas fa-newspaper"></i>
			</span>
			<span class="pc-mtext">Publications</span>
			<span class="pc-arrow"><i data-feather="chevron-right"></i></span>
		</a>
		<ul class="pc-submenu">
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.blogs.index') }}">Liste des publications</a>
			</li>
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.blogs.create') }}">Ajouter une publication</a>
			</li>
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.urgent_infos.index') }}">Infos urgentes</a>
			</li>
		</ul>
	</li>

	{{-- Évènements	--}}
	<li class="pc-item pc-hasmenu">
		<a href="#!" class="pc-link">
			<span class="pc-micon">
				 <i class="material-icons-two-tone"> event_note</i>
			</span>
			<span class="pc-mtext">Évènements</span>
			<span class="pc-arrow"><i data-feather="chevron-right"></i></span>
		</a>
		<ul class="pc-submenu">
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.events.index') }}">Liste des évènements</a>
			</li>
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.events.create') }}">Ajouter un évènement</a>
			</li>
		</ul>
	</li>

	{{-- Partenaires	--}}
	<li class="pc-item pc-hasmenu">
		<a href="#!" class="pc-link">
			<span class="pc-micon">
				<i class="fas fa-handshake"></i>
			</span>
			<span class="pc-mtext">Partenaires</span>
			<span class="pc-arrow"><i data-feather="chevron-right"></i></span>
		</a>
		<ul class="pc-submenu">
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.advertisers.index') }}">Liste des Partenaires</a>
			</li>
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.advertisers.create') }}">Ajouter un partenaire</a>
			</li>
		</ul>
	</li>


	{{-- Annonces	--}}
	<li class="pc-item pc-hasmenu">
		<a href="#!" class="pc-link">
			<span class="pc-micon">
				<i class="fas fa-broadcast-tower"></i>
			</span>
			<span class="pc-mtext">Opportunités</span>
			<span class="pc-arrow"><i data-feather="chevron-right"></i></span>
		</a>
		<ul class="pc-submenu">
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.announcements.index') }}">Liste des opportunités</a>
			</li>
			<li class="pc-item">
				<a class="pc-link" href="{{ route('admin.announcements.create') }}">Ajouter une opportunité</a>
			</li>
		</ul>
	</li>
</ul>
