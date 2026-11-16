<?php
/**
 * Configuracion del sistema.
 *
 * Este archivo va al repositorio con datos de ejemplo: no contiene
 * credenciales reales. Antes de publicar en el hospedaje, escribe aqui
 * el host, el nombre de la base, el usuario y la clave que te dio tu
 * proveedor (InfinityFree, 000webhost, etc.).
 *
 * Para probar en local, crea includes/config.local.php (excluido del
 * repositorio) con los datos de tu servidor MySQL: esos valores
 * reemplazan a los de aqui sin tocar este archivo.
 */

$config = [
    'host'    => 'localhost',
    'puerto'  => '3306',
    'nombre'  => 'pwtarea5',
    'usuario' => 'usuario',
    'clave'   => 'clave',
];

if (is_file(__DIR__ . '/config.local.php')) {
    $local = require __DIR__ . '/config.local.php';
    if (is_array($local)) {
        $config = array_merge($config, $local);
    }
}