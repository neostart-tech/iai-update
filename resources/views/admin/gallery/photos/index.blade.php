@php use Illuminate\Support\Facades\Storage; @endphp
@extends('base', [
    'title' => 'Photos de la galerie',
    'page_name' => 'Photos de la galerie',
    'breadcrumbs' => [
        'Galerie',
        'Photos'
    ]
])

@section('content')
<div class="col-sm-12">
    <div class="card col-12 mx-auto">
        <div class="card-header">
            <form method="GET" action="{{ route('admin.gallery.photos.index') }}" class="row g-2 align-items-end p-3">
                <div class="col-md-4">
                    <label class="form-label">Filtrer par album</label>
                    <select name="album" class="form-select" onchange="this.form.submit()">
                        <option value="">— Tous les albums —</option>
                        @foreach($albums as $a)
                            <option value="{{ $a->id }}" {{ (string)$albumId === (string)$a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8 text-end">
                    <a href="{{ route('admin.gallery.photos.create', $albumId ? ['album' => $albumId] : []) }}" class="btn btn-primary">Ajouter une photo</a>
                </div>
            </form>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="row g-3">
                @forelse($photos as $photo)
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <div class="card-img-top position-relative">
                                <a href="{{ route('admin.gallery.photos.show', $photo) }}">
                                    <img src="{{ Storage::url($photo->file_path) }}" alt="image" class="img-fluid w-100" style="object-fit: cover; height: 11rem" />
                                </a>
                                <div class="position-absolute end-0 bottom-0 p-2 d-flex gap-2">
                                    <a class="btn btn-warning" href="{{ route('admin.gallery.photos.edit', $photo) }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.gallery.photos.destroy', $photo) }}" onsubmit="return confirm('Supprimer cette photo ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body p-2">
                                <div class="small text-muted">Album: {{ $photo->album?->name }}</div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-truncate">{{ $photo->title ?? 'Photo' }}</span>
                                    <span class="badge bg-light text-dark">#{{ $photo->position }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">Aucune photo pour le moment.</div>
                @endforelse
            </div>

            <div class="mt-3">
                {{ $photos->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
