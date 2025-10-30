@php use Illuminate\Support\Facades\Storage; @endphp
@extends('base', [
    'title' => $album->name,
    'page_name' => 'Album: ' . $album->name,
    'breadcrumbs' => [
        'Galerie',
        'Albums',
        $album->name
    ]
])

@section('content')
<div class="col-sm-12">
    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="mb-2">Informations</h5>
                    @if($album->cover_path)
                        <img src="{{ Storage::url($album->cover_path) }}" class="img-fluid rounded mb-3" alt="cover">
                    @endif
                    <p class="mb-2"><strong>Nom:</strong> {{ $album->name }}</p>
                    @if($album->description)
                        <p class="text-muted">{{ $album->description }}</p>
                    @endif
                    <p class="mb-2"><strong>Statut:</strong> {{ $album->is_published ? 'Publié' : 'Brouillon' }}</p>

                    <div class="d-flex gap-2 mt-3">
                        <a href="{{ route('admin.gallery.albums.edit', $album) }}" class="btn btn-warning">Modifier</a>
                        <a href="{{ route('admin.gallery.photos.index', ['album' => $album->id]) }}" class="btn btn-outline-primary">Voir les photos</a>
                        <a href="{{ route('admin.gallery.photos.create', ['album' => $album->id]) }}" class="btn btn-primary">Ajouter une photo</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Photos ({{ $album->photos->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse($album->photos as $photo)
                            <div class="col-6 col-md-4">
                                <div class="card h-100">
                                    <img src="{{ Storage::url($photo->file_path) }}" class="card-img-top" style="object-fit: cover; height: 9rem" alt="{{ $photo->alt_text }}">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="{{ route('admin.gallery.photos.show', $photo) }}" class="small text-truncate">{{ $photo->title ?? 'Photo' }}</a>
                                            <span class="badge bg-light text-dark">#{{ $photo->position }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted">Aucune photo pour cet album.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
