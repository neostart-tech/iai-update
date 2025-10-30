<script src="{{ asset('admin/assets/js/plugins/choices.min.js') }}"></script>
<script>
	document.addEventListener('DOMContentLoaded', function () {
		let genericExamples = document.querySelectorAll('[data-trigger]');
		for (let i = 0; i < genericExamples.length; ++i) {
			let element = genericExamples[i];
			new Choices(element, {
				placeholderValue: 'Aucune correspondante n\'a été trouvée',
				searchPlaceholderValue: 'Saisissez ici pour faire une recherche dans la liste',
				itemSelectText: 'Choisir',
				noResultsText: "Aucune correspondante n'a été trouvée",
				noChoicesText: "Pas de choix disponible"
			});
		}
	});
</script>