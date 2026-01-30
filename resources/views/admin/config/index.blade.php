@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

@extends('base', [
    'title' => 'Configuration du système',
    'breadcrumbs' => ['Administration', 'Paramètres', 'Configuration système'],
    'page_name' => 'Configuration générale de l\'établissement',
])

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="ti ti-settings"></i> Paramètres de configuration
                    </h5>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.configuration.update') }}"
                          method="POST"
                          enctype="multipart/form-data"
                          id="config-form">
                        @csrf
                        @method('put')

                        <div class="row g-4">
                            @foreach ($configurations as $config)
                                @php
                                    $name = Str::slug($config->key, '_');
                                @endphp

                                <div class="col-md-12">
                                    <div class="card h-100 border rounded-3 shadow-sm">
                                        <div class="card-body">

                                            {{-- FILE --}}
                                            @if ($config->type === 'file')
                                                <label class="form-label fw-bold">
                                                    <i class="ti ti-photo"></i>
                                                    {{ $config->valueKey ?? $config->key }}
                                                </label>
                                                <input type="file"
                                                       id="file_{{ $name }}"
                                                       name="config_value[{{ $name }}]"
                                                       class="d-none"
                                                       accept="image/*">

                                                @if ($config->value && Storage::disk('public')->exists($config->value))
                                                    <div id="existing_{{ $name }}" class="text-center mb-3">
                                                        <img src="{{ Storage::url($config->value) }}"
                                                             class="img-fluid rounded shadow-sm"
                                                             style="max-height:220px;object-fit:contain">

                                                        <div class="mt-2 d-flex justify-content-center gap-2">
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-primary"
                                                                    onclick="openFile('{{ $name }}')">
                                                                <i class="ti ti-edit"></i> Modifier
                                                            </button>
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    onclick="removeImage('{{ $name }}')">
                                                                <i class="ti ti-trash"></i> Supprimer
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div id="zone_{{ $name }}"
                                                     class="upload-zone {{ $config->value ? 'd-none' : '' }}"
                                                     data-input="file_{{ $name }}">
                                                    <i class="ti ti-cloud-upload fs-1"></i>
                                                    <p class="mb-1 fw-semibold">Glissez l’image ici</p>
                                                    <small class="text-muted">ou cliquez pour sélectionner</small>
                                                </div>

                                                <div id="preview_{{ $name }}" class="text-center mt-3 d-none">
                                                    <img id="preview_img_{{ $name }}"
                                                         class="img-fluid rounded shadow-sm"
                                                         style="max-height:220px;object-fit:contain">
                                                    <div class="mt-2">
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-secondary"
                                                                onclick="cancelPreview('{{ $name }}')">
                                                            Annuler
                                                        </button>
                                                    </div>
                                                </div>

                                            {{-- BOOLEAN --}}
                                            @elseif ($config->type === 'boolean')
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           id="{{ $name }}"
                                                           name="config_value[{{ $name }}]"
                                                           value="1"
                                                           {{ $config->value ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold">
                                                        {{ $config->valueKey ?? $config->key }}
                                                    </label>
                                                </div>

                                            {{-- SELECT --}}
                                            @elseif ($config->type === 'select')
                                                @php
                                                    $options = collect(explode(',', $config->options))
                                                        ->map(fn($o) => explode('|', $o));
                                                @endphp

                                                <label class="form-label fw-semibold mb-2">
                                                    {{ $config->valueKey ?? $config->key }}
                                                </label>

                                                @if($name === 'systeme_pedagogique_de_etablissement')
                                                    <select name="config_value[{{ $name }}]" 
                                                            class="form-select mb-3" 
                                                            id="systeme_pedagogique_select">
                                                        @foreach ($options as [$val, $label])
                                                            <option value="{{ $val }}"
                                                                @selected($config->value === $val)>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    {{-- Options dépendantes --}}
                                                    <div id="options_unites" class="mt-2 p-3 border rounded-2 bg-light" style="display:none;">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" name="afficher_unites" id="afficher_unites">
                                                            <label class="form-check-label fw-semibold">Afficher les unités d’enseignement</label>
                                                        </div>
                                                    </div>

                                                    <div id="options_ue_uv" class="mt-2 p-3 border rounded-2 bg-light" style="display:none;">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" name="afficher_uv" id="afficher_uv">
                                                            <label class="form-check-label fw-semibold">Afficher UE / UV détaillés</label>
                                                        </div>
                                                    </div>
                                                @else
                                                    <select name="config_value[{{ $name }}]" class="form-select">
                                                        @foreach ($options as [$val, $label])
                                                            <option value="{{ $val }}" @selected($config->value === $val)>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif

                                            {{-- TEXTAREA --}}
                                            @elseif ($config->type === 'textarea')
                                                <label class="form-label fw-semibold">
                                                    {{ $config->valueKey ?? $config->key }}
                                                </label>
                                                <textarea name="config_value[{{ $name }}]"
                                                          class="form-control"
                                                          rows="4">{{ $config->value }}</textarea>

                                            {{-- TEXT --}}
                                            @else
                                                <label class="form-label fw-semibold">
                                                    {{ $config->valueKey ?? $config->key }}
                                                </label>
                                                <input type="text"
                                                       name="config_value[{{ $name }}]"
                                                       value="{{ $config->value }}"
                                                       class="form-control">
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" required>
                                <label class="form-check-label fw-semibold">Je confirme les modifications</label>
                            </div>
                            <button class="btn btn-primary px-4">
                                <i class="ti ti-device-floppy"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
.upload-zone {
    border: 2px dashed #cbd5e1;
    padding: 30px;
    text-align: center;
    cursor: pointer;
    border-radius: 10px;
    transition: .3s;
}
.upload-zone:hover {
    background: #f1f5f9;
    border-color: #3b82f6;
}
.upload-zone.drag {
    background: #dbeafe;
}
.card-body input.form-check-input {
    transform: scale(1.2);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Drag & Drop
    document.querySelectorAll('.upload-zone').forEach(zone => {
        const input = document.getElementById(zone.dataset.input);
        const name = zone.id.replace('zone_', '');

        zone.onclick = () => input.click();
        zone.ondragover = e => { e.preventDefault(); zone.classList.add('drag'); };
        zone.ondragleave = () => zone.classList.remove('drag');
        zone.ondrop = e => {
            e.preventDefault();
            zone.classList.remove('drag');
            input.files = e.dataTransfer.files;
            preview(input, name);
        };
        input.onchange = () => preview(input, name);
    });

    function preview(input, name) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById(`preview_img_${name}`).src = e.target.result;
            document.getElementById(`preview_${name}`).classList.remove('d-none');
            document.getElementById(`zone_${name}`)?.classList.add('d-none');
            document.getElementById(`existing_${name}`)?.classList.add('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }

    window.cancelPreview = function(name) {
        document.getElementById(`preview_${name}`).classList.add('d-none');
        document.getElementById(`zone_${name}`).classList.remove('d-none');
        document.getElementById(`file_${name}`).value = '';
    }

    window.openFile = function(name) { document.getElementById(`file_${name}`).click(); }
    window.removeImage = function(name) {
        if (!confirm('Supprimer cette image ?')) return;
        document.getElementById(`existing_${name}`)?.classList.add('d-none');
        document.getElementById(`zone_${name}`).classList.remove('d-none');
    }

    // Système pédagogique
    const selectSysteme = document.getElementById('systeme_pedagogique_select');
    const optsUnites = document.getElementById('options_unites');
    const optsUEUV = document.getElementById('options_ue_uv');

    function updateOptions() {
        if (selectSysteme.value === 'unites') {
            optsUnites.style.display = 'block';
            optsUEUV.style.display = 'none';
        } else if (selectSysteme.value === 'ue_uv') {
            optsUnites.style.display = 'none';
            optsUEUV.style.display = 'block';
        } else {
            optsUnites.style.display = 'none';
            optsUEUV.style.display = 'none';
        }
    }

    selectSysteme.addEventListener('change', updateOptions);
    updateOptions(); 
});
</script>
