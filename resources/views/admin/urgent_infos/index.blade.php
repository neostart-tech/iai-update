@extends('base', [
    'title' => "Informations urgentes",
    'page_name' => "Informations urgentes",
    'breadcrumbs' => [
        'Communication',
        "Infos urgentes"
    ]
])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Informations urgentes</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#urgentInfoModal" id="btn-open-create">
                    Ajouter
                </button>
            </div>
            <div class="card-body table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Publié</th>
                            <th>Document</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $item->title }}</div>
                                    <small class="text-muted">{{ Str::limit($item->summary, 80) }}</small>
                                </td>
                                <td>
                                    @if($item->is_published)
                                        <span class="badge bg-success">Publié</span>
                                        <br>
                                        <small class="text-muted">{{ optional($item->published_at)->diffForHumans() }}</small>
                                    @else
                                        <span class="badge bg-secondary">Brouillon</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $docUrl = $item->file_path ? asset('storage/'.$item->file_path) : $item->file_url;
                                    @endphp
                                    @if($docUrl)
                                        <a href="{{ $docUrl }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Ouvrir">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M14 3h7v7h-2V6.414l-9.293 9.293-1.414-1.414L17.586 5H14V3z"/><path d="M19 19H5V5h7V3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7h-2v7z"/></svg>
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button"
                                                class="btn btn-sm btn-primary btn-edit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#urgentInfoModal"
                                                data-update-url="{{ route('admin.urgent_infos.update', $item) }}"
                                                data-title="{{ $item->title }}"
                                                data-summary="{{ $item->summary }}"
                                                data-file_url="{{ $item->file_url }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/><path d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                        </button>
                                        @if(!$item->is_published)
                                            <form method="post" action="{{ route('admin.urgent_infos.publish', $item) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-success" type="submit" title="Publier">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M5 12h14v2H5z"/><path d="M12 5l7 7-1.41 1.41L12 7.83l-5.59 5.58L5 12z"/></svg>
                                                </button>
                                            </form>
                                        @else
                                            <form method="post" action="{{ route('admin.urgent_infos.unpublish', $item) }}">
                                                @csrf
                                                <button class="btn btn-sm btn-warning" type="submit" title="Dépublier">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M19 12H5v-2h14z"/><path d="M12 19l-7-7 1.41-1.41L12 16.17l5.59-5.58L19 12z"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="post" action="{{ route('admin.urgent_infos.destroy', $item) }}" onsubmit="return confirm('Supprimer cette information ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" type="submit" title="Supprimer">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M6 7h12v2H6z"/><path d="M9 9h2v10H9zM13 9h2v10h-2z"/><path d="M19 7h-3.5l-1-1h-5l-1 1H5v2h14zM6 9v11a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V9H6z"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Aucune information urgente pour le moment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div>
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Create/Edit Urgent Info -->
<div class="modal fade" id="urgentInfoModal" tabindex="-1" aria-labelledby="urgentInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="urgentInfoModalLabel">Nouvelle information urgente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="urgentInfoForm" action="{{ route('admin.urgent_infos.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="urgentInfoFormMethod" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Titre <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="urgent_title" class="form-control" required>
                        @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Résumé</label>
                        <textarea name="summary" id="urgent_summary" class="form-control" rows="3"></textarea>
                        @error('summary')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lien du document (URL)</label>
                        <input type="url" name="file_url" id="urgent_file_url" class="form-control" placeholder="https://...">
                        @error('file_url')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ou téléverser un fichier</label>
                        <input type="file" name="file" id="urgent_file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.zip,.rar">
                        @error('file')<small class="text-danger">{{ $message }}</small>@enderror
                        <small class="text-muted">Max 10 Mo. Si un fichier et un lien sont fournis, le fichier sera prioritaire.</small>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="urgent_is_published" name="is_published" value="1">
                        <label class="form-check-label" for="urgent_is_published">Publier immédiatement</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="urgent_submit_btn">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    </div>
@endsection

@section('other-js')
    @parent
    @if(session('success'))
        <script>
            // SweetAlert2 success popup, with Notyf fallback
            if (window.Swal && typeof Swal.fire === 'function') {
                Swal.fire({
                    icon: 'success',
                    title: @json(session('success')),
                    timer: 2500,
                    showConfirmButton: false
                });
            } else if (typeof showToast === 'function') {
                showToast(@json(session('success')), 'success');
            } else {
                console.log('SUCCESS:', @json(session('success')));
            }
        </script>
    @endif
    <script>
        // Open modal in CREATE mode
        const createBtn = document.getElementById('btn-open-create');
        const form = document.getElementById('urgentInfoForm');
        const methodInput = document.getElementById('urgentInfoFormMethod');
        const titleInput = document.getElementById('urgent_title');
        const summaryInput = document.getElementById('urgent_summary');
        const fileUrlInput = document.getElementById('urgent_file_url');
        const publishInput = document.getElementById('urgent_is_published');
        const fileInput = document.getElementById('urgent_file');
        const modalTitle = document.getElementById('urgentInfoModalLabel');

        if (createBtn) {
            createBtn.addEventListener('click', () => {
                modalTitle.textContent = 'Nouvelle information urgente';
                form.action = @json(route('admin.urgent_infos.store'));
                methodInput.value = 'POST';
                // Reset fields
                titleInput.value = '';
                summaryInput.value = '';
                fileUrlInput.value = '';
                publishInput.checked = false;
                if (fileInput) fileInput.value = '';
            });
        }

        // Open modal in EDIT mode
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                modalTitle.textContent = 'Modifier une information urgente';
                form.action = btn.getAttribute('data-update-url');
                methodInput.value = 'PUT';
                titleInput.value = btn.getAttribute('data-title') || '';
                summaryInput.value = btn.getAttribute('data-summary') || '';
                fileUrlInput.value = btn.getAttribute('data-file_url') || '';
                publishInput.checked = false; // editing does not toggle publish by default
                if (fileInput) fileInput.value = '';
            });
        });
    </script>
@endsection
