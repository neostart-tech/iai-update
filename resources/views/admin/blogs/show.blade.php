@php use Illuminate\Support\Facades\Storage; @endphp
@extends('base', [
	'title' => 'Détails d\'une publication',
	'page_name' => 'Détails de la publication',
	'breadcrumbs' => [
		[
			'text' => 'Publications',
			'url' => route('admin.blogs.index')
		],
		$blog->title,
		'Détails'
	]
])

@section('content')
	<div class="card">
		<div class="card-body">
			<div class="row">
				<div class="col-md-6">
					<div class="sticky-md-top product-sticky">
						<div id="carouselExampleCaptions" class="carousel slide ecomm-prod-slider" data-bs-ride="carousel">
							<div class="carousel-inner bg-light rounded position-relative">
								<div class="card-body position-absolute bottom-0 end-0">
								</div>
								<div class="carousel-item active">
									<img src="{{ Storage::disk('public')->url($blog->image) }}" class="d-block w-100" alt="Product images"/>
								</div>
								{{--								<div class="carousel-item">
																	<img src="../assets/images/application/img-prod-2.jpg" class="d-block w-100" alt="Product images" />
																</div>
																<div class="carousel-item">
																	<img src="../assets/images/application/img-prod-3.jpg" class="d-block w-100" alt="Product images" />
																</div>
																<div class="carousel-item">
																	<img src="../assets/images/application/img-prod-4.jpg" class="d-block w-100" alt="Product images" />
																</div>
																<div class="carousel-item">
																	<img src="../assets/images/application/img-prod-5.jpg" class="d-block w-100" alt="Product images" />
																</div>
																<div class="carousel-item">
																	<img src="../assets/images/application/img-prod-6.jpg" class="d-block w-100" alt="Product images" />
																</div>
																<div class="carousel-item">
																	<img src="../assets/images/application/img-prod-7.jpg" class="d-block w-100" alt="Product images" />
																</div>
																<div class="carousel-item">
																	<img src="../assets/images/application/img-prod-8.jpg" class="d-block w-100" alt="Product images" />
																</div>--}}
							</div>
							<ol class="carousel-indicators position-relative product-carousel-indicators my-sm-3 mx-0">
								<li data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="w-25 h-auto active">
									<img src="{{ Storage::disk('public')->url($blog->image) }}" class="d-block wid-50 rounded"
											 alt="Product images"/>
								</li>
								{{--<li data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" class="w-25 h-auto">
									<img src="../assets/images/application/img-prod-2.jpg" class="d-block wid-50 rounded"
											 alt="Product images"/>
								</li>
								<li data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2" class="w-25 h-auto">
									<img src="../assets/images/application/img-prod-3.jpg" class="d-block wid-50 rounded"
											 alt="Product images"/>
								</li>
								<li data-bs-target="#carouselExampleCaptions" data-bs-slide-to="3" class="w-25 h-auto">
									<img src="../assets/images/application/img-prod-4.jpg" class="d-block wid-50 rounded"
											 alt="Product images"/>
								</li>
								<li data-bs-target="#carouselExampleCaptions" data-bs-slide-to="4" class="w-25 h-auto">
									<img src="../assets/images/application/img-prod-5.jpg" class="d-block wid-50 rounded"
											 alt="Product images"/>
								</li>
								<li data-bs-target="#carouselExampleCaptions" data-bs-slide-to="5" class="w-25 h-auto">
									<img src="../assets/images/application/img-prod-6.jpg" class="d-block wid-50 rounded"
											 alt="Product images"/>
								</li>
								<li data-bs-target="#carouselExampleCaptions" data-bs-slide-to="6" class="w-25 h-auto">
									<img src="../assets/images/application/img-prod-7.jpg" class="d-block wid-50 rounded"
											 alt="Product images"/>
								</li>
								<li data-bs-target="#carouselExampleCaptions" data-bs-slide-to="7" class="w-25 h-auto">
									<img src="../assets/images/application/img-prod-8.jpg" class="d-block wid-50 rounded"
											 alt="Product images"/>--}}
								</li>
							</ol>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<h5 class="my-3">Titre: {{ $blog->title }}</h5>
					<h6 class="mt-1 mb-1 text-muted">Auteur: <strong>{{ $blog->author_name ?? 'Admin' }}</strong></h6>
					<h5 class="mt-3 mb-3">Date de publication: {{$blog->publication_date->translatedFormat('d F Y')}}</h5>
					<div class="form-group">
						<x-forms.label content="Contenu" for="content" required="0"/>
						<div class="form-control">{!! $blog->content !!}</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('other-js')
	<script>
		function showLoadedFile(url) {
			document.getElementById('pdf-viewer').data = url;
		}
	</script>
@endsection