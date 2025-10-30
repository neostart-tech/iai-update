@php
	use Illuminate\Support\Carbon;use Illuminate\Support\Str;
@endphp
<header class="pc-header">
	<div class="header-wrapper"> <!-- [Mobile Media Block] start -->
		<div class="me-auto pc-mob-drp">
			<ul class="list-unstyled">
				<!-- ======= Menu collapse Icon ===== -->
				<li class="pc-h-item pc-sidebar-collapse">
					<a href="#" class="pc-head-link ms-0" id="sidebar-hide">
						<i class="ti ti-menu-2"></i>
					</a>
				</li>
				<li class="pc-h-item pc-sidebar-popup">
					<a href="#" class="pc-head-link ms-0" id="mobile-collapse">
						<i class="ti ti-menu-2"></i>
					</a>
				</li>
				<li class="dropdown pc-h-item">
					<a
						class="pc-head-link dropdown-toggle arrow-none m-0 trig-drp-search"
						data-bs-toggle="dropdown"
						href="#"
						role="button"
						aria-haspopup="false"
						aria-expanded="false"
					>
						<svg class="pc-icon">
							<use xlink:href="#custom-search-normal-1"></use>
						</svg>
					</a>
					<div class="dropdown-menu pc-h-dropdown drp-search">
						<form class="px-3 py-2">
							<input type="search" class="form-control border-0 shadow-none" placeholder="Search here. . ."/>
						</form>
					</div>
				</li>
			</ul>
		</div>
		<!-- [Mobile Media Block end] -->
		<div class="ms-auto">
			<ul class="list-unstyled">
				<li class="dropdown pc-h-item">
					<a
						class="pc-head-link dropdown-toggle arrow-none me-0"
						data-bs-toggle="dropdown"
						href="#"
						role="button"
						aria-haspopup="false"
						aria-expanded="false"
					>
						<svg class="pc-icon">
							<use xlink:href="#custom-sun-1"></use>
						</svg>
					</a>
					<div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
						<a href="#!" class="dropdown-item" onclick="layout_change('dark')">
							<svg class="pc-icon">
								<use xlink:href="#custom-moon"></use>
							</svg>
							<span>Thème sombre</span>
						</a>
						<a href="#!" class="dropdown-item" onclick="layout_change('light')">
							<svg class="pc-icon">
								<use xlink:href="#custom-sun-1"></use>
							</svg>
							<span>Thème clair</span>
						</a>
						<a href="#!" class="dropdown-item" onclick="layout_change_default()">
							<svg class="pc-icon">
								<use xlink:href="#custom-setting-2"></use>
							</svg>
							<span>Système</span>
						</a>
					</div>
				</li>
				{{--<li class="dropdown pc-h-item">
					<a
						class="pc-head-link dropdown-toggle arrow-none me-0"
						data-bs-toggle="dropdown"
						href="#"
						role="button"
						aria-haspopup="false"
						aria-expanded="false"
					>
						<svg class="pc-icon">
							<use xlink:href="#custom-setting-2"></use>
						</svg>
					</a>
					<div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
						<a href="#!" class="dropdown-item">
							<i class="ti ti-user"></i>
							<span>My Account</span>
						</a>
						<a href="#!" class="dropdown-item">
							<i class="ti ti-settings"></i>
							<span>Settings</span>
						</a>
						<a href="#!" class="dropdown-item">
							<i class="ti ti-headset"></i>
							<span>Support</span>
						</a>
						<a href="#!" class="dropdown-item">
							<i class="ti ti-lock"></i>
							<span>Lock Screen</span>
						</a>
						<a href="#!" class="dropdown-item">
							<i class="ti ti-power"></i>
							<span>Logout</span>
						</a>
					</div>
				</li>--}}
				{{--<li class="pc-h-item">
					<a href="#" class="pc-head-link me-0" data-bs-toggle="offcanvas" data-bs-target="#announcement"
						 aria-controls="announcement">
						<svg class="pc-icon">
							<use xlink:href="#custom-flash"></use>
						</svg>
					</a>
				</li>--}}
				<li class="dropdown pc-h-item">
					<a
						class="pc-head-link dropdown-toggle arrow-none me-0"
						data-bs-toggle="dropdown"
						href="#"
						role="button"
						aria-haspopup="false"
						aria-expanded="false"
					>
						<svg class="pc-icon">
							<use xlink:href="#custom-notification"></use>
						</svg>
						@php
							$user = request()->user();
						$notifications = $user->unreadNotifications;
						$allNotificationCount = $notifications->count();
						@endphp
						@if($allNotificationCount> 0)
							<span class="badge bg-primary pc-h-badge">{{$allNotificationCount}}</span>
						@endif
						@php
							$notifications = $user->notifications;
							$unreadNotificationCount = $notifications->count();
							$notifications->map(fn($notification) => $notification->createdAt = $notification->created_at->format('Y-m-d'));
							$notifications = $notifications->groupBy('createdAt');
						@endphp
					</a>
					<div class="dropdown-menu dropdown-notification dropdown-menu-end pc-h-dropdown">
						<div class="dropdown-header d-flex align-items-center justify-content-between">
							<h5 class="m-0">Notifications</h5>
							@if($allNotificationCount > 0)
								<a href="#" onclick="document.getElementById('notification-clean-form').submit();"
									 class="btn btn-link btn-sm">Tout marquer comme lu</a>
								<form action="{{ route('notifications.reading.read-all') }}" method="post" id="notification-clean-form"
											hidden="">
									@csrf
								</form>
							@endif
						</div>
						<div class="dropdown-body text-wrap header-notification-scroll position-relative"
								 style="max-height: calc(100vh - 215px)">
							@forelse($notifications as $date => $dateNotifications)
								@php($date = Carbon::make($date))
								<p class="text-span">{{
									Str::ucfirst($date->isToday() ? "aujourd'hui" : ($date->isYesterday() ? 'Hier' : $date->longRelativeDiffForHumans()))
								}}
									<small>{{ !$date->isToday() && !$date->isYesterday() ?  $date->translatedFormat('d F Y') : '' }}</small>
								</p>
								@foreach($dateNotifications as $notification)
									@php($notificationData = $notification->data)
									<div class="card mb-2 bg-light-{{ $notificationData['level'] ?? '' }}">
										<div class="card-body">
											<div class="d-flex">
												<div class="flex-shrink-0">
													{!! $notificationData['icon'] !!}
													{{--													<i data-feather="user-check"></i>--}}
												</div>
												<div class="flex-grow-1 ms-3">
													<span
														class="float-end text-sm text-muted">{{
															Str::ucfirst(
																$notification->created_at->isToday() ?
																(
																	$notification->created_at->isCurrentMinute() ?
																	'à l\'instant':
																	$notification->created_at->diffForHumans()
																) :
																 $notification->created_at->translatedFormat('H:i')
															 )
															}}
													</span>
													<h5 class="text-body mb-2">{{ Str::limit($notificationData['title'], 20) }}</h5>
													<p class="mb-0">{{ $notificationData['content'] }}</p>
												</div>
											</div>
										</div>
									</div>
								@endforeach
							@empty
								<div class="card mb-2">
									<div class="card-body">
										<div class="d-flex">
											<div class="flex-grow-1 ms-3">
												<h5 class="text-body mb-2">Aucune notification</h5>
											</div>
										</div>
									</div>
								</div>
							@endforelse
						</div>
						<div class="text-center py-2">
							@if($unreadNotificationCount > 0)
								<a href="#" onclick="document.getElementById('notifications-reading-form').submit()"
									 class="link-danger">Supprimer
									mes notifications</a>
								<form action="{{ route('notifications.reading.clean') }}" method="post"
											id="notifications-reading-form">@csrf</form>
							@endif
						</div>
					</div>
				</li>
				@include('layouts._profil-nav')
			</ul>
		</div>
	</div>
</header>