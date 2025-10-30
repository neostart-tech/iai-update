@extends('layouts.master')

@section('content')
<div class="container mx-auto mt-8">
    <h1 class="text-2xl font-bold mb-6">Résultats de recherche pour : "{{ $q }}"</h1>
    <div class="bg-white p-6 rounded shadow">
        @if($results->isEmpty())
            <p>Aucune actualité trouvée.</p>
        @else
            <ul>
                @foreach($results as $event)
                    <li class="mb-4 border-b pb-2">
                        <a href="{{ route('events.show', $event->id) }}" class="text-blue-600 hover:underline text-lg font-semibold">
                            {{ $event->nom }}
                        </a>
                        <div class="text-xs text-gray-500">
                            {{ $event->publication_date ? \Carbon\Carbon::parse($event->publication_date)->format('d/m/Y H:i') : '' }}
                        </div>
                        <div class="mt-2 text-gray-700">{{ Str::limit(strip_tags($event->details), 120) }}</div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
