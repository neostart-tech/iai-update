<form action="{{ route('etudiants.announcements.apply-to-announcement', $announcement) }}" method="post" hidden=""
			id="apply-form">
	@csrf
	<input type="hidden" name="announcement" value="{{ $announcement->slug }}">
</form>