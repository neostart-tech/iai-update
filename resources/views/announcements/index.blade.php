@extends('base', [
	'title' => 'Liste des annonces',
	'breadcrumbs' => [
		'Annonces',
		'Liste'
	],
	'page_name' => 'Liste des annonces'
])

@section('content')
	<div class="card">
		<div class="card-body">
			{{--			@dd($announcements->first()->type)--}}
			@if($announcements->isEmpty())
				<x-empty-table content="Aucune annonce de publiée"/>
			@else
				<div class="address-check-block">
					@foreach($announcements as $announcement)
						<div class="form-control mb-3">
							<label class="form-check-label d-block" for="address-check-1">
								<span class="h6 mb-2 d-block">{{ $announcement->advertiser->nom }}
									<small class="text-muted">
										({{ $announcement->type_annonce->value }}, {{ $announcement->type_contrat->value }})
									</small>
								</span>
								<span class="text-muted address-details">
									{{ $announcement->title }}
								</span>
								<span class="row align-items-center justify-content-between mt-3">
								<span class="col-6">
									<span class="text-muted mb-0">
										<i data-feather="map-pin"></i>
										{{ $announcement->ville }}
									</span>
								</span>
{{--									@unless($announcement->applications_count)--}}
										<span class="col-6 text-end"
													onclick="document.getElementById('announcement-{{ $announcement->slug }}').click()"
										>
										<a class="btn text-bg-primary btn-outline-primary"
											 id="announcement-{{ $announcement->slug }}"
											 href="{{ route('etudiants.announcements.show', $announcement) }}">
											Lire l'annonce
										</a>
									</span>
{{--									@endunless--}}
								</span>
							</label>
						</div>
					@endforeach
				</div>
			@endif
		</div>
	</div>
@endsection
