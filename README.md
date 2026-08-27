# BookSpace

BookSpace es una aplicación web construida con Laravel para descubrir, explorar y organizar libros. Esta versión incluye una home principal de la aplicación y un dashboard de estadísticas con datos simulados, preparada para continuar el desarrollo local y para integrarse más adelante con Google Books y una base de datos real.

## Tecnologías utilizadas

- Laravel 13
- PHP 8.2 o superior
- Vite
- Bootstrap 5
- Chart.js
- Blade
- CSS personalizado
- Node.js y npm para assets frontend

## Requisitos

- PHP 8.2 o superior
- Composer
- Node.js 20 o superior
- npm
- Extensiones PHP habituales para Laravel

## Instalación local

1. Instalar dependencias PHP:

```bash
composer install
```

2. Instalar dependencias frontend:

```bash
npm install
```

3. Crear el archivo de entorno local a partir del ejemplo:

```bash
cp .env.example .env
```

En Windows PowerShell podés usar:

```powershell
Copy-Item .env.example .env
```

4. Generar la clave de la aplicación:

```bash
php artisan key:generate
```

5. Iniciar el entorno de desarrollo frontend:

```bash
npm run dev
```

6. Levantar el servidor local de Laravel:

```bash
php artisan serve
```

## Build de producción

Para generar assets compilados:

```bash
npm run build
```

Los assets generados quedan en `public/build` y esta carpeta se conserva para entornos de producción.

## Rutas principales

- `/` Home principal de BookSpace
- `/dashboard` Dashboard de estadísticas

## Estructura general del proyecto

- `app/Http/Controllers/`
  Controladores como `HomeController` y `DashboardController`.
- `resources/views/`
  Vistas Blade del sistema, layouts compartidos, navbar y footer globales.
- `resources/css/` y `resources/js/`
  Estilos y scripts frontend gestionados con Vite.
- `routes/web.php`
  Rutas web principales.
- `public/build/`
  Assets compilados listos para producción.
- `storage/`
  Archivos temporales, logs, cachés y vistas compiladas.

## Datos y entorno

- La aplicación actual usa datos simulados para estadísticas y contenidos destacados.
- No requiere todavía base de datos real para las funcionalidades visibles.
- Las credenciales reales deben configurarse únicamente desde `.env`.
- Para producción, usar `.env.production.example` como referencia segura.

## Próximos pasos de desarrollo

- Integración con Google Books API
- Biblioteca personal persistente
- Autenticación de usuarios
- Persistencia real con base de datos

## Despliegue

La documentación de despliegue quedó separada en `DEPLOY_CPANEL.md` para mantener diferenciados el flujo de desarrollo local y la configuración de producción en cPanel.
