# Biblioteca 04 · Tarea 5

Sistema de biblioteca en línea para la Tarea 5 de Programación Web Avanzada (UBE).
Catálogo, préstamos y devoluciones con tres roles: Administrador, Bibliotecario y Lector.

- Sitio publicado: https://pwtarea5.infinityfreeapp.com
- Repositorio: pendiente de publicar en GitHub
- Grupo 04

## Tecnologías

HTML5, Bootstrap 5 (retícula y menú plegable), CSS propio, JavaScript sin librerías,
PHP 8 con PDO y MySQL / MariaDB. Las tipografías y las fotografías se guardan en el
propio proyecto.

## Roles y permisos

| Rol | Puede hacer |
| --- | --- |
| Administrador | Crear y eliminar cuentas, asignar roles y consultar todas las transacciones. |
| Bibliotecario | Agregar, editar y eliminar libros; registrar préstamos y devoluciones. |
| Lector | Explorar el catálogo, solicitar préstamos y devolver sus libros. |

Los permisos se comprueban en el servidor al abrir cada página, no solo en el menú.

## Cuentas del grupo

| Integrante | Rol | Correo |
| --- | --- | --- |
| Jorge Villamar Sánchez | Administrador | jorge.villamar@grupo04.edu.ec |
| Norka Mideros Quiñonez | Bibliotecario | norka.mideros@grupo04.edu.ec |
| Bruce Balarezo Chacón | Bibliotecario | bruce.balarezo@grupo04.edu.ec |
| Mauro Chinlle Carrillo | Lector | mauro.chinlle@grupo04.edu.ec |
| Liseth Criollo | Lector | liseth.criollo@grupo04.edu.ec |

Contraseña inicial de las cinco cuentas: **Grupo04-2026!**

El administrador puede crear más cuentas desde `usuarios.php`. Si el sitio se publica
fuera de la práctica, conviene cambiar las contraseñas.

## Registro de cuentas

Cualquier visitante puede crear una cuenta desde `registro.php`. La cuenta entra con el
rol **Lector**.

- Pide nombre completo, correo, contraseña y confirmación.
- Valida el formato del correo, que no esté repetido y que la clave tenga al menos 8 caracteres.
- Guarda la contraseña con `password_hash`, nunca en texto plano.
- Obliga a marcar que se entendió el aviso: es un sitio de práctica y no se deben usar
  correos de Gmail ni contraseñas reales.
- Al terminar redirige al login con el aviso «Cuenta creada. Ya puedes iniciar sesión con tu correo.».

## Estructura

```
pwtarea5/
├── index.php              Portada y catálogo público
├── login.php              Inicio de sesión
├── registro.php           Registro público de cuentas (rol Lector)
├── logout.php             Cierre de sesión
├── panel.php              Panel según el rol
├── libros.php             Alta, edición y baja de libros (equipo)
├── prestamos.php          Préstamos y devoluciones
├── usuarios.php           Cuentas y roles (administrador)
├── includes/
│   ├── config.php         Datos de conexión del hospedaje
│   ├── config.local.php   Datos para probar en local (no se publica)
│   ├── db.php             Conexión PDO
│   ├── auth.php           Sesión y permisos
│   ├── funciones.php      Utilidades y validaciones
│   ├── header.php         Cabecera y menú
│   └── footer.php         Pie y scripts
├── assets/
│   ├── css/estilos.css
│   ├── js/app.js
│   ├── fuentes/           Newsreader y Public Sans (woff2)
│   └── favicon.svg
├── img/                   Fotografías de Unsplash
└── sql/pwtarea5.sql       Esquema y datos iniciales
```

## Base de datos

El archivo `sql/pwtarea5.sql` crea las cuatro tablas del enunciado (`roles`, `users`,
`books`, `transactions`) y carga los roles, las cinco cuentas y doce libros.

En phpMyAdmin:

1. Crea la base de datos con cotejamiento `utf8mb4_unicode_ci`.
2. Entra a la base, pestaña **Importar**, elige `sql/pwtarea5.sql` y ejecuta.

## Probar en local

1. Copia `includes/config.local.php.ejemplo` como `includes/config.local.php`
   y escribe los datos de tu servidor MySQL.
2. Importa `sql/pwtarea5.sql` en una base llamada `pwtarea5_local`.
3. Sirve la carpeta con PHP:

   ```bash
   php -S 127.0.0.1:8099 -t .
   ```

4. Abre `http://127.0.0.1:8099`.

`includes/config.local.php` sobrescribe los datos del hospedaje y está excluido del
repositorio.

## Publicación en InfinityFree

1. Agrega el dominio `pwtarea5.infinityfreeapp.com` en el panel.
2. Crea la base de datos `pwtarea5` y anota el usuario y la contraseña de MySQL.
3. Sube el proyecto por FTP a `pwtarea5.infinityfreeapp.com/htdocs`.
4. Ajusta `includes/config.php` con el host, la base, el usuario y la clave. En el
   repositorio ese archivo trae datos de ejemplo; las credenciales reales nunca se publican.
5. Importa `sql/pwtarea5.sql` desde phpMyAdmin.
6. No subas `includes/config.local.php` ni la carpeta `sql` si no quieres exponer el esquema.

## Notas

- Todas las consultas usan sentencias preparadas y las contraseñas se guardan con
  `password_hash`.
- Cada formulario que modifica datos lleva un token CSRF.
- La disponibilidad se calcula restando los préstamos sin fecha de devolución.
- Un libro o una cuenta con préstamos registrados no se puede eliminar; la base lo impide
  con las llaves foráneas y la página lo explica.

## Créditos

Fotografías de Unsplash. Tipografías Newsreader y Public Sans de Google Fonts.
