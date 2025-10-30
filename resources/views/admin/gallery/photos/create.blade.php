@php use Illuminate\Support\Facades\Storage; @endphp
@extends('base', [
    'title' => 'Ajouter une photo',
    'page_name' => 'Ajouter une photo',
    'breadcrumbs' => [
        'Galerie',
        'Photos',
        'Créer'
    ]
])

@section('content')
<div class="col-sm-12">
    <div class="card col-12 col-lg-8 mx-auto">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.gallery.photos.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Album</label>
                    @php $selectedId = old('gallery_album_id', $preselectedAlbumId ?? null); @endphp
                    @if($selectedId)
                        @php $selectedAlbum = $albums->firstWhere('id', (int) $selectedId); @endphp
                        <input type="hidden" name="gallery_album_id" value="{{ $selectedId }}">
                        <input type="text" class="form-control" value="{{ $selectedAlbum?->name ?? ('Album #'.$selectedId) }}" readonly>
                    @else
                        <select name="gallery_album_id" class="form-select @error('gallery_album_id') is-invalid @enderror" required>
                            <option value="">— Sélectionnez un album —</option>
                            @foreach($albums as $a)
                                <option value="{{ $a->id }}" {{ (string)old('gallery_album_id') === (string)$a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    @error('gallery_album_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Image(s)</label>
                    <input type="file" name="files[]" class="form-control @error('file') is-invalid @enderror @error('files') is-invalid @enderror @error('files.*') is-invalid @enderror" accept="image/*" multiple required>
                    <div class="form-text">Vous pouvez sélectionner une ou plusieurs images à la fois.</div>
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @error('files')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @error('files.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Titre</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Texte alternatif (alt)</label>
                        <input type="text" name="alt_text" value="{{ old('alt_text') }}" class="form-control @error('alt_text') is-invalid @enderror">
                        @error('alt_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label">Légende</label>
                    <textarea name="caption" rows="3" class="form-control @error('caption') is-invalid @enderror">{{ old('caption') }}</textarea>
                    @error('caption')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Position</label>
                        <input type="number" name="position" value="{{ old('position', 0) }}" class="form-control @error('position') is-invalid @enderror" min="0">
                        @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de prise</label>
                        <input type="date" name="taken_at" value="{{ old('taken_at') }}" class="form-control @error('taken_at') is-invalid @enderror">
                        @error('taken_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">Publier</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('admin.gallery.photos.index', ($preselectedAlbumId ?? null) ? ['album' => $preselectedAlbumId] : []) }}" class="btn btn-link">Annuler</a>
                    <button class="btn btn-primary" type="submit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
