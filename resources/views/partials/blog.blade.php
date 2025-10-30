@php use Illuminate\Support\Str; @endphp
<!-- Section Actualités & Événements -->
<section class="bg-gray-50 py-16">
    <div class="container mx-auto px-4 lg:px-6">
        <div class="flex flex-col lg:flex-row gap-10 xl:gap-16">
            {{-- Blogs / Actualités --}}
            <div class="flex flex-col gap-8 lg:w-2/3">
                <div class="flex items-center justify-between pb-4 border-b-2 border-green">
                    <h2 class="text-2xl lg:text-3xl font-bold text-green uppercase">Actualités</h2>
                    @if(request()->routeIs('home'))
                    <a href="{{ route('blogs.index') }}" class="flex items-center gap-2 text-green font-semibold hover:text-yellow transition-colors">
                        <span>Voir plus</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </a>
                    @endif
                </div>

                @if($blogs->isEmpty())
                <div class="bg-white rounded-xl shadow-md p-8 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-lg font-medium text-gray-600">
                        Désolé, aucune actualité n'a encore été publiée. Revenez plus tard pour de nouvelles actualités.
                    </p>
                </div>
                @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($blogs as $blog)
                    <article class="bg-white rounded-xl overflow-hidden shadow-lg transition-transform duration-300 hover:shadow-xl hover:-translate-y-1">
                        <div class="relative overflow-hidden">
                            <img class="w-full h-48 object-cover transition-transform duration-500 hover:scale-105" src="{{ Storage::disk('public')->url($blog->image) }}" alt="{{ $blog->title }}">
                            <div class="absolute top-4 left-4 bg-green text-white text-sm font-semibold px-3 py-1 rounded-full">
                                {{ $blog->publication_date->translatedFormat('d M Y') }}
                            </div>
                        </div>
                        <div class="p-5">
                            <h3 class="text-xl font-bold text-gray-800 mb-3 line-clamp-2">
                                <a href="{{ route('blogs.show', $blog) }}" class="hover:text-green transition-colors">
                                    {{ $blog->title }}
                                </a>
                            </h3>
                            <p class="text-sm text-gray-500 mb-3">Par <span class="font-medium">{{ $blog->author_display_name }}</span></p>
                            <p class="text-gray-600 mb-4 line-clamp-3">
                                {!! strip_tags(Str::limit($blog->content, 120)) !!}
                            </p>
                            <div class="flex items-center justify-between">
                                <a href="{{ route('blogs.show', $blog) }}" class="inline-flex items-center text-green font-semibold hover:text-yellow transition-colors">
                                    Lire la suite
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    {{ rand(50, 300) }}
                                </div>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
                @endif

                @unless(request()->routeIs('home'))
                <div class="mt-8">
                    {{ $blogs->links() }}
                </div>
                @endunless
            </div>

            {{-- Événements --}}
            <div class="lg:w-1/3 flex flex-col">
                <div class="flex items-center justify-between pb-4 border-b-2 border-green mb-6">
                    <h2 class="text-2xl lg:text-3xl font-bold text-green uppercase">Événements</h2>
                    @if(request()->routeIs('home'))
                    <a href="{{ route('events.search') }}" class="flex items-center gap-2 text-green font-semibold hover:text-yellow transition-colors">
                        <span>Voir plus</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </a>
                    @endif
                </div>

                @if($events->isEmpty())
                <div class="bg-white rounded-xl shadow-md p-6 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 mx-auto text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-gray-600 font-medium">
                        Aucun événement à venir. Revenez plus tard pour vous tenir informé.
                    </p>
                </div>
                @else
                <div class="space-y-6">
                    @foreach($events as $event)
                    <div class="bg-white rounded-xl shadow-md p-5 border-l-4 border-green transition-transform duration-300 hover:shadow-lg">
                        <div class="flex items-start mb-4">
                            {{-- <div class="bg-yellow text-green p-3 rounded-lg mr-4 flex-shrink-0">
                                <span class="block text-xl font-bold">{{ $event->start_date->format('d') }}</span>
                                <span class="block text-sm uppercase">{{ $event->start_date->format('M') }}</span>
                            </div> --}}
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-1">
                                    <a href="{{ url('/events/'.$event->id) }}" class="hover:text-green transition-colors">
                                        {{ $event->nom }}
                                    </a>
                                </h3>
                                <p class="text-sm text-gray-600 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ $event->location ?? 'En ligne' }}
                                </p>
                            </div>
                        </div>
                        
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                            {!! strip_tags(Str::limit($event->details, 100)) !!}
                        </p>
                        
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center text-sm text-green font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{-- @if($event->end_date)
                                    {{ $event->start_date->format('H:i') }} - {{ $event->end_date->format('H:i') }}
                                @else
                                    {{ $event->start_date->format('H:i') }}
                                @endif --}}
                            </span>
                            
                            <a href="{{ url('/events/'.$event->id) }}" class="text-sm font-semibold text-green hover:text-yellow transition-colors">
                                Voir détails
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</section>