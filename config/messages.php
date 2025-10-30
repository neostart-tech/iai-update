<?php

return [
	'cannot' => [
		'delete' => 'Impossible de supprimer :name. D\'autres éléments de l\'application dépendent encore de son existence'
	],
	'errors' => [
		'403' => [
			'text1' => 'Accès non autorisé',
			'text2' => 'Ce message signifie que vous essayez d\'accéder à une ressource à
								laquelle vous n\'avez normalement pas accès. Veuillez cliquer sur le bouton ci-dessous pour revenir à votre
								espace de navigation.'
		]
	],
	'absences' => [
		'evaluation' => [
			'parents' => ":_salutation Monsieur,Madame :_nom_prenom_resp. Nous tenons à vous informer que votre étudiant(e), :_nom_prenom_etu,
		    n'a malheureusement pas pu assister à l'évaluation suivante: :_info_evaluation. Nous souhaitons vous tenir informé(e) de cette situation.",
			"etudiant" => ":_salutation :_nom_prenom_etu, vous avez été marqué(e) comme absent(e) à l'évaluation suivante: :_info_evaluation."
		],

		'cours' => ":salutation Monsieur,Madame :_nom_prenom_resp. Nous tenons à vous informer que votre étudiant(e), :_nom_prenom_etu,
		 n'a malheureusement pas pu assister au cours de :_info_cour. Nous souhaitons vous tenir informé(e) de cette situation.",
	]
];
