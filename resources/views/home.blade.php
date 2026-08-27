<x-app-layout title="BookSpace | Inicio">
    <section class="bookspace-hero">
        <div class="container bookspace-container">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="bookspace-eyebrow">Plataforma para descubrir y organizar libros</span>
                    <h1 class="bookspace-hero__title">Tu próximo libro te espera.</h1>
                    <p class="bookspace-hero__lead">
                        Descubrí libros, explorá nuevas historias y organizá tu biblioteca personal.
                    </p>

                    <form class="bookspace-search" action="javascript:void(0)">
                        <input type="search" class="form-control" placeholder="Buscar por título, autor, categoría o ISBN..." aria-label="Buscar libros">
                        <button type="submit" class="btn bookspace-btn bookspace-btn--coral">Buscar</button>
                    </form>

                    <div class="bookspace-hero__chips">
                        <div class="bookspace-chip">
                            <strong>Exploración guiada</strong>
                            <span>Una interfaz preparada para integrar Google Books más adelante.</span>
                        </div>
                        <div class="bookspace-chip">
                            <strong>Biblioteca personal</strong>
                            <span>Guardá títulos y organizalos según tus intereses.</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="bookspace-hero__panel">
                        <div class="bookspace-hero__panel-card">
                            <p>Explorá BookSpace</p>
                            <ul>
                                <li>Búsquedas por título, autor, categoría o ISBN</li>
                                <li>Portadas y datos bibliográficos simulados</li>
                                <li>Interfaz lista para futuras funcionalidades reales</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bookspace-section" id="explorar-libros">
        <div class="container bookspace-container">
            <div class="bookspace-section-heading">
                <div>
                    <span class="bookspace-eyebrow bookspace-eyebrow--light">Explorá BookSpace</span>
                    <h2>Un sistema pensado para descubrir, ordenar y volver a encontrar tus libros.</h2>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-xl-4">
                    <article class="bookspace-feature-card">
                        <div class="bookspace-feature-card__icon">01</div>
                        <h3>Descubrí libros</h3>
                        <p>Explorá títulos y autores utilizando información bibliográfica de Google Books.</p>
                    </article>
                </div>
                <div class="col-md-6 col-xl-4" id="mi-biblioteca">
                    <article class="bookspace-feature-card">
                        <div class="bookspace-feature-card__icon">02</div>
                        <h3>Organizá tu biblioteca</h3>
                        <p>Guardá los libros que te interesan y construí tu biblioteca personal.</p>
                    </article>
                </div>
                <div class="col-md-6 col-xl-4">
                    <article class="bookspace-feature-card">
                        <div class="bookspace-feature-card__icon">03</div>
                        <h3>Descubrí según tus intereses</h3>
                        <p>Explorá categorías y encontrá nuevas lecturas relacionadas con tus gustos.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="bookspace-section bookspace-section--soft">
        <div class="container bookspace-container">
            <div class="bookspace-section-heading">
                <div>
                    <span class="bookspace-eyebrow bookspace-eyebrow--light">Libros destacados</span>
                    <h2>Una vista previa de cómo se verá la experiencia de descubrimiento.</h2>
                </div>
                <a href="{{ url('/dashboard') }}" class="btn bookspace-btn bookspace-btn--ghost">Ver estadísticas</a>
            </div>

            <div class="row g-4">
                @foreach ($featuredBooks as $book)
                    <div class="col-sm-6 col-xl-3">
                        <article class="bookspace-book-card">
                            <div class="bookspace-book-card__cover">
                                <img src="{{ $book['cover'] }}" alt="{{ $book['title'] }} de {{ $book['author'] }}" loading="lazy">
                            </div>
                            <div class="bookspace-book-card__body">
                                <span class="bookspace-book-card__category">{{ $book['category'] }}</span>
                                <h3>{{ $book['title'] }}</h3>
                                <p>{{ $book['author'] }}</p>
                                <small>{{ $book['year'] }}</small>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-app-layout>

