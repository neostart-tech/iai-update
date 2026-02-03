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
                                    $hasValidFile = false;
                                    
                                    if ($config->type === 'file' && $config->value) {
                                        try {
                                            $hasValidFile = Storage::disk('public')->exists($config->value);
                                        } catch (\Exception $e) {
                                            $hasValidFile = false;
                                        }
                                    }
                                @endphp

                                <div class="col-md-12">
                                    <div class="card h-100 border rounded-3 shadow-sm">
                                        <div class="card-body">

                                            {{-- FILE --}}
                                            @if ($config->type === 'file')
                                                <label class="form-label fw-bold d-block mb-3">
                                                    <i class="ti ti-photo"></i>
                                                    {{ $config->valueKey ?? $config->key }}
                                                </label>
                                                
                                                <input type="file"
                                                       id="file_{{ $name }}"
                                                       name="config_value[{{ $name }}]"
                                                       class="d-none"
                                                       accept="image/*">
                                                
                                                <!-- Zone de dépôt TOUJOURS visible -->
                                                <div id="zone_{{ $name }}" 
                                                     class="upload-zone"
                                                     data-input="file_{{ $name }}">
                                                    <div class="upload-content">
                                                        <i class="ti ti-cloud-upload fs-1 mb-2"></i>
                                                        <p class="mb-1 fw-semibold">Glissez l'image ici</p>
                                                        <small class="text-muted d-block">ou cliquez pour sélectionner</small>
                                                        <small class="text-muted d-block mt-1">Formats : JPG, PNG, GIF, SVG (max 2MB)</small>
                                                    </div>
                                                </div>
                                                
                                                <!-- Image existante (si valide) -->
                                                @if($hasValidFile)
                                                <div id="existing_{{ $name }}" class="text-center mb-3 mt-3">
                                                    <p class="text-muted mb-2">
                                                        <i class="ti ti-photo-check"></i> Image actuelle
                                                    </p>
                                                    <img src="{{ Storage::url($config->value) }}"
                                                         class="img-fluid rounded shadow-sm border"
                                                         style="max-height:200px;object-fit:contain"
                                                         onerror="handleImageError('{{ $name }}')">
                                                    
                                                    <div class="mt-3 d-flex justify-content-center gap-2">
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-primary"
                                                                onclick="openFile('{{ $name }}')">
                                                            <i class="ti ti-refresh"></i> Remplacer
                                                        </button>
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger"
                                                                onclick="removeImage('{{ $name }}')">
                                                            <i class="ti ti-trash"></i> Supprimer
                                                        </button>
                                                    </div>
                                                </div>
                                                @endif
                                                
                                                <!-- Prévisualisation de la nouvelle image -->
                                                <div id="preview_{{ $name }}" class="text-center mt-3 d-none">
                                                    <p class="text-muted mb-2">
                                                        <i class="ti ti-photo-up"></i> Nouvelle image
                                                    </p>
                                                    <img id="preview_img_{{ $name }}"
                                                         class="img-fluid rounded shadow-sm border"
                                                         style="max-height:200px;object-fit:contain">
                                                    <div class="mt-3 d-flex justify-content-center gap-2">
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-success"
                                                                onclick="confirmUpload('{{ $name }}')">
                                                            <i class="ti ti-check"></i> Conserver
                                                        </button>
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-secondary"
                                                                onclick="cancelUpload('{{ $name }}')">
                                                            <i class="ti ti-x"></i> Annuler
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <!-- Message d'erreur -->
                                                <div id="error_{{ $name }}" class="alert alert-danger mt-3 d-none"></div>
                                                
                                                <!-- Champ caché pour suppression -->
                                                <input type="hidden" 
                                                       id="delete_flag_{{ $name }}" 
                                                       name="delete_file[{{ $name }}]" 
                                                       value="0">

                                            {{-- BOOLEAN --}}
                                            @elseif ($config->type === 'boolean')
                                                <div class="form-check form-switch mt-3">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           id="{{ $name }}"
                                                           name="config_value[{{ $name }}]"
                                                           value="1"
                                                           {{ $config->value ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold ms-2">
                                                        {{ $config->valueKey ?? $config->key }}
                                                    </label>
                                                </div>

                                            {{-- SELECT --}}
                                            @elseif ($config->type === 'select')
                                                @php
                                                    $options = collect(explode(',', $config->options))
                                                        ->map(function($option) {
                                                            $parts = explode('|', $option);
                                                            return [
                                                                'value' => $parts[0] ?? '',
                                                                'label' => $parts[1] ?? $parts[0] ?? ''
                                                            ];
                                                        })
                                                        ->filter(fn($opt) => !empty($opt['value']));
                                                @endphp

                                                <label class="form-label fw-semibold mb-2">
                                                    {{ $config->valueKey ?? $config->key }}
                                                </label>

                                                @if($name === 'systeme_pedagogique_de_etablissement')
                                                    <select name="config_value[{{ $name }}]" 
                                                            class="form-select mb-3" 
                                                            id="systeme_pedagogique_select">
                                                        <option value="">-- Sélectionnez --</option>
                                                        @foreach ($options as $option)
                                                            <option value="{{ $option['value'] }}"
                                                                @selected($config->value === $option['value'])>
                                                                {{ $option['label'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    {{-- Options dépendantes --}}
                                                    <div id="options_unites" class="mt-2 p-3 border rounded-2 bg-light" style="display:none;">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" name="afficher_unites" id="afficher_unites">
                                                            <label class="form-check-label fw-semibold">Afficher les unités d'enseignement</label>
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
                                                        <option value="">-- Sélectionnez --</option>
                                                        @foreach ($options as $option)
                                                            <option value="{{ $option['value'] }}" 
                                                                    @selected($config->value === $option['value'])>
                                                                {{ $option['label'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif

                                            {{-- TEXTAREA --}}
                                            @elseif ($config->type === 'textarea')
                                                <label class="form-label fw-semibold mb-2">
                                                    {{ $config->valueKey ?? $config->key }}
                                                </label>
                                                <textarea name="config_value[{{ $name }}]"
                                                          class="form-control"
                                                          rows="4"
                                                          placeholder="Entrez la valeur ici...">{{ old('config_value.' . $name, $config->value) }}</textarea>

                                            {{-- TEXT --}}
                                            @else
                                                <label class="form-label fw-semibold mb-2">
                                                    {{ $config->valueKey ?? $config->key }}
                                                </label>
                                                <input type="text"
                                                       name="config_value[{{ $name }}]"
                                                       value="{{ old('config_value.' . $name, $config->value) }}"
                                                       class="form-control"
                                                       placeholder="Entrez la valeur ici...">
                                            @endif

                                            @error('config_value.' . $name)
                                                <div class="text-danger mt-1">
                                                    <small>{{ $message }}</small>
                                                </div>
                                            @enderror

                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirm_changes" required>
                                <label class="form-check-label fw-semibold" for="confirm_changes">
                                    <i class="ti ti-shield-check"></i> Je confirme les modifications
                                </label>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary px-4 me-2" onclick="resetForm()">
                                    <i class="ti ti-reload"></i> Réinitialiser
                                </button>
                                <button type="submit" class="btn btn-primary px-4" id="submit-btn">
                                    <i class="ti ti-device-floppy"></i> Enregistrer
                                </button>
                            </div>
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
    transition: all 0.3s ease;
    background: #f8fafc;
    min-height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.upload-zone:hover {
    background: #f1f5f9;
    border-color: #3b82f6;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.upload-zone.drag-active {
    background: #dbeafe;
    border-color: #1d4ed8;
    border-style: solid;
}
.upload-content {
    color: #64748b;
}
.upload-zone:hover .upload-content {
    color: #3b82f6;
}
.form-check-input:checked {
    background-color: #3b82f6;
    border-color: #3b82f6;
}
.form-select:focus, .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
}
.btn-outline-primary:hover {
    background-color: #3b82f6;
    border-color: #3b82f6;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuration
    const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
    const ALLOWED_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/svg+xml'
    ];
    
    // Initialiser toutes les zones de dépôt
    initUploadZones();
    
    // Initialiser le sélecteur système pédagogique
    initSystemePedagogique();
    
    // Initialiser la gestion du formulaire
    initFormHandling();
    
    // Fonction pour initialiser les zones de dépôt
    function initUploadZones() {
        document.querySelectorAll('.upload-zone').forEach(zone => {
            const inputId = zone.dataset.input;
            const input = document.getElementById(inputId);
            const name = zone.id.replace('zone_', '');
            
            if (!input) return;
            
            // Clic sur la zone
            zone.addEventListener('click', (e) => {
                if (!e.target.closest('button')) {
                    input.click();
                }
            });
            
            // Drag & Drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                zone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                });
            });
            
            // Effets visuels du drag & drop
            ['dragenter', 'dragover'].forEach(eventName => {
                zone.addEventListener(eventName, () => {
                    zone.classList.add('drag-active');
                });
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                zone.addEventListener(eventName, () => {
                    zone.classList.remove('drag-active');
                });
            });
            
            // Gestion du drop
            zone.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFileSelection(files[0], name);
                }
            });
            
            // Changement via l'input file
            input.addEventListener('change', (e) => {
                if (input.files.length > 0) {
                    handleFileSelection(input.files[0], name);
                }
            });
        });
    }
    
    // Fonction pour gérer la sélection d'un fichier
    function handleFileSelection(file, name) {
        const errorDiv = document.getElementById(`error_${name}`);
        
        // Réinitialiser les erreurs
        clearError(name);
        
        // Validation
        if (!validateFile(file, name)) {
            return;
        }
        
        // Prévisualisation
        const reader = new FileReader();
        reader.onload = (e) => {
            // Afficher la prévisualisation
            const previewImg = document.getElementById(`preview_img_${name}`);
            const previewDiv = document.getElementById(`preview_${name}`);
            
            if (previewImg && previewDiv) {
                previewImg.src = e.target.result;
                previewDiv.classList.remove('d-none');
                
                // Masquer la zone de dépôt et l'image existante
                document.getElementById(`zone_${name}`).classList.add('d-none');
                const existingDiv = document.getElementById(`existing_${name}`);
                if (existingDiv) {
                    existingDiv.classList.add('d-none');
                }
                
                // Réinitialiser le drapeau de suppression
                document.getElementById(`delete_flag_${name}`).value = '0';
            }
        };
        reader.readAsDataURL(file);
    }
    
    // Fonction de validation de fichier
    function validateFile(file, name) {
        const errorDiv = document.getElementById(`error_${name}`);
        
        // Vérifier le type
        if (!ALLOWED_TYPES.includes(file.type)) {
            showError(name, 'Type de fichier non supporté. Utilisez JPG, PNG, GIF ou SVG.');
            return false;
        }
        
        // Vérifier la taille
        if (file.size > MAX_FILE_SIZE) {
            showError(name, `Fichier trop volumineux (${(file.size / 1024 / 1024).toFixed(2)} MB). Maximum 2MB.`);
            return false;
        }
        
        return true;
    }
    
    // Fonctions utilitaires d'erreur
    function showError(name, message) {
        const errorDiv = document.getElementById(`error_${name}`);
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.classList.remove('d-none');
            
            // Masquer automatiquement après 5 secondes
            setTimeout(() => {
                errorDiv.classList.add('d-none');
            }, 5000);
        }
    }
    
    function clearError(name) {
        const errorDiv = document.getElementById(`error_${name}`);
        if (errorDiv) {
            errorDiv.textContent = '';
            errorDiv.classList.add('d-none');
        }
    }
    
    // Fonctions globales
    window.openFile = function(name) {
        const input = document.getElementById(`file_${name}`);
        if (input) {
            input.click();
        }
    };
    
    window.removeImage = function(name) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette image ?')) {
            return;
        }
        
        // Activer le drapeau de suppression
        const deleteFlag = document.getElementById(`delete_flag_${name}`);
        if (deleteFlag) {
            deleteFlag.value = '1';
        }
        
        // Masquer l'image existante
        const existingDiv = document.getElementById(`existing_${name}`);
        if (existingDiv) {
            existingDiv.classList.add('d-none');
        }
        
        // Afficher la zone de dépôt
        const zoneDiv = document.getElementById(`zone_${name}`);
        if (zoneDiv) {
            zoneDiv.classList.remove('d-none');
        }
        
        // Masquer la prévisualisation
        const previewDiv = document.getElementById(`preview_${name}`);
        if (previewDiv) {
            previewDiv.classList.add('d-none');
        }
        
        // Réinitialiser l'input file
        const input = document.getElementById(`file_${name}`);
        if (input) {
            input.value = '';
        }
        
        clearError(name);
    };
    
    window.cancelUpload = function(name) {
        // Masquer la prévisualisation
        const previewDiv = document.getElementById(`preview_${name}`);
        if (previewDiv) {
            previewDiv.classList.add('d-none');
        }
        
        // Afficher la zone de dépôt
        const zoneDiv = document.getElementById(`zone_${name}`);
        if (zoneDiv) {
            zoneDiv.classList.remove('d-none');
        }
        
        // Afficher l'image existante si elle existe
        const existingDiv = document.getElementById(`existing_${name}`);
        if (existingDiv) {
            existingDiv.classList.remove('d-none');
        }
        
        // Réinitialiser l'input file
        const input = document.getElementById(`file_${name}`);
        if (input) {
            input.value = '';
        }
        
        clearError(name);
    };
    
    window.confirmUpload = function(name) {
        // Juste masquer le message de prévisualisation
        const previewDiv = document.getElementById(`preview_${name}`);
        if (previewDiv) {
            previewDiv.classList.add('d-none');
        }
        
        // Le fichier reste sélectionné dans l'input
        clearError(name);
    };
    
    window.handleImageError = function(name) {
        // Si l'image existante ne se charge pas
        const existingDiv = document.getElementById(`existing_${name}`);
        if (existingDiv) {
            existingDiv.classList.add('d-none');
        }
        
        // Afficher la zone de dépôt
        const zoneDiv = document.getElementById(`zone_${name}`);
        if (zoneDiv) {
            zoneDiv.classList.remove('d-none');
        }
        
        // Activer le drapeau de suppression automatique
        const deleteFlag = document.getElementById(`delete_flag_${name}`);
        if (deleteFlag) {
            deleteFlag.value = '1';
        }
    };
    
    // Système pédagogique
    function initSystemePedagogique() {
        const selectSysteme = document.getElementById('systeme_pedagogique_select');
        const optsUnites = document.getElementById('options_unites');
        const optsUEUV = document.getElementById('options_ue_uv');
        
        if (selectSysteme && optsUnites && optsUEUV) {
            function updateOptions() {
                const value = selectSysteme.value;
                optsUnites.style.display = value === 'unites' ? 'block' : 'none';
                optsUEUV.style.display = value === 'ue_uv' ? 'block' : 'none';
            }
            
            selectSysteme.addEventListener('change', updateOptions);
            updateOptions(); // Initialiser
        }
    }
    
    // Gestion du formulaire
    function initFormHandling() {
        const form = document.getElementById('config-form');
        const confirmCheckbox = document.getElementById('confirm_changes');
        const submitBtn = document.getElementById('submit-btn');
        
        if (form && confirmCheckbox && submitBtn) {
            form.addEventListener('submit', function(e) {
                if (!confirmCheckbox.checked) {
                    e.preventDefault();
                    alert('Veuillez confirmer les modifications en cochant la case de confirmation.');
                    confirmCheckbox.focus();
                    return;
                }
                
                // Désactiver le bouton pendant l'envoi
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ti ti-loader-2 spin"></i> Enregistrement...';
            });
        }
    }
    
    window.resetForm = function() {
        if (confirm('Voulez-vous vraiment réinitialiser tous les changements ? Les modifications non enregistrées seront perdues.')) {
            window.location.reload();
        }
    };
});

// Ajouter le style pour l'animation de spin
const style = document.createElement('style');
style.textContent = `
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.spin {
    animation: spin 1s linear infinite;
}
`;
document.head.appendChild(style);
</script>