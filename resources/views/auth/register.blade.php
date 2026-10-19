<x-app-layout title="BookSpace | Crear cuenta">
    <section class="bookspace-section">
        <div class="container bookspace-container">
            <div class="bookspace-auth-card">
                <span class="bookspace-eyebrow bookspace-eyebrow--light">Empezá tu biblioteca</span>
                <h1>Crear cuenta</h1>
                <p>Tu cuenta guardará libros e intereses para las futuras recomendaciones.</p>

                <form method="POST" action="{{ route('register.store') }}" class="bookspace-form">
                    @csrf
                    <label for="name">Nombre</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" class="form-control">
                    @error('name') <span class="bookspace-form__error">{{ $message }}</span> @enderror

                    <label for="email">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="form-control">
                    @error('email') <span class="bookspace-form__error">{{ $message }}</span> @enderror

                    <label for="password">Contraseña</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password" class="form-control">
                    @error('password') <span class="bookspace-form__error">{{ $message }}</span> @enderror

                    <label for="password_confirmation">Repetir contraseña</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="form-control">

                    <button type="submit" class="btn bookspace-btn bookspace-btn--coral">Crear cuenta</button>
                </form>

                <p class="bookspace-form__footer">¿Ya tenés cuenta? <a href="{{ route('login') }}">Iniciar sesión</a></p>
            </div>
        </div>
    </section>
</x-app-layout>
