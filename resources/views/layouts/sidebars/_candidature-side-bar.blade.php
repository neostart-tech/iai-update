<ul class="pc-navbar">
	<li class="pc-item pc-caption">
		<label>Ma candidature</label>
	</li>

	<li class="pc-item">
		<a href="{{ route('officiel.my-space.show') }}" class="pc-link">
			<svg class="pc-icon">
				<use xlink:href="#custom-status-up"></use>
			</svg>
			<span class="pc-mtext">Avancement</span>
		</a>
	</li>
	@unless(request()->user()->acceptation_date)
		<li class="pc-item">
			<a href="{{ route('officiel.my-space.constitution') }}" class="pc-link">
              <span class="pc-micon">
                <i class="ti ti-file-text"></i>
              </span>
				<span class="pc-mtext">Constitution</span>
			</a>
		</li>

		<li class="pc-item">
			<a href="{{ route('officiel.my-space.files') }}" class="pc-link">
              <span class="pc-micon">
                <i class="ph-duotone ph-folder-notch-open"></i>
              </span>
				<span class="pc-mtext">Mes fichiers</span>
			</a>
		</li>
	@endunless
	<li class="pc-item">
		<a href="{{ route('officiel.my-space.payments') }}" class="pc-link">
              <span class="pc-micon">
                <i class="fas fa-money-bill-alt"></i>
              </span>
			<span class="pc-mtext">Mes payements</span>
		</a>
	</li>
</ul>