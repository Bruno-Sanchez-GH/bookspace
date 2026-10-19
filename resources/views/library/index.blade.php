<x-app-layout title="BookSpace | Mi biblioteca">
    <section class="bookspace-dashboard-hero">
        <div class="container bookspace-container">
            <div class="bookspace-section-heading bookspace-section-heading--dashboard">
                <div>
                    <span class="bookspace-eyebrow">Tu espacio</span>
                    <h1>Mi biblioteca</h1>
                    <p>Organizá tus libros y registrá por qué página vas.</p>
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

            @if ($errors->any())
                <div class="bookspace-alert bookspace-alert--error">
                    <strong>No pudimos actualizar el seguimiento.</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($books->isNotEmpty())
                <section class="bookspace-ai-panel" aria-labelledby="bookspace-ai-title">
                    <div class="bookspace-ai-panel__heading">
                        <div>
                            <span class="bookspace-eyebrow bookspace-eyebrow--light">Con Gemini</span>
                            <h2 id="bookspace-ai-title">Recomendaciones para tu próxima lectura</h2>
                            <p>Gemini compara tu biblioteca con libros reales de Google Books para elegir hasta tres opciones.</p>
                        </div>

                        <form method="POST" action="{{ route('recommendations.store') }}">
                            @csrf
                            <button type="submit" class="btn bookspace-btn bookspace-btn--coral">Generar recomendaciones</button>
                        </form>
                    </div>

                    <small class="bookspace-ai-panel__privacy">Se envían títulos, autores, categorías y progreso de lectura; nunca tu nombre ni tu correo.</small>

                    @if ($errors->recommendations->any())
                        <div class="bookspace-alert bookspace-alert--error bookspace-ai-panel__alert">
                            @foreach ($errors->recommendations->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if (session('bookRecommendations'))
                        <div class="bookspace-ai-panel__results">
                            <p class="bookspace-ai-panel__summary">{{ session('bookRecommendations.summary') }}</p>

                            <div class="row g-4">
                                @foreach (session('bookRecommendations.books', []) as $recommendedBook)
                                    <div class="col-md-6 col-xl-4">
                                        <article class="bookspace-book-card bookspace-book-card--recommendation">
                                            <div class="bookspace-book-card__cover">
                                                @if ($recommendedBook['thumbnail_url'])
                                                    <img src="{{ $recommendedBook['thumbnail_url'] }}" alt="Portada de {{ $recommendedBook['title'] }}" loading="lazy">
                                                @else
                                                    <div class="bookspace-book-card__placeholder">Sin portada</div>
                                                @endif
                                            </div>
                                            <div class="bookspace-book-card__body">
                                                <span class="bookspace-book-card__category">{{ $recommendedBook['categories'][0] ?? 'Sin categoría' }}</span>
                                                <h3>{{ $recommendedBook['title'] }}</h3>
                                                <p>{{ implode(', ', $recommendedBook['authors']) }}</p>
                                                <p class="bookspace-recommendation-reason">{{ $recommendedBook['reason'] }}</p>

                                                <form method="POST" action="{{ route('library.store', $recommendedBook['external_id']) }}" class="bookspace-card-action">
                                                    @csrf
                                                    <button type="submit" class="btn bookspace-btn bookspace-btn--coral">Guardar en mi biblioteca</button>
                                                </form>
                                            </div>
                                        </article>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
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
                        <div class="col-md-6 col-xl-4">
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

                                    <div class="bookspace-reading-progress">
                                        <div class="bookspace-reading-progress__summary">
                                            <strong>{{ $book->page_count ? "{$book->pivot->current_page} de {$book->page_count} páginas" : "{$book->pivot->current_page} páginas leídas" }}</strong>
                                            @if ($progressPercentages[$book->id] !== null)
                                                <span>{{ $progressPercentages[$book->id] }}%</span>
                                            @endif
                                        </div>

                                        @if ($progressPercentages[$book->id] !== null)
                                            <div
                                                class="bookspace-reading-progress__track"
                                                role="progressbar"
                                                aria-label="Avance de {{ $book->title }}"
                                                aria-valuenow="{{ $progressPercentages[$book->id] }}"
                                                aria-valuemin="0"
                                                aria-valuemax="100"
                                            >
                                                <span style="width: {{ $progressPercentages[$book->id] }}%"></span>
                                            </div>
                                        @endif
                                    </div>

                                    <form method="POST" action="{{ route('library.update', $book) }}" class="bookspace-progress-form">
                                        @csrf
                                        @method('PATCH')

                                        <div>
                                            <label for="reading-status-{{ $book->id }}">Estado</label>
                                            <select id="reading-status-{{ $book->id }}" name="reading_status" class="form-select">
                                                @foreach ($readingStatuses as $readingStatus)
                                                    <option value="{{ $readingStatus->value }}" @selected($book->pivot->reading_status === $readingStatus->value)>
                                                        {{ $readingStatus->label() }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label for="current-page-{{ $book->id }}">Página actual</label>
                                            <input
                                                id="current-page-{{ $book->id }}"
                                                name="current_page"
                                                type="number"
                                                min="0"
                                                @if ($book->page_count) max="{{ $book->page_count }}" @endif
                                                value="{{ $book->pivot->current_page }}"
                                                class="form-control"
                                                required
                                            >
                                        </div>

                                        <button type="submit" class="btn bookspace-btn bookspace-btn--coral">Guardar avance</button>
                                    </form>

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
