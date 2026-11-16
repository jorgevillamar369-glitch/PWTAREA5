<?php
/**
 * Sesión, inicio de sesión y permisos por rol.
 * Este archivo se incluye al principio de cada página.
 */

require __DIR__ . '/config.php';
require __DIR__ . '/funciones.php';
require __DIR__ . '/db.php';

header('Content-Type: text/html; charset=UTF-8');
date_default_timezone_set('America/Guayaquil');

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

/** Datos del usuario que está conectado, o null si es visitante. */
function usuario_actual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function es_admin(): bool
{
    return (int) (usuario_actual()['role_id'] ?? 0) === 1;
}

/** Administrador o bibliotecario: el equipo que gestiona el catálogo. */
function es_equipo(): bool
{
    return in_array((int) (usuario_actual()['role_id'] ?? 0), [1, 2], true);
}

function requerir_sesion(): void
{
    if (!usuario_actual()) {
        flash('Inicia sesión para continuar.', 'error');
        redirigir('login.php');
    }
}

function requerir_equipo(): void
{
    requerir_sesion();
    if (!es_equipo()) {
        flash('Esa sección es solo para el equipo de la biblioteca.', 'error');
        redirigir('panel.php');
    }
}

function requerir_admin(): void
{
    requerir_sesion();
    if (!es_admin()) {
        flash('Esa sección es solo para el administrador.', 'error');
        redirigir('panel.php');
    }
}

/** Comprueba correo y contraseña; devuelve el usuario o null. */
function buscar_usuario(string $email, string $clave): ?array
{
    $sql = 'SELECT u.id, u.username, u.email, u.password, u.role_id
            FROM users u
            WHERE u.email = :email
            LIMIT 1';

    $consulta = conexion()->prepare($sql);
    $consulta->execute([':email' => $email]);
    $fila = $consulta->fetch();

    if (!$fila || !password_verify($clave, $fila['password'])) {
        return null;
    }

    return $fila;
}

function iniciar_sesion(array $usuario): void
{
    session_regenerate_id(true);

    $_SESSION['usuario'] = [
        'id'       => (int) $usuario['id'],
        'username' => $usuario['username'],
        'email'    => $usuario['email'],
        'role_id'  => (int) $usuario['role_id'],
    ];
}

function cerrar_sesion(): void
{
    $_SESSION = [];
    session_destroy();
}
