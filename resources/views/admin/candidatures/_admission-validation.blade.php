<div class="row">
	<div class="col-12">
		@if($admisCandidatures->isNotEmpty())
			<div class="card">
				<div class="card-header text-end">
					<button class="btn btn-primary" onclick="manageListSubmission()">
						Soumettre la liste
					</button>
				</div>
				<div class="card-body">
					<div class="dt-responsive table-responsive">
						<form action="{{ route('admin.candidatures.admission-sub') }}" method="post" id="admission-form">
							<table id="dom-jquery" class="table table-striped table-bordered nowrap">
								<thead>
								<tr>
									<th scope="col">#</th>
									<th scope="col">Nom & prénoms</th>
									<th scope="col">Code</th>
									<th scope="col" class="text-center">Actions</th>
								</tr>
								</thead>
								@csrf
								<tbody>
								@foreach($admisCandidatures as $key => $candidat)
									<tr>
										<td>{{ $key +=1 }}</td>
										<td>{{ $candidat->nom . ' ' . $candidat->prenom }} </td>
										<td>{{ $candidat->code }}</td>
										<td class="text-center">
											<div class="form-check form-check-inline">
												<input class="form-check-input admis" type="radio" name="candidat-{{$candidat->slug}}"
															 value="{{ $candidat->slug }}" checked
															 id="{{ 'candidat-admis-'. $candidat->slug }}">
												<label class="form-check-label" for="{{ 'candidat-admis-'. $candidat->slug }}">&nbsp;Admis</label>
											</div>
											<div class="form-check form-check-inline">
												<input class="form-check-input recale" type="radio" name="candidat-{{$candidat->slug}}"
															 value="{{ $candidat->slug }}"
															 id="{{ 'candidat-recale-'. $candidat->slug }}">
												<label class="form-check-label" for="{{ 'candidat-recale-'. $candidat->slug }}">&nbsp;Recalé</label>
											</div>
										</td>
									</tr>
								@endforeach
								</tbody>
								<tfoot>
								<tr>
									<th scope="col">#</th>
									<th scope="col">Nom & prénoms</th>
									<th scope="col">Code</th>
									<th scope="col" class="text-center">Actions</th>
								</tr>
								</tfoot>
							</table>
							<input type="hidden" name="admis" id="admis">
							<input type="hidden" name="recales" id="recales">
						</form>
					</div>
				</div>
			</div>
		@else
			<x-empty-table content="Aucune candidature a afficher dans cette section"/>
		@endif
	</div>
</div>
