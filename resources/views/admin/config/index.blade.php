@php
    use Illuminate\Support\Facades\Storage;
@endphp

@extends('base', [
    'title' => 'Page des paramètres',
    'breadcrumbs' => ['Administration', 'Paramètre', 'Édition'],
    'page_name' => 'Éditer les paramètres',
])

@section('content')
    <div class="card shadow-sm">


        <div class="card-body">
            <form action="{{ route('admin.configuration.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('put')

                <div class="row g-3">
                    @foreach ($configurations as $config)
                    @php
                        $input_name = Str::slug($config->key, '_');
                        $key_lower = Str::lower($config->key);
                        $is_file = Str::contains($key_lower, ['logo', 'favicon']);
                        $is_textarea = Str::contains($key_lower, ['description']);
                    @endphp
                
                    <div class="col-md-6 mb-3">
                        <label>{{ $config->key }}</label>
                
                        @if ($is_file)
                            <input type="file" name="config_value[{{ $input_name }}]" class="form-control">
                            @if ($config->value)
                                <img src="{{ Storage::url($config->value) }}" alt="{{ $config->key }}" width="100" class="mt-2">
                            @endif
                        @elseif ($is_textarea)
                            <textarea name="config_value[{{ $input_name }}]" rows="4" class="form-control">{{ $config->value }}</textarea>
                        @else
                            <input type="text" name="config_value[{{ $input_name }}]" value="{{ $config->value }}" class="form-control">
                        @endif
                    </div>
                @endforeach
                
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('other-css')
    <link rel="stylesheet" href="{{ asset('admin/assets/css/plugins/dataTables.bootstrap5.min.css') }}">
@endsection
