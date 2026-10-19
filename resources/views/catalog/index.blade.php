<x-app-layout title="BookSpace | Catálogo">
    <section class="bookspace-dashboard-hero">
        <div class="container bookspace-container">
            <div class="bookspace-section-heading bookspace-section-heading--dashboard">
                <div>
                    <span class="bookspace-eyebrow">Google Books</span>
                    <h1>Catálogo de libros</h1>
                    <p>Buscá por título, autor, categoría o ISBN.</p>
                </div>
            </div>

            <form class="bookspace-search" action="{{ route('catalog.index') }}" method="GET">
                <input name="q" type="search" class="form-control" value="{{ $query }}" placeholder="Ejemplo: Dune, Borges o isbn:978..." aria-label="Buscar libros" minlength="2">
                <button type="submit" class="btn bookspace-btn bookspace-btn--coral">Buscar</button>
            </form>
            @error('q') <span class="bookspace-form__error">{{ $message }}</span> @enderror
        </div>
    </section>

    <section class="bookspace-section bookspace-section--soft">
        <div class="container bookspace-container">
            @if (session('status'))
                <div class="bookspace-alert bookspace-alert--success">{{ session('status') }}</div>
            @endif
            @error('catalog')
                <div class="bookspace-alert bookspace-alert--error">{{ $message }}</div>
            @enderror
            @if ($serviceError)
                <div class="bookspace-alert bookspace-alert--error">{{ $serviceError }}</div>
            @endif

            <div class="bookspace-section-heading">
                <div>
                    <span class="bookspace-eyebrow bookspace-eyebrow--light">Resultados</span>
                    <h2>{{ $query !== '' ? 'Libros para “'.$query.'”' : 'Descubrí nuevas lecturas' }}</h2>
                </div>
            </div>

            @if (count($books) === 0 && ! $serviceError)
                <div class="bookspace-empty-state">No encontramos libros para esa búsqueda.</div>
            @else
                <div class="row g-4">
                    @foreach ($books as $book)
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <article class="bookspace-book-card">
                                <div class="bookspace-book-card__cover">
                                    @if ($book['thumbnail_url'])
                                        <img src="{{ $book['thumbnail_url'] }}" alt="Portada de {{ $book['title'] }}" loading="lazy">
                                    @else
                                        <div class="bookspace-book-card__placeholder">Sin portada</div>
                                    @endif
                                </div>
                                <div class="bookspace-book-card__body">
                                    <span class="bookspace-book-card__category">{{ $book['categories'][0] ?? 'Sin categoría' }}</span>
                                    <h3>{{ $book['title'] }}</h3>
                                    <p>{{ implode(', ', $book['authors']) }}</p>
                                    <small>{{ $book['published_date'] ?: 'Fecha no disponible' }}</small>

                                    @auth
                                        @if (in_array($book['external_id'], $savedExternalIds, true))
                                            <span class="bookspace-saved-label">Guardado</span>
                                        @else
                                            <form method="POST" action="{{ route('library.store', $book['external_id']) }}" class="bookspace-card-action">
                                                @csrf
                                                <button type="submit" class="btn bookspace-btn bookspace-btn--coral">Guardar libro</button>
                                            </form>
                                        @endif
                                    @else
                                        <a href="{{ route('login') }}" class="bookspace-card-link">Iniciá sesión para guardarlo</a>
                                    @endauth
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
