@extends('base', [
	'title' => 'Mes dépôts',
	'breadcrumbs' => [
		'Mon dossier',
		'Mes dépôts'
	],
	'page_name' => 'Mes dépôts'
])

@section('content')
	<div class="card">
		<div class="card-body">
			@if($announcements->isEmpty())
				<x-empty-table content="Aucun dépôt n'a encore été fait"/>
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
								<span class="col-6 text-end">
									@if($announcement->pivot->applied )
										<a class="btn btn-outline-primary text-bg-primary">
										Fait le: {{ $announcement->pivot->created_at->translatedFormat('d F Y') }}
									</a>
									@else
										<a class="btn text-bg-primary btn-outline-primary">
										Dépôt en cours &hellip;
									</a>
									@endif
								</span>
								</span>
							</label>
						</div>
					@endforeach
				</div>
			@endif
		</div>
	</div>
@endsection
