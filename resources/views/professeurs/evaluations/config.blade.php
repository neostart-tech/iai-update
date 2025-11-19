@extends('base')

@section('content')
<h3>Paramètres de l'évaluation : {{ $evaluation->title }}</h3>

<form action="{{ route('enseignants.evaluations.update', $evaluation) }}" method="POST">
    @csrf
    @method('PUT')
    <label>
        En ligne: <input type="checkbox" name="is_online" value="1" @checked($evaluation->is_online)>
    </label><br>
    <label>
        Durée (minutes): <input type="number" name="duration_minutes" value="{{ $evaluation->duration_minutes }}">
    </label><br>
    <label>
        Sécurité:
        <select name="security_level">
            <option value="none" @selected($evaluation->security_level=='none')>Aucune</option>
            <option value="medium" @selected($evaluation->security_level=='medium')>Moyenne</option>
            <option value="strict" @selected($evaluation->security_level=='strict')>Strict</option>
        </select>
    </label><br>
    <label><input type="checkbox" name="autosave_enabled" value="1" @checked($evaluation->autosave_enabled)> Autosave activé</label><br>
    <label><input type="checkbox" name="disable_copy_paste" value="1" @checked($evaluation->disable_copy_paste)> Désactiver copier/coller</label><br>
    <label><input type="checkbox" name="disable_right_click" value="1" @checked($evaluation->disable_right_click)> Désactiver clic droit</label><br>
    <label><input type="checkbox" name="forbid_tab_switch" value="1" @checked($evaluation->forbid_tab_switch)> Interdiction changer onglet</label><br>
    <button type="submit">Mettre à jour</button>
</form>
@endsection
