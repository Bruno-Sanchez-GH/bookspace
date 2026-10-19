# BookSpace

BookSpace es una aplicación Laravel para descubrir libros mediante Google Books y guardar una biblioteca personal.

## Funcionalidades actuales

- Registro, inicio y cierre de sesión con cuentas locales.
- Catálogo público con búsqueda en Google Books.
- Biblioteca personal persistida en MySQL mediante Eloquent.
- Manejo visible de fallos y límites de la API externa.
- Dashboard inicial con datos demostrativos.

## Arquitectura

- **Backend:** PHP 8.3 y Laravel 13.
- **ORM:** Eloquent.
- **Base de datos:** MySQL en desarrollo; la configuración se obtiene de `.env`.
- **Frontend:** Blade, Bootstrap, CSS y JavaScript compilados con Vite.
- **API externa:** Google Books API.
- **Pruebas:** PHPUnit mediante `php artisan test`.

El catálogo consulta información remota. Cuando un usuario guarda un libro, Laravel obtiene su detalle, normaliza los campos y persiste un único registro en `books`. La relación muchos a muchos `book_user` permite que cada cuenta tenga su propia biblioteca sin duplicar el catálogo local.

## Instalación local

```powershell
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

Configurá en `.env` una base MySQL propia. Para evitar la cuota anónima de Google Books, agregá una clave habilitada para Books API:

```dotenv
GOOGLE_BOOKS_BASE_URL=https://www.googleapis.com/books/v1
GOOGLE_BOOKS_API_KEY=
```

Las credenciales y `.env` no se publican en Git.

## Rutas principales

- `/`: inicio.
- `/catalogo`: catálogo y búsqueda pública.
- `/registro`: alta de usuario.
- `/login`: inicio de sesión.
- `/mi-biblioteca`: libros guardados por el usuario autenticado.
- `/dashboard`: estadísticas demostrativas existentes.

## Verificación

```powershell
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run build
composer audit --locked
```

La correspondencia con las consignas está explicada en [ENTREGAS_TP4_TP5.md](ENTREGAS_TP4_TP5.md). La aplicación también se encuentra desplegada en cPanel; la automatización de futuras publicaciones queda como una mejora posterior.
