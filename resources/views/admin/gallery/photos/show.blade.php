@php use Illuminate\Support\Facades\Storage; @endphp
@extends('base', [
    'title' => $photo->title ?? 'Photo',
    'page_name' => 'Détails de la photo',
    'breadcrumbs' => [
        'Galerie',
        'Photos',
        $photo->title ?? 'Photo'
    ]
])

@section('content')
<div class="col-sm-12">
    <div class="card col-12 col-lg-10 mx-auto">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-12 col-md-6">
                    <img src="{{ Storage::url($photo->file_path) }}" alt="image" class="img-fluid rounded w-100">
                </div>
                <div class="col-12 col-md-6">
                    <h4 class="mb-2">{{ $photo->title ?? 'Photo' }}</h4>
                    <p class="text-muted">Album: {{ $photo->album?->name }}</p>
                    @if($photo->caption)
                        <p>{{ $photo->caption }}</p>
                    @endif
                    @if($photo->taken_at)
                        <p class="mb-1"><strong>Prise le:</strong> {{ $photo->taken_at->format('d/m/Y') }}</p>
                    @endif
                    <p class="mb-1"><strong>Position:</strong> {{ $photo->position }}</p>
                    <p class="mb-3"><strong>Statut:</strong> {{ $photo->is_published ? 'Publié' : 'Brouillon' }}</p>

                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.gallery.photos.edit', $photo) }}" class="btn btn-warning">Modifier</a>
                        <a href="{{ route('admin.gallery.photos.index', ['album' => $photo->gallery_album_id]) }}" class="btn btn-outline-secondary">Retour à la liste</a>
                        <form method="POST" action="{{ route('admin.gallery.photos.destroy', $photo) }}" onsubmit="return confirm('Supprimer cette photo ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
