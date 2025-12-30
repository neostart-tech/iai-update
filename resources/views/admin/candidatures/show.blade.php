@extends('base', [
'title' => 'Évaluation d\'une candidature',
'breadcrumbs' => [
[
'text' => 'Candidatures',
'url' => route('admin.candidatures.index'),
],
'Évaluation',
$candidature->nom . ' ' . $candidature->prenom . ' [' . $candidature->code . ']',
],
'page_name' => 'Évaluation d\'une candidature',
])

@section('content')
<div class="card">
    <div class="card-body">
        <div class="accordion accordion-flush" id="accordionFlushExample">
            <div class="accordion-item">
                <h2 class="accordion-header" id="flush-headingOne">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">
                        Identité du candidat
                    </button>
                </h2>
                <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne"
                    data-bs-parent="#accordionFlushExample">
                    <div class="accordion-body row">

                        <div class="form-group col-12 col-md-6">
                            <label for="nom" class="form-label">Nom </label>
                            <input type="text" id="nom" class="form-control" value="{{ $candidature->nom }}"
                                readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="prenom" class="form-label">Prénom </label>
                            <input type="text" id="prenom" class="form-control" value="{{ $candidature->prenom }}"
                                readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="nom_jeune_fille" class="form-label">Nom de jeune fille </label>
                            <input type="text" id="nom_jeune_fille" class="form-control"
                                value="{{ $candidature->nom_jeune_fille }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="genre" class="form-label">Genre </label>
                            <input type="text" id="genre" class="form-control" value="{{ $candidature->genre }}"
                                readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="date_naissance" class="form-label">Date de naissance </label>
                            <input type="text" id="date_naissance" class="form-control"
                                value="{{ $candidature->date_naissance->translatedFormat('d F Y') }}" readonly>
                        </div>


                        <div class="form-group col-12 col-md-6">
                            <label for="lieu_naissance" class="form-label">Lieu de naissance </label>
                            <input type="text" id="lieu_naissance" class="form-control"
                                value="{{ $candidature->lieu_naissance }}" readonly>
                        </div>


                        <div class="form-group col-12 col-md-6">
                            <label for="tel" class="form-label">Téléphone </label>
                            <input type="text" id="tel" class="form-control" value="{{ $candidature->tel }}"
                                readonly>
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label for="tel" class="form-label">Téléphone 2 </label>
                            <input type="text" id="tel" class="form-control" value="{{ $candidature->tel2 ?? 'Non fournie' }}"
                                readonly>
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label for="tel" class="form-label">Téléphone 3 </label>
                            <input type="text" id="tel" class="form-control" value="{{ $candidature->tel3 ?? 'Non fournie' }}"
                                readonly>
                        </div>
                        <div class="form-group col-12 col-md-6">
                            <label for="tel" class="form-label">Email </label>
                            <input type="text" id="tel" class="form-control" value="{{ $candidature->email }}"
                                readonly>
                        </div>


                        <div class="form-group col-12 col-md-6">
                            <label for="nationalite" class="form-label">Nationalité </label>
                            <input type="text" id="nationalite" class="form-control"
                                value="{{ $candidature->nationalite }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="pb" class="form-label">Boîte postale </label>
                            <input type="text" id="pb" class="form-control" value="{{ $candidature->pb }}"
                                readonly>
                        </div>


                        <div class="form-group col-12 col-md-6">
                            <label for="fax" class="form-label">Fax </label>
                            <input type="text" id="fax" class="form-control" value="{{ $candidature->fax }}"
                                readonly>
                        </div>

                        <div class="form-group col-12">
                            <label for="nom" class="form-label">Centres d'intérêt </label>
                            <textarea class="form-control" id="hobbits" cols="30" rows="3" readonly>{{ $candidature->hobbit }}</textarea>
                        </div>
                        <div class="form-group col-12">
                            <label for="nom" class="form-label">Niveau d'études </label>
                            <input type="text" id="fax" class="form-control" value="{{ $niveau->libelle }}"
                                readonly>
                        </div>
                        <div class="form-group col-12">
                            <label for="nom" class="form-label">Filiere </label>

                            <input type="text" id="fax" class="form-control" value="{{ $filiere->nom }}"
                                readonly>
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
                            <input type="text" id="responsable_nom" class="form-control"
                                value="{{ $candidature->responsable->nom }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="responsable_prenom" class="form-label">Prénom(s) </label>
                            <input type="text" id="responsable_prenom" class="form-control"
                                value="{{ $candidature->responsable->prenom }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="responsable_profession" class="form-label">Profession </label>
                            <input type="text" id="responsable_profession" class="form-control"
                                value="{{ $candidature->responsable->profession }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="responsable_employeur" class="form-label">Nom de l'employeur </label>
                            <input type="text" id="responsable_employeur" class="form-control"
                                value="{{ $candidature->responsable->employeur }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="responsable_email" class="form-label">Email </label>
                            <input type="text" id="responsable_email" class="form-control"
                                value="{{ $candidature->responsable->email }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="responsable_tel" class="form-label">Téléphone </label>
                            <input type="text" id="responsable_tel" class="form-control"
                                value="{{ $candidature->responsable->tel }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="responsable_adresse" class="form-label">Adresse </label>
                            <input type="text" id="responsable_adresse" class="form-control"
                                value="{{ $candidature->responsable->adresse }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="responsable_fax" class="form-label">Fax </label>
                            <input type="text" id="responsable_fax" class="form-control"
                                value="{{ $candidature->responsable->fax }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="responsable_bp" class="form-label">Boîte postale </label>
                            <input type="text" id="responsable_bp" class="form-control"
                                value="{{ $candidature->bp }}" readonly>
                        </div>

                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="flush-headingThree">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#flush-collapseThree" aria-expanded="false"
                        aria-controls="flush-collapseThree">
                        Parent ou tuteur
                    </button>
                </h2>
                <div id="flush-collapseThree" class="accordion-collapse collapse"
                    aria-labelledby="flush-headingThree" data-bs-parent="#accordionFlushExample">
                    <div class="accordion-body row">

                        <div class="form-group col-12 col-md-6">
                            <label for="tuteur_nom" class="form-label">Nom </label>
                            <input type="text" id="responsable_nom" class="form-control"
                                value="{{ $candidature->tuteur->nom }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="tuteur_prenom" class="form-label">Prénom(s) </label>
                            <input type="text" id="responsable_prenom" class="form-control"
                                value="{{ $candidature->tuteur->prenom }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="tuteur_profession" class="form-label">Profession </label>
                            <input type="text" id="responsable_profession" class="form-control"
                                value="{{ $candidature->tuteur->profession }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="tuteur_employeur" class="form-label">Nom de l'employeur </label>
                            <input type="text" id="responsable_employeur" class="form-control"
                                value="{{ $candidature->tuteur->employeur }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="tuteur_email" class="form-label">Email </label>
                            <input type="text" id="responsable_email" class="form-control"
                                value="{{ $candidature->tuteur->email }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="tuteur_tel" class="form-label">Téléphone </label>
                            <input type="text" id="responsable_tel" class="form-control"
                                value="{{ $candidature->tuteur->tel }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="tuteur_adresse" class="form-label">Adresse </label>
                            <input type="text" id="responsable_adresse" class="form-control"
                                value="{{ $candidature->tuteur->adresse }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="responsable_fax" class="form-label">Fax </label>
                            <input type="text" id="responsable_fax" class="form-control"
                                value="{{ $candidature->responsable->fax }}" readonly>
                        </div>

                        <div class="form-group col-12 col-md-6">
                            <label for="responsable_bp" class="form-label">Boîte postale </label>
                            <input type="text" id="responsable_bp" class="form-control"
                                value="{{ $candidature->bp }}" readonly>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>




<div class="card">
    <div class="card-body">
        <div class="col-12 file-manger-wrapper">
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-home" role="tabpanel" tabindex="0">
                    <div class="row">
                        {{-- Lettre manuscrite --}}
                        <div class="col-md-6 col-lg-4 col-xxl-3">
                            <div class="card file-card">
                                <div class="card-body">
                                    <div class="my-3 text-center">
                                        <img src="{{ asset('admin/assets/images/application/img-file-pdf.svg') }}"
                                            alt="img" class="img-fluid" />
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-4">
                                        <div>
                                            <h6 class="mb-0"><span class="text-truncate w-100">Lettre
                                                    manuscrite</span></h6>
                                        </div>
                                        <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            @if(optional($album)->lettre)
                                            onclick="showLoadedFile('{{ Storage::url(optional($album)->lettre) }}')"
                                            data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg"
                                            @else
                                            onclick="return false;" aria-disabled="true"
                                            @endif>
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Extrait de naissance --}}
                        <div class="col-md-6 col-lg-4 col-xxl-3">
                            <div class="card file-card">
                                <div class="card-body">
                                    <div class="my-3 text-center">
                                        <img src="{{ asset('admin/assets/images/application/img-file-pdf.svg') }}"
                                            alt="img" class="img-fluid" />
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-4">
                                        <div>
                                            <h6 class="mb-0"><span class="text-truncate w-100">Extrait de
                                                    naissance</span></h6>
                                        </div>
                                        <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            @if(optional($album)->naissance)
                                            onclick="showLoadedFile('{{ Storage::url(optional($album)->naissance) }}')"
                                            data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg"
                                            @else
                                            onclick="return false;" aria-disabled="true"
                                            @endif>
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Certificat de nationalité --}}
                        <div class="col-md-6 col-lg-4 col-xxl-3">
                            <div class="card file-card">
                                <div class="card-body">
                                    <div class="my-3 text-center">
                                        <img src="{{ asset('admin/assets/images/application/img-file-pdf.svg') }}"
                                            alt="img" class="img-fluid" />
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-4">
                                        <div>
                                            <h6 class="mb-0"><span class="text-truncate w-100">Certificat de
                                                    nationalité</span></h6>
                                        </div>
                                        <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            @if(optional($album)->nationalite)
                                            onclick="showLoadedFile('{{ Storage::url(optional($album)->nationalite) }}')"
                                            data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg"
                                            @else
                                            onclick="return false;" aria-disabled="true"
                                            @endif>
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Diplôme requis --}}
                        <div class="col-md-6 col-lg-4 col-xxl-3">
                            <div class="card file-card">
                                <div class="card-body">
                                    <div class="my-3 text-center">
                                        <img src="{{ asset('admin/assets/images/application/img-file-pdf.svg') }}"
                                            alt="img" class="img-fluid" />
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-4">
                                        <div>
                                            <h6 class="mb-0"><span class="text-truncate w-100">Diplôme requis</span>
                                            </h6>
                                            <p class="mb-0 text-muted"><small>{{ optional($album)->type_diplome }}</small></p>
                                        </div>
                                        <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            @if(optional($album)->diplome)
                                            onclick="showLoadedFile('{{ Storage::url(optional($album)->diplome) }}')"
                                            data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg"
                                            @else
                                            onclick="return false;" aria-disabled="true"
                                            @endif>
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Photo d'identité --}}
                        <div class="col-md-6 col-lg-4 col-xxl-3">
                            <div class="card file-card">
                                <div class="card-body">
                                    <div class="my-3 text-center">
                                        <img src="{{ asset('admin/assets/images/application/img-file-img.svg') }}"
                                            alt="img" class="img-fluid" />
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-4">
                                        <div>
                                            <h6 class="mb-0"><span class="text-truncate w-100">Photo
                                                    d'identité</span></h6>
                                        </div>
                                        <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            @if(optional($album)->photo)
                                            onclick="showLoadedFile('{{ Storage::url(optional($album)->photo) }}')"
                                            data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg"
                                            @else
                                            onclick="return false;" aria-disabled="true"
                                            @endif>
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Certificat médical --}}
                        <div class="col-md-6 col-lg-4 col-xxl-3">
                            <div class="card file-card">
                                <div class="card-body">
                                    <div class="my-3 text-center">
                                        <img src="{{ asset('admin/assets/images/application/img-file-pdf.svg') }}"
                                            alt="img" class="img-fluid" />
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-4">
                                        <div>
                                            <h6 class="mb-0"><span class="text-truncate w-100">Certificat
                                                    médical</span></h6>
                                        </div>
                                        <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            @if(optional($album)->certificat_medical)
                                            onclick="showLoadedFile('{{ Storage::url(optional($album)->certificat_medical) }}')"
                                            data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg"
                                            @else
                                            onclick="return false;" aria-disabled="true"
                                            @endif>
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Coupon réponse --}}
                        @if (optional($album)->coupon)
                        <div class="col-md-6 col-lg-4 col-xxl-3">
                            <div class="card file-card">
                                <div class="card-body">
                                    <div class="my-3 text-center">
                                        <img src="{{ asset('admin/assets/images/application/img-file-pdf.svg') }}"
                                            alt="img" class="img-fluid" />
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-4">
                                        <div>
                                            <h6 class="mb-0"><span class="text-truncate w-100">Coupon
                                                    réponse</span></h6>
                                        </div>
                                        <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            onclick="showLoadedFile('{{ Storage::url(optional($album)->coupon) }}')"
                                            data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif


                        @if(optional($album)->bulletins_lycee_paths)
                        @php
                        $bulletins = json_decode(optional($album)->bulletins_lycee_paths, true);
                        @endphp

                        @foreach(['seconde' => 'Seconde', 'premiere' => 'Première', 'terminale' => 'Terminale'] as $niveau => $label)
                        @if(isset($bulletins[$niveau]) && count($bulletins[$niveau]) > 0)
                        @foreach($bulletins[$niveau] as $index => $path)
                        <div class="col-md-6 col-lg-4 col-xxl-3">
                            <div class="card file-card">
                                <div class="card-body">
                                    <div class="my-3 text-center">
                                        <img src="{{ asset('admin/assets/images/application/img-file-img.svg') }}"
                                            alt="img" class="img-fluid" />
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-4">
                                        <div>
                                            <h6 class="mb-0">
                                                <span class="text-truncate w-100">Bulletin {{ $label }}</span>
                                            </h6>
                                            <p class="mb-0 text-muted">
                                                <small>Fichier {{ $index + 1 }}</small>
                                            </p>
                                        </div>
                                        <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            onclick="showLoadedFile('{{ Storage::url($path) }}')"
                                            data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @endif
                        @endforeach
                        @endif

                        @if(optional($album)->releve_bac1_path)
                        <div class="col-md-6 col-lg-4 col-xxl-3">
                            <div class="card file-card">
                                <div class="card-body">
                                    <div class="my-3 text-center">
                                        <img src="{{ asset('admin/assets/images/application/img-file-img.svg') }}"
                                            alt="img" class="img-fluid" />
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-4">
                                        <div>
                                            <h6 class="mb-0">
                                                <span class="text-truncate w-100">Relevé BAC 1</span>
                                            </h6>
                                        </div>
                                        <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            onclick="showLoadedFile('{{ Storage::url(optional($album)->releve_bac1_path) }}')"
                                            data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif


                        @if(optional($album)->releve_bac2_path)
                        <div class="col-md-6 col-lg-4 col-xxl-3">
                            <div class="card file-card">
                                <div class="card-body">
                                    <div class="my-3 text-center">
                                        <img src="{{ asset('admin/assets/images/application/img-file-img.svg') }}"
                                            alt="img" class="img-fluid" />
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-4">
                                        <div>
                                            <h6 class="mb-0">
                                                <span class="text-truncate w-100">Relevé BAC 2</span>
                                            </h6>
                                        </div>
                                        <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                            onclick="showLoadedFile('{{ Storage::url(optional($album)->releve_bac2_path) }}')"
                                            data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif


                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer row">
        <div class="col-md-3 col-lg-3">
            <button data-pc-animate="fade-in-scale" type="button" class="btn btn-success col-12 mb-3"
                data-bs-toggle="modal" data-bs-target="#validationModal">
                Valider la demande
            </button>
        </div>
        <div class="col-md-3 col-lg-3">
            <button data-pc-animate="fade-in-scale" type="button" class="btn btn-danger col-12 mb-3"
                data-bs-toggle="modal" data-bs-target="#rejectionModal">
                Rejeter la demande
            </button>
        </div>
        <div class="col-md-3 col-lg-3">
            <button data-pc-animate="fade-in-scale" type="button" class="btn btn-secondary col-12 mb-3"
                data-bs-toggle="modal" data-bs-target="#rectificationModal">
                Demander une rectification
            </button>
        </div>

        <div class="col-md-3 col-lg-3">
            <button data-pc-animate="fade-in-scale" type="button" class="btn btn-warning col-12 mb-3"
                data-bs-toggle="modal" data-bs-target="#reorientationModal">
                Réonrientation
            </button>
        </div>
    </div>
</div>

{{-- Validation --}}
<div class="modal fade modal-animate" id="validationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Validation de la candidature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Cliquer sur le bouton "Oui, valider" reviendra à marquer le dépôt de candidature de
                <b>{{ $candidature->nom . ' ' . $candidature->prenom }}</b>
                comme en règle et valide pour la suite de la procédure.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning shadow-2" data-bs-dismiss="modal"
                    onclick="document.getElementById('validation-form').submit()">Oui, valider
                </button>
            </div>
        </div>
    </div>
</div>
<form action="{{ route('admin.candidatures.validate', [$candidature]) }}" method="post" hidden
    id="validation-form">
    @csrf @method('put')
</form>

{{-- Rejet --}}
<div class="modal fade modal-animate" id="rejectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rejet catégorique d'une candidature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.candidatures.reject', [$candidature]) }}" method="post"
                    id="rejection-form">
                    @csrf @method('put')
                    <div class="form-group">
                        <label for="motif" class="form-label">Motifs du rejet de la candidature
                            <x-forms.required-field />
                        </label>
                        <textarea type="text" name="motif" id="motif" class="form-control">{{ old('motif') }}</textarea>
                    </div>
                </form>
                Cliquer sur le bouton "Oui, rejeter" reviendra à marquer le dépôt de candidature de
                <b>{{ $candidature->nom . ' ' . $candidature->prenom }}</b>
                comme <span class="text-danger">catégoriquement invalide</span> pour la suite de la procédure.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary shadow-2"
                    onclick="document.getElementById('rejection-form').submit()">Oui, rejeter
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Demande de rectification --}}
<div class="modal fade modal-animate" id="rectificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Demande de rectification sur la candidature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('admin.candidatures.ask-for-rectification', [$candidature]) }}" method="post"
                    id="ask-for-rectification-form">
                    @csrf @method('put')
                    <div class="form-group">
                        <label for="motif" class="form-label">Rectifications exigées sur la candidature
                            <x-forms.required-field />
                        </label>
                        <textarea name="motif" id="motif" class="form-control">{{ old('motif') }}</textarea>
                    </div>
                </form>
                Cliquer sur le bouton "Oui, exiger des rectifications" reviendra à marquer le dépôt de candidature de
                <b>{{ $candidature->nom . ' ' . $candidature->prenom }}</b>
                comme invalide pour la suite de la procédure et donc <span class="text-warning">nécessitant des
                    rectifications</span> pour pouvoir
                poursuivre la
                procédure de candidature au concours.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary shadow-2"
                    onclick="document.getElementById('ask-for-rectification-form').submit()">
                    Oui, exiger des rectifications
                </button>
            </div>
        </div>
    </div>
</div>



{{-- Faire une Réonrientation --}}
<div class="modal fade" id="reorientationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg">

            <!-- HEADER -->
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>
                    Réorientation de l’étudiant: {{$candidature->nom}} {{$candidature->prenom}}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body">
                <form id="reorientationForm">

                    <!-- Informations actuelles -->
                    <div class="card border-secondary mb-3">
                        <div class="card-body p-3">
                            <small class="text-muted d-block">PARCOURS ACTUEL</small>
                            <h6 class="mb-0" id="ancienFiliere">{{$candidature->filiere->nom}}</h6>
                            <div class="badge bg-secondary" id="ancienNiveau">{{$candidature->niveau->libelle}}</div>
                        </div>
                    </div>

                    <!-- Nouvelle orientation -->
                    <div class="card border-warning mb-3">
                        <div class="card-body p-3">
                            <small class="text-muted d-block">NOUVELLE ORIENTATION</small>

                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label for="newFiliere" class="form-label fw-bold">Filière</label>
                                    <select name="filiere_id" id="newFiliere" class="form-select" required data-trigger>
                                        <option value="">Choisir une filière</option>
                                        @foreach ($filieres as $f)
                                        <option value="{{$f->id}}">{{$f->nom}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="newNiveau" class="form-label fw-bold">Niveau</label>

                                    <select id="newNiveau" name="niveau_id" class="form-select" required data-trigger>
                                        @foreach ($niveaux as $niveau)
                                        <option value="{{$niveau->id}}">{{$niveau->libelle}}</option>

                                        @endforeach
                                    </select>

                                </div>



                                <div class="col-12">
                                    <label for="motif" class="form-label fw-bold">Motif de réorientation</label>
                                    <textarea id="motif" class="form-control" rows="3" name="motif">Changement de projet professionnel.</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Avertissement -->
                    <div class="alert alert-warning mt-2">
                        ⚠️ La réorientation peut entraîner un retour à un niveau inférieur selon la compatibilité académique.
                    </div>

                    <!-- FOOTER -->
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Annuler
                        </button>
                        <button type="button" class="btn btn-warning" onclick="validerReorientation()">
                            <i class="bi bi-check-circle me-1"></i> Confirmer
                        </button>
                    </div>

                </form>
            </div>



        </div>
    </div>
</div>


{{-- File preview	--}}
<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">

                <iframe id="pdf-viewer" src="" width="100%" height="500px"></iframe>
            </div>
        </div>
    </div>
</div>
@if (Session::get('success'))
    <script>
        swal.fire("{{Session::get('success')}}")
    </script>
@endif


@endsection

@section('other-js')

<script>
    function showLoadedFile(file) {
        const viewer = document.getElementById('pdf-viewer');
        viewer.src = file;
    }

    document.querySelector('.bd-example-modal-lg').addEventListener('hidden.bs.modal', function() {
        document.getElementById('pdf-viewer').src = '';
    });
</script>
<script>
 async function validerReorientation() {

    const modal = bootstrap.Modal.getInstance(
        document.getElementById('reorientationModal')
    );
    modal.hide();

    const result = await Swal.fire({
        title: 'Confirmation',
        text: 'Voulez-vous vraiment valider cette réorientation ?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Oui, valider',
        cancelButtonText: 'Annuler'
    });

    if (!result.isConfirmed) {
        Swal.fire({
            icon: 'info',
            title: 'Annulé',
            text: 'Réorientation annulée'
        });
        return;
    }

    const form = document.getElementById('reorientationForm');
    const formData = new FormData(form);

    const url = "{{ route('admin.candidatures.reorienter', $candidature->slug) }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (response.status === 201) {
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
        }
        else if (response.status === 409) {
            Swal.fire({
                icon: 'warning',
                title: 'Déjà enregistré',
                text: data.message
            });
        }
        else if (response.status === 422) {
            
            let erreurs = Object.values(data.errors).flat().join('\n');

            Swal.fire({
                icon: 'error',
                title: 'Erreur de validation',
                text: erreurs
            });
        }
        else {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: data.message ?? 'Une erreur inattendue est survenue'
            });
        }

        window.location.href="/administration/candidature/liste"

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur serveur',
            text: 'Impossible de contacter le serveur'
        });
        console.error(error);
    }
}

</script>


@endsection