<ul class="pc-navbar">

	<li class="pc-item">
		<a href="{{ route('my-calendar') }}" class="pc-link">
              <span class="pc-micon">
                <i class="fa fa-calendar-day"></i>
              </span>
			<span class="pc-mtext">Emploi du temps</span>
		</a>
	</li>

	<li class="pc-item">
	<a href="{{ route('etudiants.announcements.index') }}" class="pc-link">
		<span class="pc-micon"><i data-feather="volume-2"></i></span>
		<span class="pc-mtext">Annonces</span>
	</a>
</li>

<li class="pc-item">
	<a href="{{ route('etudiants.releves.auth.view') }}" class="pc-link">
		<span class="pc-micon"><i data-feather="file-text"></i></span>
		<span class="pc-mtext">Mon relevé</span>
	</a>
</li>

<li class="pc-item">
	<a href="{{ route('etudiants.notes.index') }}" class="pc-link">
		<span class="pc-micon"><i data-feather="book-open"></i></span>
		<span class="pc-mtext">Notes</span>
	</a>
</li>


	<li class="pc-item pc-caption">
		<label>Mon dossier</label>
	</li>

	<li class="pc-item">
		<a href="{{ route('etudiants.my-space.constitution') }}" class="pc-link">
			<span class="pc-micon">
				<i class="ti ti-file-text"></i>
			</span>
			<span class="pc-mtext">Constitution</span>
		</a>
	</li>

	<li class="pc-item">
		<a href="{{ route('etudiants.my-space.my-files') }}" class="pc-link">
			<span class="pc-micon">
				<i class="ph-duotone ph-folder-notch-open"></i>
			</span>
			<span class="pc-mtext">Mes fichiers</span>
		</a>
	</li>
	<li class="pc-item">
		<a href="{{ route('etudiants.cv.show') }}" class="pc-link">
			<span class="pc-micon">
				<i class="material-icons-two-tone">contact_page</i>
			</span>
			<span class="pc-mtext">Mon CV</span>
		</a>
	</li>
	<li class="pc-item">
		<a href="{{ route('etudiants.announcements.my-applications') }}" class="pc-link">
			<span class="pc-micon">
				 <i class="material-icons-two-tone"> archive</i>
			</span>
			<span class="pc-mtext">Mes dépôts</span>
		</a>
	</li>
	<li class="pc-item">
		<a href="{{ route('etudiants.my-space.my-payment') }}" class="pc-link">
			<span class="pc-micon">
				 <i class="material-icons-two-tone"> payment</i>
			</span>
			<span class="pc-mtext">Mes paiements</span>
		</a>
	</li>

</ul>