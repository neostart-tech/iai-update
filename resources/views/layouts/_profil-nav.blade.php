<li class="dropdown pc-h-item header-user-profile">
	<a
		class="pc-head-link dropdown-toggle arrow-none me-0"
		data-bs-toggle="dropdown"
		href="#"
		role="button"
		aria-haspopup="false"
		data-bs-auto-close="outside"
		aria-expanded="false"
	>
		<div class="flex-shrink-0">
							<span
								class="avtar bg-primary text-white">{{ Str::limit($user->nom, 1, '') . ' ' . Str::limit($user->prenom, 1, '') }}
							</span>
		</div>
	</a>
	<div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
		<div class="dropdown-header d-flex align-items-center justify-content-between">
			<h5 class="m-0">Profile</h5>
		</div>
		<div class="dropdown-body">
			<div class="profile-notification-scroll position-relative" style="max-height: calc(100vh - 225px)">
				<div class="d-flex mb-1">
					<div class="flex-shrink-0">
						<img src="{{ asset('admin/assets/images/user/avatar-2.jpg') }}" alt="user-image"
								 class="user-avtar wid-35"/>
					</div>
					<div class="flex-grow-1 ms-3">
						<h6 class="mb-1">{{ $user->nom . ' ' . $user->prenom }}</h6>
						<span>{{ $user->email }}</span>
					</div>
				</div>
				<hr class="border-secondary border-opacity-50"/>
				{{--<div class="card">
						<div class="card-body py-3">
							<div class="d-flex align-items-center justify-content-between">
								<h5 class="mb-0 d-inline-flex align-items-center"
								>
									<svg class="pc-icon text-muted me-2">
										<use xlink:href="#custom-notification-outline"></use>
									</svg
									>
									Notification
								</h5
								>
								<div class="form-check form-switch form-check-reverse m-0">
									<input class="form-check-input f-18" type="checkbox" role="switch"/>
								</div>
							</div>
						</div>
					</div>
				<p class="text-span">Manage</p>
				<a href="#" class="dropdown-item">
					<span>
						<svg class="pc-icon text-muted me-2">
							<use xlink:href="#custom-setting-outline"></use>
						</svg>
						<span>Settings</span>
					</span>
				</a>
				<a href="#" class="dropdown-item">
					<span>
						<svg class="pc-icon text-muted me-2">
							<use xlink:href="#custom-share-bold"></use>
						</svg>
						<span>Share</span>
					</span>
				</a>--}}
				<a href="{{ route('my-space.my-account') }}" class="dropdown-item">
					<span>
						<svg class="pc-icon text-muted me-2">
							<use xlink:href="#custom-lock-outline"></use>
						</svg>
						<span>Mon profil</span>
					</span>
				</a>
				{{--				<hr class="border-secondary border-opacity-50"/>
								<p class="text-span">Team</p>
								<a href="#" class="dropdown-item">
									<span>
										<svg class="pc-icon text-muted me-2">
											<use xlink:href="#custom-profile-2user-outline"></use>
										</svg>
										<span>UI Design team</span>
									</span>
									<div class="user-group">
										<img src="{{ asset('admin/assets/images/user/avatar-1.jpg') }}" alt="user-image" class="avtar"/>
										<span class="avtar bg-danger text-white">K</span>
										<span class="avtar bg-success text-white">
											<svg class="pc-icon m-0">
												<use xlink:href="#custom-user"></use>
											</svg>
										</span>
										<span class="avtar bg-light-primary text-primary">+2</span>
									</div>
								</a>
								<a href="#" class="dropdown-item">
									<span>
										<svg class="pc-icon text-muted me-2">
											<use xlink:href="#custom-profile-2user-outline"></use>
										</svg>
										<span>Friends Groups</span>
									</span>
									<div class="user-group">
										<img src="{{ asset('admin/assets/images/user/avatar-1.jpg') }}" alt="user-image" class="avtar"/>
										<span class="avtar bg-danger text-white">K</span>
										<span class="avtar bg-success text-white">
											<svg class="pc-icon m-0">
												<use xlink:href="#custom-user"></use>
											</svg>
										</span>
									</div>
								</a>
								<a href="#" class="dropdown-item">
											<span>
												<svg class="pc-icon text-muted me-2">
													<use xlink:href="#custom-add-outline"></use>
												</svg>
												<span>Add new</span>
											</span>
									<div class="user-group">
												<span class="avtar bg-primary text-white">
													<svg class="pc-icon m-0">
														<use xlink:href="#custom-add-outline"></use>
													</svg>
												</span>
									</div>
								</a>--}}
				<hr class="border-secondary border-opacity-50"/>
				<div class="d-grid mb-3">
					<button class="btn btn-primary" onclick="document.getElementById('{{$logoutFormId}}').submit();">
						<svg class="pc-icon me-2">
							<use xlink:href="#custom-logout-1-outline"></use>
						</svg
						>
						Me déconnecter
					</button>
				</div>
				{{--<div
					class="card border-0 shadow-none drp-upgrade-card mb-0"
					style="background-image: url({{ asset('admin/assets/images/layout/img-profile-card.jpg') }})"
				>
					<div class="card-body">
						<div class="user-group">
							<img src="{{ asset('admin/assets/images/user/avatar-1.jpg') }}" alt="user-image" class="avtar"/>
							<img src="{{ asset('admin/assets/images/user/avatar-2.jpg') }}" alt="user-image" class="avtar"/>
							<img src="{{ asset('admin/assets/images/user/avatar-3.jpg') }}" alt="user-image" class="avtar"/>
							<img src="{{ asset('admin/assets/images/user/avatar-4.jpg') }}" alt="user-image" class="avtar"/>
							<img src="{{ asset('admin/assets/images/user/avatar-5.jpg') }}" alt="user-image" class="avtar"/>
							<span class="avtar bg-light-primary text-primary">+20</span>
						</div>
						<h3 class="my-3 text-dark">245.3k <small class="text-muted">Followers</small></h3>
						<div class="btn btn btn-warning">
							<svg class="pc-icon me-2">
								<use xlink:href="#custom-logout-1-outline"></use>
							</svg>
							Upgrade to Business
						</div>
					</div>
				</div>--}}
			</div>
		</div>
	</div>
</li>
