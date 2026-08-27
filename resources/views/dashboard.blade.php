<x-app-layout title="BookSpace | Dashboard">
    <section class="bookspace-dashboard-hero">
        <div class="container bookspace-container">
            <div class="bookspace-section-heading bookspace-section-heading--dashboard">
                <div>
                    <span class="bookspace-eyebrow">Dashboard BookSpace</span>
                    <h1>Dashboard de estadísticas</h1>
                    <p>Monitoreo visual preparado para acompañar el crecimiento futuro de BookSpace.</p>
                </div>
            </div>

            <div class="row g-4">
                @foreach ($stats as $stat)
                    <div class="col-sm-6 col-xl-3">
                        <article class="bookspace-stat-card">
                            <span class="bookspace-stat-card__icon">
                                @switch($stat['icon'])
                                    @case('bookmark')
                                        &#128278;
                                        @break
                                    @case('users')
                                        &#128101;
                                        @break
                                    @case('search')
                                        &#128269;
                                        @break
                                    @default
                                        &#127760;
                                @endswitch
                            </span>
                            <p class="bookspace-stat-card__title">{{ $stat['title'] }}</p>
                            <h2>{{ number_format($stat['value'], 0, ',', '.') }}</h2>
                            <p class="bookspace-stat-card__description">{{ $stat['description'] }}</p>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bookspace-section bookspace-section--soft bookspace-dashboard-section">
        <div class="container bookspace-container">
            <div
                class="row g-4"
                id="dashboard-charts"
                data-searches-by-month='@json($charts["searchesByMonth"])'
                data-saved-by-category='@json($charts["savedByCategory"])'
                data-user-activity='@json($charts["userActivity"])'
                data-consulted-distribution='@json($charts["consultedDistribution"])'
            >
                <div class="col-lg-6">
                    <article class="bookspace-chart-card">
                        <div class="bookspace-chart-card__heading">
                            <h3>Búsquedas realizadas por mes</h3>
                            <p>Evolución simulada de consultas dentro de la plataforma.</p>
                        </div>
                        <canvas id="searchesChart" height="280"></canvas>
                    </article>
                </div>
                <div class="col-lg-6">
                    <article class="bookspace-chart-card">
                        <div class="bookspace-chart-card__heading">
                            <h3>Libros guardados por categoría</h3>
                            <p>Distribución inicial de bibliotecas personales por interés.</p>
                        </div>
                        <canvas id="savedChart" height="280"></canvas>
                    </article>
                </div>
                <div class="col-lg-6">
                    <article class="bookspace-chart-card">
                        <div class="bookspace-chart-card__heading">
                            <h3>Actividad de usuarios por mes</h3>
                            <p>Usuarios activos simulados con crecimiento sostenido.</p>
                        </div>
                        <canvas id="activityChart" height="280"></canvas>
                    </article>
                </div>
                <div class="col-lg-6">
                    <article class="bookspace-chart-card">
                        <div class="bookspace-chart-card__heading">
                            <h3>Distribución de libros consultados por categoría</h3>
                            <p>Áreas temáticas más consultadas en el escenario actual.</p>
                        </div>
                        <canvas id="distributionChart" height="280"></canvas>
                    </article>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
