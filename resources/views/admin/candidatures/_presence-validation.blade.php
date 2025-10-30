<div class="row">
	<div class="col-12">
		@if($participantCandidatures->isNotEmpty())
			<x-empty-table content="Ne choisissez que les candidats qui ont été absents" class="info"/>
			<div class="card">
				<div class="card-header text-end">
					<button class="btn btn-primary" onclick="handlePresenceFormSubmit()">
						Soumettre la liste
					</button>
				</div>
				@endif
				<div class="card-body">
					@if($participantCandidatures->isNotEmpty())
						<form action="{{ route('admin.candidatures.presence-sub') }}" method="post" id="presence-form">
							<div class="dt-responsive table-responsive">
								<table id="dom-jquery" class="table table table-hover">
									<thead>
									<tr>
										<th>#</th>
										<th>Nom</th>
										<th>Prénoms</th>
										<th>Code</th>
										<th>Action</th>
									</tr>
									</thead>
									@csrf
									<tbody>
									@foreach($participantCandidatures as $key => $candidat)
										<tr>
											<td>{{ $key +=1 }}</td>
											<td>{{ $candidat->nom }} </td>
											<td>{{ $candidat->prenom }}</td>
											<td>{{ $candidat->code }}</td>
											<td class="text-center">
												<label for="{{ $candidat->slug }}">Marquer comme absent </label> &nbsp;
												<input type="checkbox" class="form-check-input absents" name="candidats[]" id="{{ $candidat->slug }}"
															 value="{{ $candidat->slug }}">
											</td>
										</tr>
									@endforeach
									</tbody>
									<tfoot>
									<tr>
										<th>#</th>
										<th>Nom</th>
										<th>Prénoms</th>
										<th>Code</th>
										<th>Action</th>
									</tr>
									</tfoot>
								</table>
							</div>
						</form>
				</div>
				@else
					<x-empty-table content="Aucune candidature a afficher dans cette section"/>
				@endif
			</div>
	</div>
</div>