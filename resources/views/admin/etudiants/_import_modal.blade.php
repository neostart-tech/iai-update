<div class="modal fade" id="importEtudiantsModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="importForm" class="modal-content" enctype="multipart/form-data">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Importer des étudiants</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <label class="form-label">Fichier Excel</label>
                <input type="file"
                    name="file"
                    class="form-control"
                    accept=".xls,.xlsx"
                    required>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-success">
                    <i class="fa fa-upload"></i> Importer
                </button>
            </div>
            <div class="progress mt-3 d-none" id="importProgressWrapper">
                <div class="progress-bar progress-bar-striped progress-bar-animated"
                    id="importProgress"
                    style="width: 0%">
                    0%
                </div>
            </div>

            <div class="alert alert-info mt-3 d-none" id="importStatus"></div>

        </form>
    </div>
</div>

@include('admin.etudiants.import_progress_script')