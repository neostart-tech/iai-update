@php use Illuminate\Support\Facades\Storage; use Illuminate\Support\Str; @endphp
@extends('base', [
    'title' => 'Albums de la galerie',
    'page_name' => 'Albums de la galerie',
    'breadcrumbs' => [
        'Galerie',
        'Albums'
    ]
])

@section('content')
<div class="col-sm-12">
    <div class="card col-12 mx-auto">
        <div class="card-header">
            <div class="text-end p-4 pb-sm-2 mb-2">
                <a href="{{ route('admin.gallery.albums.create') }}" class="btn btn-primary">
                    Ajouter un album
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="row g-3">
                @forelse($albums as $album)
                    <div class="col-sm-6 col-xl-4">
                        <div class="card h-100">
                            <div class="card-img-top position-relative">
                                <a href="{{ route('admin.gallery.albums.show', $album) }}">
                                    @if($album->cover_path)
                                        <img src="{{ Storage::url($album->cover_path) }}" alt="cover"
                                             class="img-fluid w-100" style="object-fit: cover; height: 14rem" />
                                    @else
                                        <div class="d-flex align-items-center justify-content-center bg-light" style="height:14rem">
                                            <span class="text-muted">Pas de couverture</span>
                                        </div>
                                    @endif
                                </a>
                                <div class="position-absolute end-0 bottom-0 p-2 d-flex gap-2">
                                    <a class="btn btn-warning" href="{{ route('admin.gallery.albums.edit', $album) }}">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.gallery.albums.destroy', $album) }}" onsubmit="return confirm('Supprimer cet album ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title mb-1">{{ $album->name }}</h5>
                                <p class="text-muted mb-2">{{ Str::limit($album->description, 120) }}</p>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>{{ $album->photos_count }} photo(s)</span>
                                    <span>{{ $album->is_published ? 'Publié' : 'Brouillon' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">Aucun album pour le moment.</div>
                @endforelse
            </div>

            <div class="mt-3">
                {{ $albums->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
