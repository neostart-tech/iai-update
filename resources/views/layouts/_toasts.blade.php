{{-- Error Toast --}}
<div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
	<div class="toast hide text-white bg-danger fade" id="danger-liveToast" role="alert" aria-live="assertive" aria-atomic="true">
		<div class="d-flex">
			<div class="toast-header bg-danger text-white">Oups!!</div>
			<div class="toast-body" id="danger-toast-body"> </div>
			<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
							aria-label="Close"></button>
		</div>
	</div>
</div>

{{-- Warning Toast --}}
<div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
	<div class="toast hide text-white bg-warning fade" id="warning-liveToast" role="alert" aria-live="assertive" aria-atomic="true">
		<div class="d-flex">
			<div class="toast-header bg-warning text-white">Oups!!</div>
			<div class="toast-body" id="warning-toast-body"> </div>
			<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
							aria-label="Close"></button>
		</div>
	</div>
</div>
