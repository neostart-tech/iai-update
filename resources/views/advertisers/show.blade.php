@extends('base', [
	'title' => 'Détails d\'un partenaire',
	'page_name' => 'Détails du partenaire',
	'breadcrumbs' => [
		[
			'text' => 'Partenaires',
			'url' => route('admin.advertisers.index')
		],
		$advertiser->nom,
		'Détails'
	]
])

@section('content')
	<div class="card">
		<div class="card-header">
			<div class="text-end p-4 pb-sm-2 mb-2">
				<a href="{{ route('admin.advertisers.edit', $advertiser) }}" class="btn btn-primary">
					<i class="ti ti-edit f-18"></i> Modifier
				</a>
			</div>
		</div>
		<div class="card-body">
			<div class="row">
				<div class="form-group col-md-6">
					<x-forms.label for="nom" content="Nom" required="0"/>
					<input class="form-control" type="text" id="nom" name="nom" value="{{ old('nom', $advertiser->nom) }}"
								 readonly>
					{!! errorAlert($errors->first('nom'), 'nom') !!}
				</div>

				<div class="form-group col-md-6">
					<x-forms.label for="email" content="Email" required="0"/>
					<input class="form-control" type="email" id="email" name="email"
								 value="{{ old('email', $advertiser->email) }}" readonly>
					{!! errorAlert($errors->first('email'), 'email') !!}
				</div>

				<div class="form-group col-md-6">
					<x-forms.label for="site" content="Site" required="0"/>
					<input class="form-control" type="url" id="site" name="site" value="{{ old('site', $advertiser->site) }}"
								 readonly>
					{!! errorAlert($errors->first('site'), 'site') !!}
				</div>

				<div class="form-group col-md-6">
					<x-forms.label for="ville" content="Ville" required="0"/>
					<input class="form-control" type="text" id="ville" name="ville"
								 value="{{ old('ville', $advertiser->ville) }}" readonly>
					{!! errorAlert($errors->first('ville'), 'ville') !!}
				</div>

				<div class="form-group">
					<x-forms.label for="details" content="À propos du partenaire" required="0"/>
					<div class="form-control" id="details">{!!  $advertiser->details !!}</div>
				</div>
			</div>
		</div>
	</div>
@endsection
