@php use Illuminate\Support\Facades\Storage; @endphp
@extends('base',[
	'title' => 'Ma candidature',
	'page_name' => 'Mes fichiers',
	'breadcrumbs' => ['Ma candidature', 'Mes fichiers']
])

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
											<img src="{{ asset('admin/assets/images/application/img-file-pdf.svg') }}" alt="img"
													 class="img-fluid"/>
										</div>
										<div class="d-flex align-items-center justify-content-between mt-4">
											<div>
												<h6 class="mb-0"><span class="text-truncate w-100">Lettre manuscrite</span></h6>
											</div>
											<a href="#" class="avtar avtar-s btn-light-secondary user-popup"
												 @if(optional($album)->lettre)
                                                     onclick="showLoadedFile('{{ Storage::url(optional($album)->lettre) }}')"
                                                     data-bs-toggle="modal" data-bs-target="#file-previewer"
                                                 @else
                                                     onclick="return false;"
                                                     aria-disabled="true"
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
											<img src="{{ asset('admin/assets/images/application/img-file-pdf.svg') }}" alt="img"
													 class="img-fluid"/>
										</div>
										<div class="d-flex align-items-center justify-content-between mt-4">
											<div>
												<h6 class="mb-0"><span class="text-truncate w-100">Extrait de naissance</span></h6>
											</div>
											<a href="#" class="avtar avtar-s btn-light-secondary user-popup"
												 @if(optional($album)->naissance)
                                                     onclick="showLoadedFile('{{ Storage::url(optional($album)->naissance) }}')"
                                                     data-bs-toggle="modal" data-bs-target="#file-previewer"
                                                 @else
                                                     onclick="return false;"
                                                     aria-disabled="true"
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
											<img src="{{ asset('admin/assets/images/application/img-file-pdf.svg') }}" alt="img"
													 class="img-fluid"/>
										</div>
										<div class="d-flex align-items-center justify-content-between mt-4">
											<div>
												<h6 class="mb-0"><span class="text-truncate w-100">Certificat de nationalité</span></h6>
											</div>
											<a href="#" class="avtar avtar-s btn-light-secondary user-popup"
												 @if(optional($album)->nationalite)
                                                     onclick="showLoadedFile('{{ Storage::url(optional($album)->nationalite) }}')"
                                                     data-bs-toggle="modal" data-bs-target="#file-previewer"
                                                 @else
                                                     onclick="return false;"
                                                     aria-disabled="true"
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
											<img src="{{ asset('admin/assets/images/application/img-file-pdf.svg') }}" alt="img"
													 class="img-fluid"/>
										</div>
										<div class="d-flex align-items-center justify-content-between mt-4">
											<div>
												<h6 class="mb-0"><span class="text-truncate w-100">diplôme requis</span></h6>
												<p class="mb-0 text-muted"><small>{{ optional($album)->type_diplome }}</small></p>
											</div>
											<a href="#" class="avtar avtar-s btn-light-secondary user-popup"
												 @if(optional($album)->diplome)
                                                     onclick="showLoadedFile('{{ Storage::url(optional($album)->diplome) }}')"
                                                     data-bs-toggle="modal" data-bs-target="#file-previewer"
                                                 @else
                                                     onclick="return false;"
                                                     aria-disabled="true"
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
											<img src="{{ asset('admin/assets/images/application/img-file-img.svg') }}" alt="img"
													 class="img-fluid"/>
										</div>
										<div class="d-flex align-items-center justify-content-between mt-4">
											<div>
												<h6 class="mb-0"><span class="text-truncate w-100">Photo d'identité</span></h6>
											</div>
											<a href="#" class="avtar avtar-s btn-light-secondary user-popup"
												 @if(optional($album)->photo)
                                                     onclick="showLoadedFile('{{ Storage::url(optional($album)->photo) }}')"
                                                     data-bs-toggle="modal" data-bs-target="#file-previewer"
                                                 @else
                                                     onclick="return false;"
                                                     aria-disabled="true"
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
											<img src="{{ asset('admin/assets/images/application/img-file-pdf.svg') }}" alt="img"
													 class="img-fluid"/>
										</div>
										<div class="d-flex align-items-center justify-content-between mt-4">
											<div>
												<h6 class="mb-0"><span class="text-truncate w-100">Certificat médical</span></h6>
											</div>
											<a href="#" class="avtar avtar-s btn-light-secondary user-popup"
												 @if(optional($album)->certificat_medical)
                                                     onclick="showLoadedFile('{{ Storage::url(optional($album)->certificat_medical) }}')"
                                                     data-bs-toggle="modal" data-bs-target="#file-previewer"
                                                 @else
                                                     onclick="return false;"
                                                     aria-disabled="true"
                                                 @endif>
												<i class="fa fa-eye"></i>
											</a>
										</div>
									</div>
								</div>
							</div>
							@if(optional($album)->coupon)
								<div class="col-md-6 col-lg-4 col-xxl-3">
									<div class="card file-card">
										<div class="card-body">
											<div class="my-3 text-center">
												<img src="{{ asset('admin/assets/images/application/img-file-pdf.svg') }}" alt="img"
														 class="img-fluid"/>
											</div>
											<div class="d-flex align-items-center justify-content-between mt-4">
												<div>
													<h6 class="mb-0"><span class="text-truncate w-100">Coupon réponse</span></h6>
												</div>
												<a href="#" class="avtar avtar-s btn-light-secondary user-popup"
													 @if(optional($album)->coupon)
                                                         onclick="showLoadedFile('{{ Storage::url(optional($album)->coupon) }}')"
                                                         data-bs-toggle="modal" data-bs-target="#file-previewer"
                                                     @else
                                                         onclick="return false;"
                                                         aria-disabled="true"
                                                     @endif>
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
	</div>
	@include('officiel.mySpace._preview-modal')
@endsection

@section('other-js')
	<script>
		function showLoadedFile(url) {
			document.getElementById('pdf-viewer').data = url;
		}
	</script>
@endsection
