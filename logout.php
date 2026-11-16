<?php
/**
 * Cierra la sesión y vuelve a la portada con un aviso.
 */
require __DIR__ . '/includes/auth.php';

cerrar_sesion();

session_start();
flash('Sesión cerrada. Hasta la próxima visita.');
redirigir('index.php');
