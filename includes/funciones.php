<?php
/**
 * Funciones de apoyo: texto seguro, avisos, fechas y roles.
 */

/** Escapa el texto antes de imprimirlo en HTML. */
function e(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** Envía al navegador a otra página y detiene el script. */
function redirigir(string $ruta): void
{
    header('Location: ' . $ruta);
    exit;
}

/** Token del formulario para evitar envíos desde otros sitios. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valido(?string $token): bool
{
    return is_string($token) && hash_equals($_SESSION['csrf'] ?? '', $token);
}

/** Guarda un aviso que se mostrará en la siguiente página. */
function flash(string $mensaje, string $tipo = 'ok'): void
{
    $_SESSION['flash'] = ['mensaje' => $mensaje, 'tipo' => $tipo];
}

/** Devuelve el aviso pendiente (si existe) y lo borra. */
function tomar_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $aviso = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $aviso;
}

/** Convierte 2026-11-12 en "12 nov 2026". */
function fecha_legible(?string $fecha): string
{
    if (empty($fecha)) {
        return 'Pendiente';
    }

    $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun',
              'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    $partes = explode('-', $fecha);
    if (count($partes) !== 3) {
        return $fecha;
    }

    $mes = (int) $partes[1];
    return (int) $partes[2] . ' ' . $meses[$mes - 1] . ' ' . $partes[0];
}

/** Nombre del rol a partir de su número. */
function nombre_rol(int $id): string
{
    return [
        1 => 'Administrador',
        2 => 'Bibliotecario',
        3 => 'Lector',
    ][$id] ?? 'Sin rol';
}

/** Pasa un texto a minúsculas; usa mbstring si está disponible. */
function minusculas(string $texto): string
{
    return function_exists('mb_strtolower')
        ? mb_strtolower($texto, 'UTF-8')
        : strtolower($texto);
}

/** Cuenta caracteres (no bytes) cuando mbstring está disponible. */
function longitud(string $texto): int
{
    return function_exists('mb_strlen')
        ? mb_strlen($texto, 'UTF-8')
        : strlen($texto);
}
