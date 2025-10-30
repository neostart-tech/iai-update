<div id="editSalleModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.groups.store') }}" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">Formulaire de création d'un groupe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @php($group = new App\Models\Group())
                    @csrf
                    <div class="form-group text-start">
                        <label class="form-label" for="filiere_id">
                            Filière
                            <x-forms.required-field />
                        </label>
                        <select class="form-control" data-trigger name="filiere_id" id="filiere_id">
                            @foreach ($filieres as $filiere)
                                <option value="{{ $filiere->id }}" id="{{ $filiere->id }}"
                                    @selected(old('filiere_id') === $filiere->id)>
                                    {{ $filiere->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group text-start">
                        <label class="form-label" for="nom">Nom du groupe
                            <x-forms.required-field />
                        </label>
                        <input type="text" class="form-control uppercase" id="nom" name="nom"
                            aria-describedby="nom" placeholder="Ex: Groupe A"
                            value="{{ old('nom', $group->getAttribute('nom')) }}">
                        {!! errorAlert($errors->first('nom'), 'nom') !!}
                    </div>
                    <input type="hidden" name="groupId" id='groupId'>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick=" refreshForm()">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
