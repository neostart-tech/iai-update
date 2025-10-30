<div class="row">
	<div class="col-12">
		@if($payementCandidatures->isNotEmpty())
			<x-empty-table content="Ne choisissez que les candidats qui ont payé" class="info"/>
			<div class="card">
				<div class="card-header text-end">
					<button type="submit" class="btn btn-primary" onclick="handlePayeFormSubmit()">Valider la sélection</button>
				</div>
				<div class="card-body">
					<form action="{{ route('admin.candidatures.payement-des-frais-de-participation.store') }}" id="paye-form"
								method="post">
						@csrf
						<div class="dt-responsive table-responsive">
							<table id="dom-jquery" class="table table-hover">
								<thead>
								<tr>
									<th scope="col">#</th>
									<th scope="col">Nom & Prénoms</th>
									<th>Code</th>
									<th scope="col" class="text-center">Actions</th>
								</tr>
								</thead>
								<tbody>
								@foreach($payementCandidatures as $key => $candidature)
									<tr>
										<th scope="row">{{ $key+1 }}</th>
										<td>{{ $candidature->nom . ' ' . $candidature->prenom  }}</td>
										<td>{{ $candidature->code }}</td>
										<td class="text-center">
											<x-forms.label content="Marqué comme ayant payé" for="{{ $candidature->slug }}" required="0"
																		 class="form-check-label"/> &nbsp;
											<input type="checkbox" class="form-check-input" value="{{ $candidature->slug }}" name="paye[]"
														 id="{{ $candidature->slug }}">
										</td>
									</tr>
								@endforeach
								</tbody>
								<tfoot>
								<tr>
									<th scope="col">#</th>
									<th scope="col">Nom & Prénoms</th>
									<th scope="col">Code</th>
									<th scope="col" class="text-center">Actions</th>
								</tr>
								</tfoot>
							</table>
						</div>
					</form>
				</div>
			</div>
		@else
			<x-empty-table content="Aucune candidature a afficher dans cette section"/>
		@endif
	</div>
</div>
