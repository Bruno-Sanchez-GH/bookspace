<x-app-layout title="BookSpace | Mi biblioteca">
    <section class="bookspace-dashboard-hero">
        <div class="container bookspace-container">
            <div class="bookspace-section-heading bookspace-section-heading--dashboard">
                <div>
                    <span class="bookspace-eyebrow">Tu espacio</span>
                    <h1>Mi biblioteca</h1>
                    <p>Los libros que guardes también ayudarán a construir tus intereses.</p>
                </div>
                <a href="{{ route('catalog.index') }}" class="btn bookspace-btn bookspace-btn--outline">Explorar catálogo</a>
            </div>
        </div>
    </section>

    <section class="bookspace-section bookspace-section--soft">
        <div class="container bookspace-container">
            @if (session('status'))
                <div class="bookspace-alert bookspace-alert--success">{{ session('status') }}</div>
            @endif

            @if ($books->isEmpty())
                <div class="bookspace-empty-state">
                    <h2>Tu biblioteca está vacía</h2>
                    <p>Explorá el catálogo y guardá tu primer libro.</p>
                    <a href="{{ route('catalog.index') }}" class="btn bookspace-btn bookspace-btn--coral">Buscar libros</a>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($books as $book)
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <article class="bookspace-book-card">
                                <div class="bookspace-book-card__cover">
                                    @if ($book->thumbnail_url)
                                        <img src="{{ $book->thumbnail_url }}" alt="Portada de {{ $book->title }}" loading="lazy">
                                    @else
                                        <div class="bookspace-book-card__placeholder">Sin portada</div>
                                    @endif
                                </div>
                                <div class="bookspace-book-card__body">
                                    <span class="bookspace-book-card__category">{{ $book->categories[0] ?? 'Sin categoría' }}</span>
                                    <h3>{{ $book->title }}</h3>
                                    <p>{{ implode(', ', $book->authors) }}</p>
                                    <small>{{ $book->published_date ?: 'Fecha no disponible' }}</small>
                                    <form method="POST" action="{{ route('library.destroy', $book) }}" class="bookspace-card-action">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn bookspace-btn bookspace-btn--ghost">Quitar</button>
                                    </form>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
