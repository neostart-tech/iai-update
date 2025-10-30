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
                                    readonly>                            </div>

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
    


    {{-- {{dd($album)}} --}}
    <div class="card">
        <div class="card-body">
            <div class="col-12 file-manger-wrapper">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-home" role="tabpanel" tabindex="0">
                        <div class="row">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer row">
            <div class="col-md-3 col-lg-4">
                <button data-pc-animate="fade-in-scale" type="button" class="btn btn-success col-12 mb-3"
                    data-bs-toggle="modal" data-bs-target="#validationModal">
                    Valider la demande
                </button>
            </div>
            <div class="col-md-3 col-lg-4">
                <button data-pc-animate="fade-in-scale" type="button" class="btn btn-warning col-12 mb-3"
                    data-bs-toggle="modal" data-bs-target="#rejectionModal">
                    Rejeter la demande
                </button>
            </div>
            <div class="col-md-6 col-lg-4">
                <button data-pc-animate="fade-in-scale" type="button" class="btn btn-secondary col-12 mb-3"
                    data-bs-toggle="modal" data-bs-target="#rectificationModal">
                    Demander une rectification
                </button>
            </div>
        </div>
    </div>

    {{-- Bulletins & Relevés liés à la candidature --}}
    <div class="card mt-3">
        <div class="card-header">Bulletins et relevés <span class="badge bg-secondary ms-2">{{ collect($candidature->documents)->count() }}</span></div>
        <div class="card-body">

            <div class="row g-3">
                @foreach(['seconde' => 'Seconde', 'premiere' => 'Première', 'terminale' => 'Terminale'] as $key => $label)
                    <div class="col-md-4">
                        <div class="border rounded h-100 p-2">
                            <div class="fw-semibold mb-2">Bulletins {{ $label }}</div>
                            @php($items = collect($candidature->documents)->where('type','bulletin')->where('niveau', $key))
                            @if($items->count())
                                <div class="row g-2">
                                    @foreach($items as $i => $doc)
                                        @php($url = Storage::url($doc->path))
                                        <div class="col-6">
                                            <div class="border rounded p-2 h-100 d-flex flex-column">
                                                <div class="small mb-2">Bulletin {{ $i+1 }}</div>
                                                @php($isImage = \Illuminate\Support\Str::endsWith(strtolower($doc->path), ['.jpg','.jpeg','.png','.webp']))
                                                <a href="#" class="d-block" onclick="showLoadedFile('{{ $url }}')" data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">
                                                    @if($isImage)
                                                        <img src="{{ $url }}" alt="Bulletin {{ $label }} {{ $i+1 }}" class="img-fluid rounded" />
                                                    @else
                                                        <div class="flex-fill d-flex align-items-center justify-content-center text-muted" style="min-height:80px;">
                                                            <i class="bi bi-file-earmark-pdf" style="font-size:24px"></i>
                                                        </div>
                                                        <span class="btn btn-sm btn-outline-success mt-2">Aperçu</span>
                                                    @endif
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">Aucun bulletin</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <div class="border rounded h-100 p-2">
                        <div class="fw-semibold mb-2">Relevés BAC 1</div>
                        @php($r1 = collect($candidature->documents)->where('type','releve')->where('niveau','bac1'))
                        @if($r1->count())
                            <div class="row g-2">
                                @foreach($r1 as $i => $doc)
                                    @php($url = Storage::url($doc->path))
                                    <div class="col-6">
                                        <div class="border rounded p-2 h-100 d-flex flex-column">
                                            <div class="small mb-2">Relevé {{ $i+1 }}</div>
                                            @php($isImage = \Illuminate\Support\Str::endsWith(strtolower($doc->path), ['.jpg','.jpeg','.png','.webp']))
                                            <a href="#" class="d-block" onclick="showLoadedFile('{{ $url }}')" data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">
                                                @if($isImage)
                                                    <img src="{{ $url }}" alt="Relevé BAC1 {{ $i+1 }}" class="img-fluid rounded" />
                                                @else
                                                    <div class="flex-fill d-flex align-items-center justify-content-center text-muted" style="min-height:80px;">
                                                        <i class="bi bi-file-earmark-pdf" style="font-size:24px"></i>
                                                    </div>
                                                    <span class="btn btn-sm btn-outline-success mt-2">Aperçu</span>
                                                @endif
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">Aucun relevé BAC 1</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded h-100 p-2">
                        <div class="fw-semibold mb-2">Relevés BAC 2</div>
                        @php($r2 = collect($candidature->documents)->where('type','releve')->where('niveau','bac2'))
                        @if($r2->count())
                            <div class="row g-2">
                                @foreach($r2 as $i => $doc)
                                    @php($url = Storage::url($doc->path))
                                    <div class="col-6">
                                        <div class="border rounded p-2 h-100 d-flex flex-column">
                                            <div class="small mb-2">Relevé {{ $i+1 }}</div>
                                            @php($isImage = \Illuminate\Support\Str::endsWith(strtolower($doc->path), ['.jpg','.jpeg','.png','.webp']))
                                            <a href="#" class="d-block" onclick="showLoadedFile('{{ $url }}')" data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">
                                                @if($isImage)
                                                    <img src="{{ $url }}" alt="Relevé BAC2 {{ $i+1 }}" class="img-fluid rounded" />
                                                @else
                                                    <div class="flex-fill d-flex align-items-center justify-content-center text-muted" style="min-height:80px;">
                                                        <i class="bi bi-file-earmark-pdf" style="font-size:24px"></i>
                                                    </div>
                                                    <span class="btn btn-sm btn-outline-success mt-2">Aperçu</span>
                                                @endif
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">Aucun relevé BAC 2</span>
                        @endif
                    </div>
                </div>
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
@endsection

@section('other-js')
    <script>
        function showLoadedFile(file) {
            const viewer = document.getElementById('pdf-viewer');
            viewer.src = file ; 
        }

        document.querySelector('.bd-example-modal-lg').addEventListener('hidden.bs.modal', function() {
            document.getElementById('pdf-viewer').src = ''; 
        });
    </script>
@endsection
