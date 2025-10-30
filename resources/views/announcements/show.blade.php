@php use Illuminate\Support\Facades\Storage; @endphp
@extends('base', [
	'title' => 'Détails d\'une annonce',
	'page_name' => 'Détails de l\'annonce',
	'breadcrumbs' => [
		[
			'text' => 'Annonces',
			'url' => route('etudiants.announcements.index')
		],
		$announcement->title,
		'Détails'
	]
])

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-md-6">
					<div class="sticky-md-top product-sticky mb-3">
						{!! $announcement->content !!}
					</div>
					@if($announcement->file_path)
						<button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
										data-bs-target="#file-previewer"
										onclick="showLoadedFile('{{ url(Storage::url($announcement->file_path)) }}')">
							<i class="fa fa-file-pdf"></i>
							Annonce officielle
						</button>
					@endif
				</div>
				<div class="col-md-6">
					<span class="badge bg-success f-14">{{ $announcement->type_annonce->value }}</span>
					<span class="badge bg-secondary f-14">{{ $announcement->type_contrat->value }}</span>
					<h4 class="my-3">{{ $announcement->title }}</h4>
					<div class="star f-18 mb-3">
						<i data-feather="globe"></i> {{ $announcement->advertiser->nom }}
					</div>
					<div class="star f-18 mb-3">
						<i data-feather="map-pin"></i> {{ $announcement->ville }}
					</div>
					<div class="star f-18 mb-3">
						<i data-feather="mail"></i> {{ $announcement->advertiser->email }}
					</div>
					@if($announcement->advertiser->site)
						<div class="star f-18 mb-3 text-black">
							<i data-feather="link-2">
							</i>
							<a href="{{ $announcement->advertiser->site }}" target="_blank">
								{{ $announcement->advertiser->site }} <i data-feather="external-link"></i>
							</a>
						</div>
					@endif
					@unless($applied)
						<div class="row">
							<div class="d-grid">
								<button type="button" class="btn btn-outline-primary"
												onclick="alertAboutApplication()">
									<i class="fas fa-pray"></i> Postuler
								</button>
							</div>
						</div>
					@endif
				</div>
			</div>
		</div>
	</div>
	@include('announcements._preview-modal')
	@unless($applied)
		@include('announcements.apply-form')
	@endunless
@endsection


@section('other-js')
	<script>
		function showLoadedFile(url) {
			document.getElementById('pdf-viewer').data = url;
		}

		function alertAboutApplication() {
			new Swal({
				icon: 'info',
				title: 'Dépôt de candidature',
				text: 'Voulez-vous confirmer cette action ?',
				confirmButtonText: 'Oui, postuler'
			}).then(result => result.isConfirmed && document.getElementById('apply-form').submit());

		}
	</script>
@endsection
