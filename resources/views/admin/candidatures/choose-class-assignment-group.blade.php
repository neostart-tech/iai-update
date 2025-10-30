@extends('base', [
	'title' => 'Attribution de groupe',
	'breadcrumbs' => [
		'Administration',[
			'text' =>'Candidatures',
			'url' => route('admin.candidatures.index')
		],
		'Attribution de groupe'
	],
	'page_name' => 'Attribution de groupes aux candidats'
])

@section('content')
	@if($groups->isNotEmpty())
		<div class="card">
			<div class="card-header text-center">
				<h3>Choisissez un groupe</h3>
			</div>
			<div class="card-body">
				<div class="col-12">
					<div class="tab-content" id="pills-tabContent">
						<div class="tab-pane fade show active">
							<div class="row">
								@foreach($groups as $group)
									<div class="col-md-6">
										<div class="card">
											<div class="card-body">
												<div class="row align-items-center">
													<div class="col-8">
														<h3 class="mb-1">{{ $group->nom . ' - ' .$group->filiere->code }}</h3>
														<p class="text-muted mb-0">{{ $group->etudiants_count }} Étudiants</p>
													</div>
													<div class="col-4 text-end element"
															 onclick="document.getElementById('group-{{$group->slug}}').click();">
														<a href="{{ route('admin.candidatures.show-class-assignment-view', $group) }}"
															 id="group-{{$group->slug}}"></a>
														<i class="ti ti-chevron-right text-primary f-36"></i>
													</div>
												</div>
											</div>
										</div>
									</div>
								@endforeach
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	@else
		<x-empty-table content="Aucun groupe n'a encore été enregistré"/>
	@endif
@endsection

@section('other-css')
	<style>
		.element:hover {
			cursor: pointer;
		}
	</style>
@endsection