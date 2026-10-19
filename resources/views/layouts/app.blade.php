<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'BookSpace' }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bookspace-body">
        <div class="bookspace-app-shell">
            <nav class="navbar navbar-expand-lg bookspace-navbar">
                <div class="container bookspace-container">
                    <a class="navbar-brand bookspace-brand" href="{{ url('/') }}">Book<span>Space</span></a>
                    <button class="navbar-toggler bookspace-navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#bookspaceNavbar" aria-controls="bookspaceNavbar" aria-expanded="false" aria-label="Abrir navegación">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="bookspaceNavbar">
                        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2 bookspace-navbar__menu">
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ url('/') }}">Inicio</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('catalog.*') ? 'active' : '' }}" href="{{ route('catalog.index') }}">Explorar libros</a></li>
                            @auth
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('library.*') ? 'active' : '' }}" href="{{ route('library.index') }}">Mi biblioteca</a></li>
                            @endauth
                            <li class="nav-item"><a class="nav-link bookspace-nav-pill {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ url('/dashboard') }}">Estadísticas</a></li>
                        </ul>
                        <div class="bookspace-navbar__actions">
                            @guest
                                <a class="bookspace-auth-link" href="{{ route('login') }}">Iniciar sesión</a>
                                <a class="btn bookspace-btn bookspace-btn--outline" href="{{ route('register') }}">Crear cuenta</a>
                            @else
                                <span class="bookspace-user-name">{{ auth()->user()->name }}</span>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn bookspace-btn bookspace-btn--outline">Cerrar sesión</button>
                                </form>
                            @endguest
                        </div>
                    </div>
                </div>
            </nav>

            <main>
                {{ $slot }}
            </main>

            <footer class="bookspace-footer">
                <div class="container bookspace-container">
                    <div class="bookspace-footer__grid">
                        <div class="bookspace-footer__brand-block">
                            <a class="bookspace-brand bookspace-brand--footer" href="{{ url('/') }}">Book<span>Space</span></a>
                            <p>Descubrí, explorá y organizá tus próximas lecturas.</p>
                            <small>Catálogo conectado con Google Books y biblioteca personal persistente.</small>
                        </div>
                        <div class="bookspace-footer__links">
                            <span class="bookspace-footer__title">Navegación</span>
                            <a href="{{ url('/') }}">Inicio</a>
                            <a href="{{ route('catalog.index') }}">Explorar</a>
                            @auth
                                <a href="{{ route('library.index') }}">Mi biblioteca</a>
                            @endauth
                            <a href="{{ url('/dashboard') }}">Estadísticas</a>
                        </div>
                        <div class="bookspace-footer__meta">
                            <span class="bookspace-footer__title">BookSpace</span>
                            <p>Sistema visual unificado para descubrir libros, organizar lecturas y revisar estadísticas.</p>
                            <small>Copyright BookSpace 2026</small>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
