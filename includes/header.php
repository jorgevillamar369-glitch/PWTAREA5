<?php
/**
 * Cabecera común: <head>, menú y avisos.
 * Cada página define $titulo y $activo antes de incluir este archivo.
 */
$usuario = usuario_actual();
$activo  = $activo ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo ?? 'Biblioteca') ?> · Biblioteca 04</title>
<meta name="description" content="Sistema de biblioteca en línea del Grupo 04. Catálogo, préstamos y devoluciones con tres roles de usuario.">
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
<link rel="preload" href="assets/fuentes/newsreader-400.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="assets/fuentes/publicsans-400.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>

<header class="cabecera">
  <div class="contenedor cabecera__fila">
    <a class="marca" href="index.php">
      <span class="marca__nombre">Biblioteca</span>
      <span class="marca__numero">04</span>
      <span class="marca__linea">Sistema en línea</span>
    </a>

    <button class="menu-boton d-lg-none" type="button" data-bs-toggle="collapse"
            data-bs-target="#menu" aria-expanded="false" aria-controls="menu">
      <span class="menu-boton__linea"></span>
      <span class="menu-boton__linea"></span>
      <span class="visually-hidden">Abrir menú</span>
    </button>

    <nav id="menu" class="menu collapse d-lg-flex">
      <a class="menu__enlace <?= $activo === 'inicio' ? 'menu__enlace--activo' : '' ?>" href="index.php">Inicio</a>
      <a class="menu__enlace <?= $activo === 'catalogo' ? 'menu__enlace--activo' : '' ?>" href="index.php#catalogo">Catálogo</a>

      <?php if ($usuario): ?>
        <a class="menu__enlace <?= $activo === 'panel' ? 'menu__enlace--activo' : '' ?>" href="panel.php">Mi panel</a>
        <?php if (es_equipo()): ?>
          <a class="menu__enlace <?= $activo === 'libros' ? 'menu__enlace--activo' : '' ?>" href="libros.php">Libros</a>
        <?php endif; ?>
        <a class="menu__enlace <?= $activo === 'prestamos' ? 'menu__enlace--activo' : '' ?>" href="prestamos.php">Préstamos</a>
        <?php if (es_admin()): ?>
          <a class="menu__enlace <?= $activo === 'usuarios' ? 'menu__enlace--activo' : '' ?>" href="usuarios.php">Usuarios</a>
        <?php endif; ?>

        <span class="menu__cuenta">
          <span class="menu__nombre"><?= e($usuario['username']) ?></span>
          <span class="menu__rol"><?= e(nombre_rol((int) $usuario['role_id'])) ?></span>
        </span>
        <a class="menu__salir" href="logout.php">Salir</a>
      <?php else: ?>
        <?php if (basename($_SERVER['PHP_SELF']) !== 'registro.php'): ?>
          <a class="menu__enlace" href="registro.php">Crear cuenta</a>
        <?php endif; ?>
        <?php if (basename($_SERVER['PHP_SELF']) !== 'login.php'): ?>
          <a class="menu__entrar" href="login.php">Iniciar sesión</a>
        <?php endif; ?>
      <?php endif; ?>
    </nav>
  </div>
</header>

<?php $aviso = tomar_flash(); ?>
<?php if ($aviso): ?>
  <div class="aviso aviso--<?= $aviso['tipo'] === 'error' ? 'error' : 'ok' ?>" data-aviso>
    <div class="contenedor aviso__fila">
      <p class="aviso__texto"><?= e($aviso['mensaje']) ?></p>
      <button type="button" class="aviso__cerrar" data-cerrar-aviso>Cerrar</button>
    </div>
  </div>
<?php endif; ?>
