@php use Illuminate\Support\Facades\Storage; @endphp
@extends('base', [
    'title' => 'Modifier l\'album',
    'page_name' => 'Modifier l\'album',
    'breadcrumbs' => [
        'Galerie',
        'Albums',
        'Modifier'
    ]
])

@section('content')
<div class="col-sm-12">
    <div class="card col-12 col-lg-8 mx-auto">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.gallery.albums.update', $album) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" value="{{ old('name', $album->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Slug (optionnel)</label>
                    <input type="text" name="slug" value="{{ old('slug', $album->slug) }}" class="form-control @error('slug') is-invalid @enderror">
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $album->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Image de couverture</label>
                    @if($album->cover_path)
                        <div class="mb-2">
                            <img src="{{ Storage::url($album->cover_path) }}" alt="cover" style="height: 120px; object-fit: cover" class="rounded border">
                        </div>
                    @endif
                    <input type="file" name="cover" class="form-control @error('cover') is-invalid @enderror" accept="image/*">
                    @error('cover')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_published" id="is_published" value="1" {{ old('is_published', $album->is_published) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_published">Publié</label>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.gallery.albums.index') }}" class="btn btn-link">Annuler</a>
                    <button class="btn btn-primary" type="submit">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
