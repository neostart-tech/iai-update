<script>
	const notification = new Notyf({
		position: {
			x: "right",
			y: "top"
		},
		duration: 5000
	});

	function showToast(message, toastClass = 'success') {
		popNotification(message, toastClass);
	}

	function popNotification(message, color = 'success') {
		switch (color) {
			case "danger":
				notification.error(message);
				break;
			case "success":
			default:
				notification.success(message);
				break;
		}
	}
</script>
