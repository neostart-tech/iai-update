@extends('base', $meta)

@section('content')
	<div class="col-12">
		<div class="card">
			<div class="card-header text-end">
				<a href="#" data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg"
					 class="btn btn-primary">
					<i class="ti ti-plus f-18"></i> Ajouter une programmation
				</a>
			</div>
			<div class="card-body position-relative">
				<div id="calendar" class="calendar"></div>
			</div>
		</div>
	</div>

	@include('layouts.calendar._show-modal')

	@include('layouts.calendar._edit-modal')
	@include('admin.users.teacher-calendar._create')
@endsection
@section('other-js')
	<script src="{{ asset('admin/assets/js/plugins/index.global.min.js') }}"></script>
	<script src="{{ asset('admin/assets/js/plugins/sweetalert2.all.min.js') }}"></script>
	@include('layouts.calendar._scripts')
	@include('layouts._select-search-script')
@endsection
@section('other-css')
	<link href="{{ asset('admin/assets/css/plugins/animate.min.css') }}" rel="stylesheet" type="text/css">
@endsection
