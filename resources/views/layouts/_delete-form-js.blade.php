<script>
	function showDeleteAlert(action, title, callback = null, text = null, confirmButtonText = null, cancelButtonText = null,) {
		let deleteForm = document.getElementById('delete-form');
		deleteForm.setAttribute('action', action);

		new Swal({
			title: title,
			text: text ?? "Cette action mettra cette ressource en quarantaine!",
			icon: 'warning',
			showCancelButton: true,
			cancelButtonColor: '#5b6b79',
			confirmButtonColor: '#3085d6',
			cancelButtonText: cancelButtonText ?? 'Annuler',
			confirmButtonText: confirmButtonText ?? 'Oui, supprimer!'
		}).then(result => {
			if (result.isConfirmed) {
				callback ? callback() : deleteForm.submit();
			}
		});
	}

	function showRestoreAlert(url, message) {
		new Swal({
			title: message,
			text: "Cette action sortira cette ressource de la quarantaine!",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#d33',
			cancelButtonColor: '#3085d6',
			cancelButtonText: 'Annuler',
			confirmButtonText: 'Oui, restaurer!'
		}).then(result => {
			result.isConfirmed && (document.location.href = url);
		});
	}
</script>
