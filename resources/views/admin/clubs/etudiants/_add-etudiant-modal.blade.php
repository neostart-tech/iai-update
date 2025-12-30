<div class="modal fade" id="addEtudiantModal" tabindex="-1"
    aria-labelledby="addEtudiantModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="POST"
                action="{{ route('admin.club.etudiants.store', $club) }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title" id="addEtudiantModalLabel">
                        Ajouter un étudiant au club
                    </h5>
                    <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- Étudiant --}}
                    <div class="form-group text-start">
                        <label for="etudiant_id" class="form-label">
                            Étudiant
                            <x-forms.required-field />
                        </label>

                        <select class="form-control"
                            name="etudiant_ids[]"
                            id="etudiant_id"
                            required 
                            data-trigger
                            multiple
                            >
                            
                            <option value="">-- Sélectionner --</option>
                            @foreach ($etudiants as $etudiant)
                                <option value="{{ $etudiant->id }}">
                                   
                                    {{ $etudiant->nom }} {{ $etudiant->prenom }}
                                </option>
                            @endforeach
                        </select>

                        {!! errorAlert($errors->first('etudiant_id'), 'etudiant_id') !!}
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Annuler
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Ajouter
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
