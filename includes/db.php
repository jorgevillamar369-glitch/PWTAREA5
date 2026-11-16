<?php
/**
 * Conexión única a MySQL mediante PDO.
 * Se reutiliza la misma conexión durante toda la petición.
 */

function conexion(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    global $config;

    $dsn = 'mysql:host=' . $config['host']
         . ';port=' . $config['puerto']
         . ';dbname=' . $config['nombre']
         . ';charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, $config['usuario'], $config['clave'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        exit('No se pudo conectar con la base de datos. Revisa includes/config.php.');
    }

    return $pdo;
}
