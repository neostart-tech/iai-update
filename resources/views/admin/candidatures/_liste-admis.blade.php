<div class="tab-pane" id="profile-5" role="tabpanel" aria-labelledby="profile-tab-5">
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-body">
					@if($futursEtudiants->isNotEmpty())
						<div class="dt-responsive table-responsive">
							<table id="dom-jquery-4" class="table table-hover">
								<thead>
								<tr>
									<th scope="col">#</th>
									<th scope="col">Nom</th>
									<th scope="col">Prénoms</th>
									<th scope="col" class="text-center">Actions</th>
								</tr>
								</thead>
								<tbody>
								@foreach($futursEtudiants as $key => $candidature)
									<tr>
										<th scope="row">{{ $key+=1 }}</th>
										<td>{{ $candidature->nom  }}</td>
										<td>{{ $candidature->prenom  }}</td>
										<td class="text-center">
											<ul class="list-inline me-auto mb-0">
												<li class="list-inline-item align-bottom" data-bs-toggle="tooltip" title="Aperçu">
													<a href="{{ route('admin.candidatures.show', $candidature) }}"
														 class="avtar avtar-xs btn-link-secondary btn-pc-default">
														<i class="ti ti-eye f-18"></i>
													</a>
												</li>
											</ul>
										</td>
									</tr>
								@endforeach
								</tbody>
								<tfoot>
								<tr>
									<th scope="col">#</th>
									<th scope="col">Nom</th>
									<th scope="col">Prénoms</th>
									<th scope="col" class="text-center">Actions</th>
								</tr>
								</tfoot>
							</table>
						</div>
					@else
						<x-empty-table content="Aucune candidature a afficher dans cette section"/>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>