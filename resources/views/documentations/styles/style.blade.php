<style>
    .file-upload-area {
        border: 2px dashed #dee2e6;
        transition: all 0.3s;
        cursor: pointer;
    }

    .file-upload-area:hover {
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.05);
    }

    .file-upload-area.dragover {
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.1);
    }

    .document-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .document-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }

    .file-icon-wrapper {
        transition: transform 0.2s;
    }

    .document-card:hover .file-icon-wrapper {
        transform: scale(1.1);
    }

    .file-icon-large {
        transition: transform 0.3s;
    }

    .file-icon-large:hover {
        transform: scale(1.05);
    }

    .badge {
        font-size: 0.75em;
        padding: 0.35em 0.65em;
    }

    .select2-container--default .select2-selection--multiple {
        min-height: 38px;
        border-color: #dee2e6;
    }

    .view-content {
        transition: opacity 0.3s;
    }

    .table th {
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
    }

    .table td {
        vertical-align: middle;
    }
</style>