@php use Illuminate\Support\Facades\Storage; @endphp
@extends('base', [
	'title' => 'Liste des blogs',
	'page_name' => 'Liste des blogs',
	'breadcrumbs' => [
		'Opportunités',
		'Liste des blogs'
	]
])

@section('content')
	<div class="col-sm-12">
		<div class="card col-12 mx-auto">
			<div class="card-header">
				<div class="text-end p-4 pb-sm-2 mb-2">
					<a href="{{ route('admin.blogs.create') }}" class="btn btn-primary me-2">
						Ajouter un blog
					</a>
					<a href="{{ route('admin.urgent_infos.index') }}" class="btn btn-outline-danger">
						Ajout info urgent
					</a>
				</div>
			</div>
			<div class="card-body">
				<div class="ecom-wrapper">
					<div class="ecom-content row col-12">
						<div class="card mx-auto">
							<div class="card-body">
								<form action="{{ route('admin.blogs.search') }}" method="post">
									@csrf
									<div class="d-sm-flex align-items-center">
										<ul class="list-inline me-auto my-1">
											<li class="list-inline-item">
												<div class="form-search">
													<i class="ti ti-search"></i>
													<input type="search" name="title" id="title"
																 class="form-control" placeholder="Rechercher une publication"/>
												</div>
											</li>
										</ul>
										<ul class="list-inline ms-auto my-1">
											<li class="list-inline-item">
												<select class="form-select" name="direction">
													<option value="desc" selected>Du plus récent au plus ancien</option>
													<option value="asc">Du plus ancien au plus récent</option>
												</select>
											</li>
											<li class="list-inline-item align-bottom">
												<button
													type="submit"
													href="#"
													class="d-inline-flex d-xxl-none btn btn-link-secondary align-items-center"
													data-bs-toggle="offcanvas"
													data-bs-target="#offcanvas_mail_filter"
												>
													<i class="ti ti-filter f-16"></i> Filtrer
												</button>
												<button
													type="submit"
													href="#"
													class="d-none d-xxl-inline-flex btn btn-link-secondary align-items-center"
													data-bs-toggle="collapse"
													data-bs-target="#ecom-filter"
												>
													<i class="ti ti-filter f-16"></i> Filtrer
												</button>
											</li>
										</ul>
									</div>
								</form>
							</div>
						</div>
						@foreach($blogs as $blog)
							<div class="col-sm-6 col-xl-4" data-title="{{ $blog->title }}">
								<div class="card product-card">
									<div class="card-img-top position-relative">
										<a href="#">
											<img src="{{ Storage::disk('public')->url($blog->image) }}" alt="image" class="img-prod img-fluid w-100"
													 style="object-fit: cover; height: 18rem"/>
										</a>
										<div class="btn-prod-cart card-body position-absolute end-0 bottom-0">
											<div class="btn btn-primary"
													 onclick="document.location.href = '{{ route('admin.blogs.show', $blog) }}'">
												<i class="fa fa-eye"></i>
											</div>
											<div class="btn btn-warning"
													 onclick="document.location.href = '{{ route('admin.blogs.edit', $blog) }}'">
												<i class="fa fa-edit"></i>
											</div>
											<div class="btn btn-danger" onclick="showDeleteAlert(
                        '{{ route('admin.blogs.destroy', $blog) }}',
                        'Voulez-vous supprimer cette publication ?'
                    )">
												<i class="fa fa-trash"></i>
											</div>
										</div>
									</div>
									<div class="card-body">
										<a href="#">
											<p
												class="prod-content mb-0 text-muted">{{ $blog->publication_date->translatedFormat('d F Y') }}</p>
										</a>
										<p class="mb-1 text-secondary">Auteur: <strong>{{ $blog->author_name ?? 'Admin' }}</strong></p>
										<div class="d-flex align-items-center justify-content-between mt-2">
											<h4 class="mb-0 text-truncate">
												<b>{{ $blog->title }}</b>
											</h4>
										</div>
									</div>
								</div>
							</div>
						@endforeach
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('other-js')
	<script>
		document.getElementById('title').addEventListener('input', event => {
			const queryString = event.target.value;
			console.log("query string :", queryString);
			document.querySelectorAll("[data-title]").forEach(element => {

				if (element.getAttribute('data-title').toLowerCase().includes(queryString.toLowerCase())) {
					element.style.display = 'flex';
				} else {
					element.style.display = 'none';
				}
			});
		});
	</script>
@endsection
