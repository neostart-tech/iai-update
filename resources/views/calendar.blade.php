@extends('base', [
	'title' => 'Emploi du temps de la salle A',
	'page_name' => 'Emploi du temps de la salle A',
	'breadcrumbs' => ['Administration', 'Gestion des emploi du temps', 'Salle A']
])

@section('content')
	<div class="col-12">
		<div class="card">
			<div class="card-body position-relative">
				<div id="calendar" class="calendar"></div>
			</div>
		</div>
	</div>

	@include('layouts.calendar._show-modal')

	@include('layouts.calendar._edit-modal')
@endsection
@section('other-js')
	<script src="{{ asset('admin/assets/js/plugins/index.global.min.js') }}"></script>
	<!-- Sweet Alert -->
	<script src="{{ asset('admin/assets/js/plugins/sweetalert2.all.min.js') }}"></script>
	@include('layouts.calendar._scripts')
@endsection