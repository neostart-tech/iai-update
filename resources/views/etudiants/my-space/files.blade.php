@php use Illuminate\Support\Facades\Storage; @endphp
@extends('base', [
    'title' => 'Mes fichiers',
    'page_name' => 'Mes fichiers',
    'breadcrumbs' => ['Mon dossier', 'Mes fichiers'],
])

{{-- {{dd($album)}}   --}}
@section('content')
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
                                                onclick="showLoadedFile({{ Storage::url($album->lettre) }})"
                                                data-bs-toggle="modal" data-bs-target="#file-previewer">
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
                                                <h6 class="mb-0"><span class="text-truncate w-100">Extrait de ssss
                                                        naissance</span></h6>
                                            </div>
                                            <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                                onclick="showLoadedFile('{{ Storage::url($album->naissance) }}')"
                                                data-bs-toggle="modal" data-bs-target="#file-previewer">
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
                                                onclick="showLoadedFile('{{ Storage::url($album->nationalite) }}')"
                                                data-bs-toggle="modal" data-bs-target="#file-previewer">
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
                                                <h6 class="mb-0"><span class="text-truncate w-100">diplôme requis</span>
                                                </h6>
                                                <p class="mb-0 text-muted"><small>{{ $album->type_diplome }}</small></p>
                                            </div>
                                            <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                                onclick="showLoadedFile('{{ Storage::url($album->diplome) }}')"
                                                data-bs-toggle="modal" data-bs-target="#file-previewer">
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
                                                <h6 class="mb-0"><span class="text-truncate w-100">Photo d'identité</span>
                                                </h6>
                                            </div>
                                            <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                                onclick="showLoadedFile('{{ Storage::url($album->photo) }}')"
                                                data-bs-toggle="modal" data-bs-target="#file-previewer">
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
                                                onclick="showLoadedFile('{{ Storage::url($album->certificat_medical) }}')"
                                                data-bs-toggle="modal" data-bs-target="#file-previewer">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if ($album->coupon)
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
                                                    onclick="showLoadedFile('{{ Storage::url($album->coupon) }}')"
                                                    data-bs-toggle="modal" data-bs-target="#file-previewer">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ($album->cv)
                                <div class="col-md-6 col-lg-4 col-xxl-3">
                                    <div class="card file-card">
                                        <div class="card-body">
                                            <div class="my-3 text-center">
                                                <img src="{{ asset('admin/assets/images/application/img-file-pdf.svg') }}"
                                                    alt="img" class="img-fluid" />
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mt-4">
                                                <div>
                                                    <h6 class="mb-0"><span class="text-truncate w-100">Curriculum
                                                            Vitae</span></h6>
                                                </div>
                                                <div class="text-end gap-5">
                                                    <a href="{{ route('etudiants.cv.download') }}"
                                                        class="avtar avtar-s btn-light-secondary user-popup">
                                                        <i class="fa fa-download"></i>
                                                    </a>
                                                    {{-- <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                                        data-bs-toggle="modal" data-bs-target="#file-previewer">
                                                        <i class="fa fa-trash"
                                                            onclick="document.getElementById('delete-form').submit()"></i>
                                                    </a> --}}
                                                    <a href="#" class="avtar avtar-s btn-light-secondary user-popup"
                                                        onclick="showLoadedFile('{{ Storage::url($album->cv) }}')"
                                                        data-bs-toggle="modal" data-bs-target="#file-previewer">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                </div>
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
    </div>
    @include('officiel.mySpace._preview-modal')

    <form action="{{ route('etudiants.cv.delete') }}" method="post" id="delete-form">@csrf @method('delete')</form>
@endsection

@section('other-js')
    <script>
        // function showLoadedFile(url) {
        // 	document.getElementById('pdf-viewer').data = url;
        // }

        console.log("URLs des fichiers:");
        console.log("Lettre: {{ Storage::url($album->lettre) }}");
        console.log("Diplôme: {{ Storage::url($album->diplome) }}");

        function showLoadedFile(file) {
            const viewers = document.getElementById('pdf-viewer');
            console.log(file)
            viewers.src = file;
        }
        //document.querySelector('.bd-example-modal-lg').addEventListener('hidden.bs.modal', function() {
          //  document.getElementById('pdf-viewer').src = '';
     //   });
    </script>
@endsection
