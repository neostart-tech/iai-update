<form action="{{ route('logout') }}" method="post" id="logout-form" hidden="">@csrf</form>
<form action="{{ route('etudiants.auth.logout') }}" method="post" id="etudiant-logout-form" hidden="">@csrf</form>
<form action="{{ route('officiel.logout') }}" method="post" id="officiel-logout-form" hidden="">@csrf</form>
<form action="{{ route('enseignants.auth.logout') }}" method="post" id="enseignant-logout-form" hidden="">@csrf</form>

