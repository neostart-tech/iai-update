@php use App\Enums\GenreEnum; @endphp
@extends('base',[
	'title' => 'Mon dossier de candidature',
	'page_name' => 'État d\'avancement de ma candidature',
	'breadcrumbs' => ['Ma candidature', 'État d\'avancement']
])

@section('content')
	@php($appName = config('app.name'))
	@php($valide = $candidature->dossier_valide)
	@php($frais = $candidature->frais_paye)
	@php($participation = $candidature->participation)
	@php($admission = $candidature->admission)
	@php($motif = $candidature->mofif)
{{--	@dd($candidature->unreadNotifications)--}}
	<div class="card task-card">
		<div class="card-body">
			<ul class="list-unstyled task-list">
				<li>
					<i class="feather icon-check f-w-600 task-icon bg-success"></i>
					<p class="m-b-5">Création du compte - <b>{{ $candidature->created_at->translatedFormat('d F Y') }}</b></p>
					<h5 class="text-muted">
						Vous avez à présent un compte de candidature auprès de {{ AppGetters::getAppName() ? AppGetters::getAppName() : "Laravel" }}
					</h5>
				</li>
				<li>
					<i class="task-icon feather icon-check f-w-600 bg-success"></i>
					<p class="m-b-5">Dépôt des documents - <b>{{ $candidature->created_at->translatedFormat('d F Y') }}</b></p>
					<h5 class="text-muted">Les documents constitutifs de votre dossier de candidature ont été enregistrés avec
						succès</h5>
				</li>
				<li>
					<i
						class="task-icon {{ $valide ? 'bg-success feather icon-check f-w-600' : 'bg-warning'}} {{ !$motif ?: 'bg-danger icon-x' }}"></i>
					<p class="m-b-5">Validation du dossier de candidature
						@if($candidature->dossier_valide)
							- <b>{{ $candidature->validation_date->translatedFormat('d F Y') }}</b>
						@else
							<span class="text-danger">(en attente)</span>
						@endif
					</p>
					<h5 class="text-muted">
						{{ $valide ? 'Si votre candidature est acceptée, vous serez autorisé à payer les frais de participation au concours de
						sélection' : ''}}
					</h5>
				</li>
				<li>
					<i
						class="task-icon {{ $frais ? 'bg-success  feather icon-check f-w-600' : ( $valide ? 'bg-warning' : 'bg-secondary')}} {{ !$motif ?: 'bg-danger icon-x' }}"></i>
					<p class="m-b-5">
						Payement des frais de participation au concours
						@if($frais)
							- <b>{{ $candidature->frai_paye_date->translatedFormat('d F Y') }}</b>
						@else
							@if($valide)
								<span class="text-danger">(en attente)</span>
							@endif
						@endif
					</p>
					<h5 class="text-muted">{{ $frais ? 'Vous avez payé les frais de participation au concours' : '' }}</h5>
				</li>
				<li>
					<i
						class="task-icon {{ $participation ? 'bg-success  feather icon-check f-w-600' : ( $frais ? 'bg-warning' : 'bg-secondary')}} {{ !$motif ?: 'bg-danger icon-x' }}"></i>
					<p class="m-b-5">
						Participation au concours
						@if($participation)
							- <b>{{ $candidature->participation_date->translatedFormat('d F Y') }}</b>
						@else
							@if($frais)
								<span class="text-danger">(en attente)</span>
							@endif
						@endif
					</p>
					<h5 class="text-muted">Vous avez participé au concours de sélection</h5>
				</li>
				<li>
					<i
						class="task-icon {{ $admission ? 'bg-success  feather icon-check f-w-600' : ( $participation ? 'bg-warning' : 'bg-secondary')}}"></i>
					<p class="m-b-5">
						Admission à {{AppGetters::getAppName() ? AppGetters::getAppName() : "Laravel"}}
						@if($admission)
							- <b>{{ $candidature->admission_date->translatedFormat('d F Y') }}</b>
						@else
							@if($participation)
								<span class="text-danger">(en attente)</span>
							@endif
						@endif
					</p>
					<h5 class="text-muted">Félicitations! vous avez été {{ GenreEnum::M === $candidature->genre ? 'admis' : 'admise'}}
						à {{ AppGetters::getAppName() ? AppGetters::getAppName() : "Laravel" }}. Vous serez rediriger dans une classe bientôt</h5>
				</li>
			</ul>
		</div>
	</div>

	@if($motif)
		<div class="card">
			<div class="card-header border-bottom-0">
				<button class="btn btn-warning m-t-5" type="button" data-bs-toggle="collapse"
								data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
					Motif de suspension de la procédure
				</button>
			</div>
			<div class="collapse" id="collapseExample">
				<div class="card-body border-top">
					<p class="mb-0">{{ $motif }}</p>
				</div>
			</div>
		</div>
	@endif
@endsection

@section('other-css')
	<link rel="stylesheet" href="{{ asset('admin/assets/fonts/fontawesome.css') }}" >
@endsection