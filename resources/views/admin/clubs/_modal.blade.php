<div id="clubModal" class="modal fade" tabindex="-1" role="dialog"
    aria-labelledby="exampleModalCenterTitle" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <form action="{{ route('admin.club.store') }}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">
                        Formulaire de création d'un club
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @php($club = new App\Models\Club())


                    {{-- Nom du club --}}
                    <div class="form-group text-start">
                        <label class="form-label" for="nom">
                            Nom du club
                            <x-forms.required-field />
                        </label>
                        <input type="text"
                            class="form-control uppercase"
                            id="nom"
                            name="nom"
                            placeholder="Ex: Club Informatique"
                            value="{{ old('nom', $club->nom) }}">
                        {!! errorAlert($errors->first('nom'), 'nom') !!}
                    </div>

                    {{-- Responsable --}}
                    <div class="form-group text-start mt-3">
                        <label class="form-label" for="responsable_id">
                            Responsable du club
                            <x-forms.required-field />
                        </label>
                        <select class="form-control" data-trigger name="responsable_id" id="responsable_id" >
                            <option value="">-- Sélectionner --</option>

                            @foreach ($responsables as $responsable)

                            
                            <option value="{{ $responsable->id }}"
                                @selected(old('responsable_id') == $responsable->id)>
                                {{ $responsable->nom }} {{ $responsable->prenom }}
                            </option>
                            @endforeach
                        </select>
                        
                        {!! errorAlert($errors->first('responsable_id'), 'responsable_id') !!}
                    </div>

                    {{-- Description --}}
                    <div class="form-group text-start mt-3">
                        <label class="form-label" for="description">
                            Description
                        </label>
                        <textarea class="form-control"
                            name="description"
                            id="description"
                            rows="3"
                            placeholder="Description du club">{{ old('description') }}</textarea>
                    </div>

                    {{-- Date de création --}}


                    <div class="form-group text-start mt-3">
                        <label class="form-label" for="date_creation">
                            Date de création
                            <x-forms.required-field />
                        </label>
                        <input type="date"
                            class="form-control"
                            name="date_creation"
                            id="date_creation"
                            value="{{ old('date_creation', now()->toDateString()) }}">
                        {!! errorAlert($errors->first('date_creation'), 'date_creation') !!}
                    </div>

                    {{-- Hidden id (édition) --}}
                    <input type="hidden" name="clubId" id="clubId">

                </div>

                <div class="modal-footer">
                    <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                        onclick="refreshForm()">
                        Annuler
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Enregistrer
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>