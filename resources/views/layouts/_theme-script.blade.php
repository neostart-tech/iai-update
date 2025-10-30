<script>
		layout_change(getThemeMode());
		preset_change('preset-' + getPreset());

	const storeThemeMode = theme => {
		localStorage.setItem('theme', theme)
	}

	function getThemeMode() {
		return localStorage.getItem('theme');
	}

	const storePreset = preset => {
		localStorage.setItem('preset', preset.charAt(7));
	}

	function getPreset() {
		return localStorage.getItem('preset') ?? '7'
	}
</script>
