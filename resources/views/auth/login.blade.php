<x-app-layout title="BookSpace | Iniciar sesión">
    <section class="bookspace-section">
        <div class="container bookspace-container">
            <div class="bookspace-auth-card">
                <span class="bookspace-eyebrow bookspace-eyebrow--light">Tu cuenta</span>
                <h1>Iniciar sesión</h1>
                <p>Accedé para guardar libros y construir tus intereses.</p>

                <form method="POST" action="{{ route('login.store') }}" class="bookspace-form">
                    @csrf
                    <label for="email">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="form-control">
                    @error('email') <span class="bookspace-form__error">{{ $message }}</span> @enderror

                    <label for="password">Contraseña</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="form-control">
                    @error('password') <span class="bookspace-form__error">{{ $message }}</span> @enderror

                    <label class="bookspace-checkbox">
                        <input name="remember" type="checkbox" value="1">
                        Recordarme
                    </label>

                    <button type="submit" class="btn bookspace-btn bookspace-btn--coral">Entrar</button>
                </form>

                <p class="bookspace-form__footer">¿Todavía no tenés cuenta? <a href="{{ route('register') }}">Crear cuenta</a></p>
            </div>
        </div>
    </section>
</x-app-layout>
