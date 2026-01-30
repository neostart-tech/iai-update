<div class="modal fade" id="addDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-plus-circle me-2"></i>Ajouter un nouveau document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('documentation.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="row g-3">
                        {{-- Titre --}}
                        <div class="col-12">
                            <label for="title" class="form-label">
                                <i class="fas fa-heading me-1"></i>Titre du document <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                name="title"
                                id="title"
                                class="form-control"
                                placeholder="Ex: Guide d'utilisation"
                                required>
                        </div>

                        {{-- Fichier --}}
                        <div class="col-12">
                            <label for="file" class="form-label">
                                <i class="fas fa-file-upload me-1"></i>Fichier <span class="text-danger">*</span>
                            </label>
                            <div class="file-upload-area border rounded-2 p-4 text-center">
                                <input type="file"
                                    name="file"
                                    id="file"
                                    class="form-control d-none"
                                    required
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png">
                                <div class="file-upload-placeholder">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <p class="mb-2">Glissez-déposez votre fichier ici ou</p>
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('file').click()">
                                        <i class="fas fa-folder-open me-2"></i>Parcourir les fichiers
                                    </button>
                                    <p class="text-muted small mt-2 mb-0">Formats acceptés: PDF, DOC, XLS, PPT, JPG, PNG (Max 10MB)</p>
                                </div>
                                <div class="file-upload-preview d-none">
                                    <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-2">
                                        <div class="d-flex align-items-center gap-3">
                                            <i class="fas fa-file text-primary fa-2x"></i>
                                            <div>
                                                <h6 class="mb-0 file-name">Fichier.pdf</h6>
                                                <small class="text-muted file-size">2.5 MB</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-light remove-file">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Sections d'accès --}}
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title d-flex align-items-center gap-2 text-primary">
                                        <i class="fas fa-user-tag"></i>
                                        Rôles autorisés
                                    </h6>
                                    <select name="roles[]" data-trigger class="form-select select2" multiple data-placeholder="Sélectionnez les rôles">
                                        @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title d-flex align-items-center gap-2 text-success">
                                        <i class="fas fa-users"></i>
                                        Groupes autorisés
                                    </h6>
                                    <select data-trigger name="groupes[]" class="form-select select2" multiple data-placeholder="Sélectionnez les groupes">
                                        @foreach($groupes as $groupe)
                                        <option value="{{ $groupe->id }}"> {{ $groupe->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title d-flex align-items-center gap-2 text-info">
                                        <i class="fas fa-graduation-cap"></i>
                                        Filières autorisées
                                    </h6>
                                    <select name="filieres[]" class="form-select select2" data-trigger multiple data-placeholder="Sélectionnez les filières">
                                        @foreach($filieres as $filiere)
                                        <option value="{{ $filiere->id }}">{{ $filiere->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title d-flex align-items-center gap-2 text-warning">
                                        <i class="fas fa-layer-group"></i>
                                        Niveaux autorisés
                                    </h6>
                                    <select name="niveaux[]" class="form-select select2" data-trigger multiple data-placeholder="Sélectionnez les niveaux">
                                        @foreach($niveaux as $niveau)
                                        <option value="{{ $niveau->id }}">{{ $niveau->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Annuler
                    </button>
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                        <i class="fas fa-upload"></i>
                        Enregistrer le document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>