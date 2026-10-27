# Plan de pruebas - Tablón de anuncios

## Cómo se han generado

Los casos se generaron con ayuda de una IA (Claude) usando un prompt con el contexto concreto de la aplicación: reglas de permisos, validaciones, CSRF y lista blanca de acciones. Después los revisé y los ejecuté manualmente uno a uno, anotando lo que ocurrió realmente. Para los casos que modifican el HTML (quitar `required`, cambiar campos ocultos, quitar el token CSRF) usé las herramientas de desarrollador del navegador (F12).

## Entorno

- Docker Compose: PHP 8.3 + Apache, MySQL 8.0, phpMyAdmin
- URL: http://localhost:8080
- Fecha de ejecución: 27/10/2026
- Usuarios de prueba: A (usuario con el que se crean los anuncios) y B (segundo usuario, en otra sesión/navegador)

## Prompt utilizado

```
Actúa como tester de software. Genera casos de prueba manuales para una
aplicación web PHP (tablón de anuncios de clase) con estas características:

- Usuarios: registro (email único, contraseña mínimo 6 caracteres, guardada
  con password_hash), login y logout. Mensaje genérico si falla el login.
- Anuncios: título (máx. 200 caracteres), cuerpo y categoría (lista cerrada:
  Venta, Búsqueda, Objetos perdidos, Proyectos, Otros). Solo usuarios
  logueados pueden crear. Solo el autor puede editar o borrar (403 si no es
  suyo, 404 si no existe).
- Comentarios: solo usuarios logueados, máx. 1000 caracteres, el user_id sale
  de la sesión. Al borrar un anuncio se borran sus comentarios.
- Buscador: filtra por título o categoría con LIKE.
- Seguridad: todos los formularios POST llevan token CSRF (403 si falta o es
  incorrecto), consultas preparadas, htmlspecialchars en las vistas.
- Las acciones están en una lista blanca (404 si no existe).

Genera unos 35 casos agrupados por funcionalidad (usuarios, anuncios,
autorización, comentarios, búsqueda, seguridad). Incluye casos normales,
casos límite (campos vacíos, longitudes máximas, caracteres especiales,
espacios) y casos de error o ataque (XSS, inyección SQL, CSRF, manipulación
de campos ocultos, acceso sin sesión, acceso a recursos ajenos).

Formato: tabla en Markdown con las columnas: ID, Descripción, Pasos,
Resultado esperado. Deja vacías las columnas "Resultado obtenido" y
"Estado (Pasa/Falla)".
```

## Resumen

- Total de casos: 53
- Pasan: 53 (B6 pasa con una limitación conocida, ver "Observaciones")
- Fallan: 0

| Sección | Casos | Pasan |
|---------|-------|-------|
| Usuarios | 10 | 10 |
| Anuncios | 9 | 9 |
| Autorización | 7 | 7 |
| Comentarios | 10 | 10 |
| Búsqueda | 6 | 6 |
| Seguridad | 11 | 11 |

## Casos de prueba

### 1. Usuarios

| ID | Descripción | Pasos | Resultado esperado | Resultado obtenido | Estado |
|----|-------------|-------|--------------------|--------------------|--------|
| U1 | Registro válido | Registrarse con email nuevo, nombre y contraseña de 8 caracteres | Redirige al login con mensaje verde | Registro correcto y redirección al login | Pasa |
| U2 | Email duplicado | Registrarse otra vez con el email de U1 | "Ese email ya está registrado" | Muestra error de email duplicado | Pasa |
| U3 | Contraseña de 5 caracteres | Quitar `minlength` con F12 y registrar con `12345` | Error de contraseña mínima | Muestra error por el límite inferior | Pasa |
| U4 | Contraseña de 6 caracteres | Registrarse con `123456` | Registro correcto | Registro correcto | Pasa |
| U5 | Email con formato inválido | Cambiar `type="email"` a `text` y enviar `abc` | "El email no es válido" | Muestra error de email no válido | Pasa |
| U6 | Campos vacíos o solo espacios | Quitar `required` y enviar nombre con espacios | Mensaje de error, no se crea el usuario | Pide completar los campos | Pasa |
| U7 | Login correcto | Entrar con email y contraseña de A | Cabecera con email y "Cerrar sesión" | Login correcto | Pasa |
| U8 | Login fallido | Contraseña incorrecta y email inexistente, por separado | Mismo mensaje genérico en ambos | Muestra "Email o contraseña incorrectos" | Pasa |
| U9 | Logout | Pulsar "Cerrar sesión" | Vuelve a "Iniciar sesión" y "Registrarse" | Funciona | Pasa |
| U10 | Contraseña con hash | Mirar `users.password` en phpMyAdmin | Hash `$2y$10$...` | Hash correcto, sin texto plano | Pasa |

### 2. Anuncios

| ID | Descripción | Pasos | Resultado esperado | Resultado obtenido | Estado |
|----|-------------|-------|--------------------|--------------------|--------|
| A1 | Crear anuncio válido | Con A: título, categoría y contenido válidos | Detalle con mensaje "Publicación creada" | Se muestra el detalle con el mensaje | Pasa |
| A2 | Título de 200 caracteres | Crear con un título de 200 caracteres | Se crea correctamente | Se crea correctamente | Pasa |
| A3 | Título de 201 caracteres | Quitar `maxlength` con F12 y enviar 201 caracteres | Error de longitud; el formulario conserva lo escrito | Muestra el error y conserva los datos | Pasa |
| A4 | Título solo espacios | Quitar `required` y enviar espacios | Error de campos obligatorios | Muestra el error | Pasa |
| A5 | Contenido vacío | Quitar `required` y enviar el contenido vacío | Error de campos obligatorios | Muestra el error | Pasa |
| A6 | Categoría inventada | Cambiar el `value` de una opción con F12 | "Selecciona una categoría válida" | Muestra el error | Pasa |
| A7 | Caracteres especiales | Título `Ñandú: "café" & 'té' 😀` | Se guarda y se muestra tal cual | Se muestra correctamente (conexión `utf8mb4`) | Pasa |
| A8 | Editar anuncio propio | Con A, editar título y categoría | Cambios guardados | Cambios guardados | Pasa |
| A9 | Borrar anuncio propio | Con A, pulsar Borrar y aceptar | Desaparece del listado | Desaparece del listado | Pasa |

### 3. Autorización

| ID | Descripción | Pasos | Resultado esperado | Resultado obtenido | Estado |
|----|-------------|-------|--------------------|--------------------|--------|
| AU1 | Crear sin sesión (GET) | Sin loguear, abrir `?controller=Post&action=create` escribiendo la URL | Redirige al login con mensaje | Redirige al login | Pasa |
| AU2 | Editar sin sesión | Sin loguear, abrir `?controller=Post&action=edit&id=1` escribiendo la URL | Redirige al login | Redirige al login | Pasa |
| AU3 | Botones ocultos a no autores | Con B, abrir un anuncio de A | No aparecen Editar ni Borrar | No aparecen | Pasa |
| AU4 | Editar ajeno por URL | Con B, abrir `edit&id=ID_DE_A` | Error 403 | Error 403 | Pasa |
| AU5 | Actualizar ajeno | Con B, cambiar el `id` oculto del formulario de edición por el de A y guardar | Error 403, el anuncio de A no cambia | Error 403 "No tienes permiso para hacer esto." | Pasa |
| AU6 | Borrar ajeno | Con B, cambiar el `id` oculto del formulario de borrar por el de A y enviar | Error 403, el anuncio sigue existiendo | Error 403, el anuncio sigue existiendo | Pasa |
| AU7 | Anuncio inexistente | `show&id=9999` y `edit&id=9999` | Error 404 en ambos | Error 404 en ambos | Pasa |

### 4. Comentarios

| ID | Descripción | Pasos | Resultado esperado | Resultado obtenido | Estado |
|----|-------------|-------|--------------------|--------------------|--------|
| C1 | Comentario válido | Con A, comentar en un anuncio | Aparece con su nombre y fecha; el contador sube | Aparece correctamente y el contador sube | Pasa |
| C2 | Sin sesión | Abrir un anuncio sin loguear | Sin formulario, con enlace para iniciar sesión | Sin formulario y con el enlace | Pasa |
| C3 | Orden cronológico | Con B, comentar en el mismo anuncio | El de B aparece después del de A | Orden correcto | Pasa |
| C4 | Vacío o solo espacios | Quitar `required`, enviar solo espacios | Mensaje de error, no se guarda | Muestra el mensaje de error esperado | Pasa |
| C5 | 1000 caracteres | Pegar un texto de 1000 caracteres | Se guarda | Se guarda correctamente | Pasa |
| C6 | 1001 caracteres | Quitar `maxlength`, enviar 1001 caracteres | Mensaje de error, no se guarda | Muestra el mensaje de error por exceso de caracteres | Pasa |
| C7 | `post_id` inexistente | Cambiar `post_id` a `9999` y pulsar Comentar | Error 404 | Error 404 "La publicación no existe." | Pasa |
| C8 | `user_id` manipulado | Añadir un campo `user_id` con F12 y enviar | El comentario se guarda con el usuario de la sesión | Se guarda a nombre del usuario logueado | Pasa |
| C9 | Saltos de línea | Comentar con dos líneas | Se muestran en dos líneas | Se muestran en varias líneas | Pasa |
| C10 | Borrado en cascada | Con A, crear anuncio con comentarios, borrarlo con el botón Borrar y mirar `comments` en phpMyAdmin | Los comentarios del anuncio desaparecen | Al borrar el anuncio desde la web, sus comentarios desaparecen de la tabla | Pasa |

### 5. Búsqueda

| ID | Descripción | Pasos | Resultado esperado | Resultado obtenido | Estado |
|----|-------------|-------|--------------------|--------------------|--------|
| B1 | Por parte del título | Buscar un fragmento de un título | Solo salen los que coinciden | Solo salen los anuncios que coinciden | Pasa |
| B2 | Por categoría | Buscar `Venta` | Salen los de esa categoría | Salen los anuncios de esa categoría | Pasa |
| B3 | Sin resultados | Buscar `zzzzzz` | "No hay anuncios que coincidan con tu búsqueda" | Muestra ese mensaje | Pasa |
| B4 | Búsqueda vacía | Enviar el buscador sin texto | Se listan todos | Se listan todos | Pasa |
| B5 | Mayúsculas y minúsculas | Buscar `venta` en minúsculas | Coincide con `Venta` | Coincide (la collation de MySQL no distingue mayúsculas) | Pasa |
| B6 | Comodines `%` y `_` | Buscar `%` | Devuelve todos los anuncios | Devuelve todos los anuncios | Pasa (con limitación, ver Observaciones) |

### 6. Seguridad

| ID | Descripción | Pasos | Resultado esperado | Resultado obtenido | Estado |
|----|-------------|-------|--------------------|--------------------|--------|
| S1 | XSS en comentario | Comentar `<script>alert(1)</script>` | Se muestra como texto, no se ejecuta | Se ve literalmente el texto, sin alerta | Pasa |
| S2 | XSS en título | Crear anuncio con título `<b>hola</b><img src=x onerror=alert(1)>` | Se ve literal, sin negrita ni alerta | Se crea y se muestra literal | Pasa |
| S3 | XSS en nombre de usuario | Registrar usuario `<script>alert(1)</script>`, publicar y comentar | El nombre se ve como texto en listado y comentarios | El anuncio y el comentario muestran el nombre como texto | Pasa |
| S4 | SQL en búsqueda | Buscar `' OR 1=1 --` | Sin errores y sin resultados | "No hay anuncios que coincidan con tu búsqueda" | Pasa |
| S5 | SQL en login | Cambiar `type="email"` a `text` y entrar con `' OR '1'='1` | Mensaje genérico de login incorrecto | "Email o contraseña incorrectos" | Pasa |
| S6 | CSRF: token ausente | Borrar `csrf_token` con F12 y enviar | 403 "Token de seguridad no válido..." | 403 con el mensaje del token | Pasa |
| S7 | CSRF: token falso | Cambiar el `value` del token por `abc` y enviar | 403 | 403 con el mismo mensaje | Pasa |
| S8 | CSRF: ataque desde otra web | `ataque.html` en el mismo navegador con la sesión abierta, apuntando a `destroy` con el id de un anuncio propio | 403 y el anuncio no se borra | 403 con el mensaje del token | Pasa |
| S9 | Acción POST por GET | Abrir `?controller=Post&action=destroy&id=ID_PROPIO` escribiendo la URL | 403 y no se borra nada | 403 con el mensaje del token | Pasa |
| S10 | Acción fuera de la lista blanca | Abrir `?controller=Post&action=loquesea` | "Página no encontrada" (404) | "Página no encontrada" | Pasa |
| S11 | Controlador inexistente | Abrir `?controller=Admin&action=index` | "Página no encontrada" (404) | "Página no encontrada" | Pasa |

## Observaciones y limitaciones conocidas

- **B6 (comodines de LIKE):** al buscar `%` se devuelven todos los anuncios, porque `%` y `_` son comodines de `LIKE` y la aplicación no los escapa. No es una vulnerabilidad: la consulta es preparada, así que no hay inyección SQL. Es un comportamiento menor que se deja como limitación conocida. Si se quisiera corregir, bastaría con escapar el término con `addcslashes($term, '%_\\')` en `Post::search()`.
- **AU1 y AU2:** en un primer intento comprobé que la interfaz no mostraba los botones, pero eso no prueba la protección del servidor. Se repitieron escribiendo la URL a mano, que es lo que haría un atacante.
- **AU5, AU6 y S6 a S9:** distinguí los dos tipos de 403 por su texto. "No tienes permiso para hacer esto." viene de la autorización (`findOwnedPost()`). "Token de seguridad no válido..." viene del CSRF (`verifyCsrf()`). En S8 y S9 usé anuncios propios para asegurarme de que el 403 se debía al CSRF y no a la autorización.

## Fallos encontrados y correcciones

| Caso | Qué falló | Cómo se corrigió |
|------|-----------|------------------|
| (durante el desarrollo) | `Class "App\Models\Post" not found`: el archivo se llamaba `Posts.php` y la clase `Post` | Se renombró a `Post.php`. PSR-4 exige que nombre de archivo, clase y carpeta coincidan |
| (durante el desarrollo) | `AuthController.php` y `Controller.php` guardados en `src/Models/` | Se movieron a `src/Controllers/` y `src/Core/`, según su namespace |
| (durante el desarrollo) | `src` montado en `/var/www/html/src` en Docker, donde Composer no lo encontraba | Se montó en `/var/www/src` |

En las pruebas manuales no se encontró ningún fallo funcional ni de seguridad.

## Conclusiones

- Los casos más reveladores fueron los de **autorización y CSRF**, porque no se pueden probar con la interfaz normal: hay que manipular el HTML con F12 o forzar URLs. Ocultar un botón no protege nada; la protección real está en el servidor.
- Aprendí a distinguir **autenticación** (`requireLogin()`: saber quién eres) de **autorización** (`findOwnedPost()`: qué puedes hacer), y a reconocer cuál de las dos actúa mirando el texto del 403.
- Las consultas preparadas y `htmlspecialchars()` resolvieron todos los casos de inyección SQL y XSS sin código extra en cada pantalla, y el `user_id` tomado de `$_SESSION` evita la suplantación de autor.