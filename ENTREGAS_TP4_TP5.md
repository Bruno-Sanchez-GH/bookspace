# BookSpace: memoria técnica de TP4 y TP5

Estado al 19 de octubre de 2026.

## Alcance desarrollado

La aplicación permite crear una cuenta local, iniciar sesión, buscar libros en Google Books y guardar una selección en una biblioteca personal. El catálogo remoto es visible sin autenticación; guardar y quitar libros exige una sesión iniciada.

## Entrega 4: backend y ORM

La implementación coincide con la consigna de definir un backend, elegir un ORM y comunicar el sistema con una base de datos por medio del ORM.

| Elemento | Elección | Uso concreto |
|---|---|---|
| Backend y framework | PHP 8.3 + Laravel 13 | Rutas, controladores, validación, sesiones y coordinación de la aplicación |
| ORM | Eloquent | Modelos `User` y `Book`, consultas y relación de biblioteca |
| Base de datos | MySQL local | Usuarios, libros normalizados y relación de libros guardados |
| Frontend | Blade + Bootstrap + CSS/JS | Formularios de acceso, catálogo y biblioteca |

### Modelo de datos

- `users`: cuentas locales; Laravel cifra las contraseñas y mantiene la sesión.
- `books`: copia normalizada de cada volumen guardado. `external_id` identifica el volumen de Google y es único.
- `book_user`: relación muchos a muchos entre usuarios y libros. La combinación de ambos identificadores es única.

El catálogo se consulta en vivo y no se copia completo. La persistencia ocurre cuando alguien guarda un libro. `Book::updateOrCreate()` mantiene actualizados sus metadatos y `syncWithoutDetaching()` evita duplicar la relación de la biblioteca.

### Flujo demostrable de TP4

1. El usuario se registra o inicia sesión.
2. Busca un libro en `/catalogo`.
3. Pulsa **Guardar en mi biblioteca**.
4. Laravel valida el identificador, consulta el detalle y lo guarda mediante Eloquent.
5. `/mi-biblioteca` recupera la relación desde MySQL; el usuario puede quitarla sin borrar el libro compartido.

Archivos principales: `app/Models/Book.php`, `app/Models/User.php`, `app/Http/Controllers/SavedBookController.php` y las migraciones de `books` y `book_user`.

## Entrega 5: API de Google Books

**Proveedor:** Google Books API

**URL base:** `https://www.googleapis.com/books/v1`

**Objetivo:** obtener títulos, autores, sinopsis, fecha, ISBN, categorías, cantidad de páginas, portada y enlace informativo sin cargar manualmente un catálogo.

### Endpoints usados

| Endpoint | Funcionalidad en BookSpace |
|---|---|
| `GET /volumes?q={consulta}&maxResults=12&orderBy=relevance&printType=books` | Buscar libros para el catálogo |
| `GET /volumes/{volumeId}` | Recuperar el detalle exacto antes de guardarlo |

La integración está encapsulada en `app/Services/GoogleBooksService.php`. Usa tiempo máximo de conexión, tiempo máximo total, reintentos y conversión a una estructura interna estable. La clave se lee de `GOOGLE_BOOKS_API_KEY`; nunca se escribe en el repositorio. Si Google no responde o rechaza la cuota, el controlador conserva la página y muestra un mensaje comprensible.

La verificación en producción devolvió resultados reales mediante la página del catálogo. Books API está habilitada en Google Cloud y la clave se encuentra restringida a ese servicio. La credencial se guarda únicamente en el `.env` de producción.

## Autenticación elegida

El login actual usa autenticación de sesión de Laravel y cuentas almacenadas localmente. Es la opción directa para una aplicación Blade y permite relacionar intereses y biblioteca con un usuario. No cuenta como la API de TP5: la API documentada para esa entrega es Google Books.

Google OAuth puede incorporarse después como segundo método de acceso. Seguiría siendo necesario conservar un usuario local para asociar libros, preferencias y notificaciones.

## Pruebas y evidencia

- Registro válido y rechazo de correo duplicado.
- Login correcto, credenciales inválidas y logout.
- Búsqueda y normalización de resultados simulando Google Books.
- Mensaje controlado ante un error HTTP 503 del proveedor.
- Restricción de biblioteca a usuarios autenticados.
- Persistencia del libro y de la relación; eliminación sólo de la relación personal.
- Resultado actual: 11 pruebas, 47 aserciones.
- Migraciones `books` y `book_user` aplicadas en MySQL local.
- Build de producción generado correctamente.
- Auditoría de Composer sin vulnerabilidades conocidas después de actualizar dependencias compatibles.

## Estado y próximas decisiones

TP4 tiene una funcionalidad real que usa Laravel, Eloquent y MySQL. TP5A cuenta con integración, documentación, clave restringida y consultas reales verificadas con Google Books. Ambos flujos están desplegados en cPanel.

Las recomendaciones, el historial de intereses, las notificaciones y la estantería mensual 3D todavía no están implementados. Antes de desarrollarlos hay que decidir qué acción expresa interés (ver, guardar, marcar o descartar), qué canal usar para las notificaciones y cómo limitar la selección mensual. La automatización con IA de TP5+ se documentará e implementará como etapa separada para que su aporte sea demostrable.

El despliegue actual en cPanel se realizó manualmente y quedó verificado. La automatización con GitHub Actions se evaluará como una mejora posterior porque todavía debe confirmarse un método de acceso remoto compatible con el hosting.
