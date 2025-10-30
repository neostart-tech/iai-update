@extends('base', [
	'title' => 'Détails d\'une opportunité',
	'page_name' => 'Détails de l\'opportunité',
	'breadcrumbs' => [
		[
			'text' => 'Opportunités',
			'url' => route('admin.announcements.index')
		],
		$announcement->title,
		'Détails'
	]
])

@section('content')
	<div class="card">
		<div class="card-body row">

			<div class="form-group col-md-6">
				<x-forms.label for="title" content="Annonceur"/>
				<input class="form-control" type="text" value="{{ $announcement->advertiser->nom }}" readonly>
			</div>

			<div class="form-group col-md-6 col-lg-3">
				<x-forms.label for="title" content="Type d'opportunité" required="0"/>
				<input class="form-control" type="text" value="{{$announcement->type_contrat->value}}" readonly>
			</div>

			<div class="form-group col-md-6 col-lg-3">
				<x-forms.label for="title" content="Type de contrat" required="0"/>
				<input class="form-control" type="text" value="{{$announcement->type_annonce->value}}" readonly>
			</div>

			<div class="form-group col-md-6">
				<x-forms.label for="ville" content="Ville" required="0"/>
				<input class="form-control" type="text" id="ville" value="{{ $announcement->ville }}" readonly>
			</div>

			<div class="form-group col-md-6">
				<x-forms.label for="duration" content="Durée (6 mois / 90 jours)" required="0"/>
				<input class="form-control" type="text" id="duration" value="{{ $announcement->duration ?? '--'}}" readonly>
			</div>

			<div class="form-group">
				<x-forms.label for="ville" content="Titre" required="0"/>
				<div class="form-control" type="text" id="ville" readonly>
					{{ $announcement->title }}
				</div>
			</div>

			<div class="form-group">
				<x-forms.label for="content" content="Contenu de l'annonce" required="0"/>
				<div class="form-control" id="content">{!!  $announcement->content !!}</div>
			</div>

			@if($announcement->file_path)
				<div class="form-group">
					<button class="btn btn-primary" id="file_path"
									data-bs-toggle="modal" data-bs-target="#file-previewer"
									onclick="showLoadedFile('{{ url(Storage::url($announcement->file_path)) }}')">
						Document officiel
					</button>
				</div>
			@endif

			@include('admin.announcements._preview-modal')
		</div>
	</div>
@endsection

@section('other-js')
	<script>
		function showLoadedFile(url) {
			document.getElementById('pdf-viewer').data = url;
		}
	</script>
@endsection