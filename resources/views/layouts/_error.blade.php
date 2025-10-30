{{-- Message d'erreur de validation --}}
@foreach($errors->all() as $error)
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<strong>Oups!</strong> {{ $error }}
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
@endforeach

@if(session()->has('success'))
	<div class="alert alert-success alert-dismissible fade show" role="alert">
{{--		@dd(session()->get('success')[0])--}}
		<strong>Super!</strong> {{ session()->pull('success')[0] }}
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
@endif

@if(session()->has('danger'))
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<strong>Oups!</strong> {{ session()->pull('danger')[0] }}
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
@endif

@if(session()->has('warning'))
	<div class="alert alert-warning alert-dismissible fade show" role="alert">
		<strong>Attention!</strong> {{ session()->pull('warning')[0] }}
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
	</div>
@endif
