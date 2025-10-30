<div class="modal fade" id="show-modal" data-bs-keyboard="false" tabindex="-1"
		 aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header border-0 pb-0">
				<h5 class="mb-0" id="user-nom"></h5>
				<a href="#" class="avtar avtar-s btn-link-danger btn-pc-default" data-bs-dismiss="modal">
					<i class="ti ti-x f-20"></i>
				</a>
			</div>
			<div class="modal-body">
				<div class="row align-items-center">
					<div class="col-sm-4">
						<div class="bg-light rounded position-relative">
							<div class="text-center">
								<div class="chat-avtar d-inline-flex mx-auto">
									<img class="img-fluid rounded" id="user-img" src="" alt="User image">
								</div>
							</div>
							<div class="position-absolute end-0 top-0 p-3">
								<x-badge/>
							</div>
						</div>
					</div>
					<div class="col-sm-8">
						<h5 id="user-nom">Biographie: </h5>
						<p class="text-muted text-justify" id="user-desc"></p>
						<h6>Rôles: </h6>
						<div class="table-responsive">
							<table class="table w-auto table-borderless">
								<tbody id="user-roles-table">
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>