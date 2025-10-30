@php use Illuminate\Support\Facades\Storage; @endphp
@extends('base', [
    'title' => 'Modifier la photo',
    'page_name' => 'Modifier la photo',
    'breadcrumbs' => [
        'Galerie',
        'Photos',
        'Modifier'
    ]
])

@section('content')
<div class="col-sm-12">
    <div class="card col-12 col-lg-8 mx-auto">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.gallery.photos.update', $photo) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Album</label>
                    <select name="gallery_album_id" class="form-select @error('gallery_album_id') is-invalid @enderror" required>
                        @foreach($albums as $a)
                            <option value="{{ $a->id }}" {{ old('gallery_album_id', $photo->gallery_album_id) == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                        @endforeach
                    </select>
                    @error('gallery_album_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Image (laisser vide pour conserver)</label>
                    <div class="mb-2">
                        <img src="{{ Storage::url($photo->file_path) }}" alt="image" style="height: 120px; object-fit: cover" class="rounded border">
                    </div>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept="image/*">
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Titre</label>
                        <input type="text" name="title" value="{{ old('title', $photo->title) }}" class="form-control @error('title') is-invalid @enderror">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Texte alternatif (alt)</label>
                        <input type="text" name="alt_text" value="{{ old('alt_text', $photo->alt_text) }}" class="form-control @error('alt_text') is-invalid @enderror">
                        @error('alt_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label">Légende</label>
                    <textarea name="caption" rows="3" class="form-control @error('caption') is-invalid @enderror">{{ old('caption', $photo->caption) }}</textarea>
                    @error('caption')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Position</label>
                        <input type="number" name="position" value="{{ old('position', $photo->position) }}" class="form-control @error('position') is-invalid @enderror" min="0">
                        @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de prise</label>
                        <input type="date" name="taken_at" value="{{ old('taken_at', optional($photo->taken_at)->format('Y-m-d')) }}" class="form-control @error('taken_at') is-invalid @enderror">
                        @error('taken_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $photo->is_published) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_published">Publié</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('admin.gallery.photos.index', ['album' => $photo->gallery_album_id]) }}" class="btn btn-link">Annuler</a>
                    <button class="btn btn-primary" type="submit">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
