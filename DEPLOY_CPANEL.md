# Deploy BookSpace en cPanel

Este archivo documenta el despliegue de producción y, en particular, la adaptación que se usó en el servidor para separar `public_html` del código Laravel.

## Diferencia clave entre desarrollo y cPanel

En esta copia local, `public/index.php` fue restaurado al formato estándar de Laravel:

- `../storage/framework/maintenance.php`
- `../vendor/autoload.php`
- `../bootstrap/app.php`

En el servidor cPanel, `public_html` estaba configurado para cargar Laravel desde una carpeta hermana llamada `bookspace_laravel`. Eso implicó cambiar manualmente `public/index.php` para apuntar a:

- `../bookspace_laravel/storage/framework/maintenance.php`
- `../bookspace_laravel/vendor/autoload.php`
- `../bookspace_laravel/bootstrap/app.php`

Si en un futuro volvés a desplegar con esa misma estructura de carpetas, ese ajuste debe repetirse en el `public/index.php` del servidor, no en la copia local de desarrollo.

## Antes de comprimir

Ejecutar en local:

```powershell
php artisan test --filter=BookSpacePagesTest
npm run build
```

## Incluir en el ZIP

- `app/`
- `bootstrap/`
- `config/`
- `database/`
- `public/`
- `resources/`
- `routes/`
- `storage/`
- `vendor/`
- `artisan`
- `composer.json`
- `composer.lock`
- `package.json`
- `package-lock.json`
- `.env.production.example` como referencia

## Excluir del ZIP

- `.env`
- `node_modules/`
- `tests/`
- `.git/`
- `.phpunit.result.cache`
- `storage/logs/*.log`
- `storage/framework/views/*.php`
- `storage/framework/cache/data/*`
- `storage/framework/sessions/*`
- `storage/framework/testing/*`
- `public/hot`
- archivos de IDE como `.vscode/`, `.idea/`

## Configuración manual en cPanel

1. Crear el `.env` de producción a partir de `.env.production.example`.
2. Completar `APP_KEY` con una clave real generada para producción.
3. Completar `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
4. Verificar que el dominio o subdominio apunte a la carpeta pública correcta.
5. Si usás la estructura separada de cPanel:
   ajustar `public/index.php` para que cargue la carpeta hermana `bookspace_laravel`.
6. Ejecutar en el servidor:

```bash
php artisan key:generate
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

7. Si más adelante se usa base de datos real para más componentes:

```bash
php artisan migrate --force
```

## Notas

- `public/build/` debe subirse tal como quedó compilado localmente.
- La app usa Bootstrap y Chart.js desde Vite, por eso no necesita `npm run dev` en producción.
- El ejemplo de producción usa `SESSION_DRIVER=file` y `CACHE_STORE=file` para evitar depender de tablas extra en un deploy inicial de cPanel.
