@extends('base', [
	'title' => 'Détails d\'un étudiant',
	'page_title' => 'Détails d\'un étudiant',
	'page_name' => 'Détails d\'un étudiant',
	'breadcrumbs' => [
		'Liste des étudiants',
		'Détails d\'un étudiant',
		$etudiant->nom . ' ' . $etudiant->prenom . ' ['. $etudiant->matricule .']'
]
])

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="form-group col-md-6">
					<x-forms.label for="matricule" content="Numero matricule" required="0"/>
					<input class="form-control" type="text" value="{{ $etudiant->matricule }}" id="matricule" readonly>
				</div>
				<div class="form-group col-md-6">
					<x-forms.label for="matricule" content="Groupe" required="0"/>
					<input class="form-control" type="text" value="{{ $etudiant->group->first()->nom }}" id="matricule" readonly>
				</div>
			</div>
			<div class="accordion accordion-flush" id="accordionFlushExample">
				<div class="accordion-item">
					<h2 class="accordion-header" id="flush-headingOne">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
										data-bs-target="#flush-collapseOne" aria-expanded="false"
										aria-controls="flush-collapseOne">
							Informations de l'étudiant
						</button>
					</h2>
					<div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne"
							 data-bs-parent="#accordionFlushExample">
						<div class="accordion-body row">

							<div class="form-group col-12 col-md-6">
								<label for="nom" class="form-label">Nom </label>
								<input type="text" id="nom" class="form-control" value="{{ $etudiant->nom }}" readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="prenom" class="form-label">Prénom </label>
								<input type="text" id="prenom" class="form-control" value="{{ $etudiant->prenom }}" readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="nom_jeune_fille" class="form-label">Nom de jeune fille </label>
								<input type="text" id="nom_jeune_fille" class="form-control" value="{{ $etudiant->nom_jeune_fille }}"
											 readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="genre" class="form-label">Genre </label>
								<input type="text" id="genre" class="form-control" value="{{ $etudiant->genre }}" readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="date_naissance" class="form-label">Date de naissance </label>
								<input type="text" id="date_naissance" class="form-control"
											 value="{{ $etudiant->date_naissance->translatedFormat('d F Y') }}"
											 readonly>
							</div>


							<div class="form-group col-12 col-md-6">
								<label for="lieu_naissance" class="form-label">Lieu de naissance </label>
								<input type="text" id="lieu_naissance" class="form-control" value="{{ $etudiant->lieu_naissance }}"
											 readonly>
							</div>


							<div class="form-group col-12 col-md-6">
								<label for="tel" class="form-label">Téléphone </label>
								<input type="text" id="tel" class="form-control" value="{{ $etudiant->tel }}" readonly>
							</div>


							<div class="form-group col-12 col-md-6">
								<label for="nationalite" class="form-label">Nationalité </label>
								<input type="text" id="nationalite" class="form-control" value="{{ $etudiant->nationalite }}" readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="pb" class="form-label">Boîte postale </label>
								<input type="text" id="pb" class="form-control" value="{{ $etudiant->pb }}" readonly>
							</div>


							<div class="form-group col-12 col-md-6">
								<label for="fax" class="form-label">Fax </label>
								<input type="text" id="fax" class="form-control" value="{{ $etudiant->fax }}" readonly>
							</div>

							<div class="form-group col-12">
								<label for="nom" class="form-label">Centres d'intérêt </label>
								<textarea class="form-control" id="hobbits" cols="30" rows="3" readonly>{{ $etudiant->hobbit}}</textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="accordion-item">
					<h2 class="accordion-header" id="flush-headingTwo">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
										data-bs-target="#flush-collapseTwo" aria-expanded="false" aria-controls="flush-collapseTwo">
							Personne responsable des frais de formation
						</button>
					</h2>
					<div id="flush-collapseTwo" class="accordion-collapse collapse" aria-labelledby="flush-headingTwo"
							 data-bs-parent="#accordionFlushExample">
						<div class="accordion-body row">

							<div class="form-group col-12 col-md-6">
								<label for="responsable_nom" class="form-label">Nom </label>
								<input type="text" id="responsable_nom" class="form-control" value="{{ $etudiant->responsable->nom }}"
											 readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="responsable_prenom" class="form-label">Prénom(s) </label>
								<input type="text" id="responsable_prenom" class="form-control"
											 value="{{ $etudiant->responsable->prenom }}" readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="responsable_profession" class="form-label">Profession </label>
								<input type="text" id="responsable_profession" class="form-control"
											 value="{{ $etudiant->responsable->profession }}" readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="responsable_employeur" class="form-label">Nom de l'employeur </label>
								<input type="text" id="responsable_employeur" class="form-control"
											 value="{{ $etudiant->responsable->employeur }}" readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="responsable_email" class="form-label">Email </label>
								<input type="text" id="responsable_email" class="form-control"
											 value="{{ $etudiant->responsable->email }}" readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="responsable_tel" class="form-label">Téléphone </label>
								<input type="text" id="responsable_tel" class="form-control" value="{{ $etudiant->responsable->tel }}"
											 readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="responsable_adresse" class="form-label">Adresse </label>
								<input type="text" id="responsable_adresse" class="form-control"
											 value="{{ $etudiant->responsable->adresse }}" readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="responsable_fax" class="form-label">Fax </label>
								<input type="text" id="responsable_fax" class="form-control" value="{{ $etudiant->responsable->fax }}"
											 readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="responsable_bp" class="form-label">Boîte postale </label>
								<input type="text" id="responsable_bp" class="form-control" value="{{ $etudiant->bp }}" readonly>
							</div>

						</div>
					</div>
				</div>
				<div class="accordion-item">
					<h2 class="accordion-header" id="flush-headingThree">
						<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
										data-bs-target="#flush-collapseThree" aria-expanded="false" aria-controls="flush-collapseThree">
							Parent ou tuteur
						</button>
					</h2>
					<div id="flush-collapseThree" class="accordion-collapse collapse" aria-labelledby="flush-headingThree"
							 data-bs-parent="#accordionFlushExample">
						<div class="accordion-body row">

							<div class="form-group col-12 col-md-6">
								<label for="tuteur_nom" class="form-label">Nom </label>
								<input type="text" id="responsable_nom" class="form-control" value="{{ $etudiant->tuteur->nom }}"
											 readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="tuteur_prenom" class="form-label">Prénom(s) </label>
								<input type="text" id="responsable_prenom" class="form-control" value="{{ $etudiant->tuteur->prenom }}"
											 readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="tuteur_profession" class="form-label">Profession </label>
								<input type="text" id="responsable_profession" class="form-control"
											 value="{{ $etudiant->tuteur->profession }}" readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="tuteur_employeur" class="form-label">Nom de l'employeur </label>
								<input type="text" id="responsable_employeur" class="form-control"
											 value="{{ $etudiant->tuteur->employeur }}" readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="tuteur_email" class="form-label">Email </label>
								<input type="text" id="responsable_email" class="form-control" value="{{ $etudiant->tuteur->email }}"
											 readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="tuteur_tel" class="form-label">Téléphone </label>
								<input type="text" id="responsable_tel" class="form-control" value="{{ $etudiant->tuteur->tel }}"
											 readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="tuteur_adresse" class="form-label">Adresse </label>
								<input type="text" id="responsable_adresse" class="form-control" value="{{ $etudiant->tuteur->adresse }}"
											 readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="responsable_fax" class="form-label">Fax </label>
								<input type="text" id="responsable_fax" class="form-control" value="{{ $etudiant->responsable->fax }}"
											 readonly>
							</div>

							<div class="form-group col-12 col-md-6">
								<label for="responsable_bp" class="form-label">Boîte postale </label>
								<input type="text" id="responsable_bp" class="form-control" value="{{ $etudiant->bp }}" readonly>
							</div>

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
