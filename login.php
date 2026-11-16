<?php
/**
 * Acceso al sistema con correo y contraseña.
 */
require __DIR__ . '/includes/auth.php';

if (usuario_actual()) {
    redirigir('panel.php');
}

$titulo = 'Iniciar sesión';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf'] ?? null)) {
        flash('El formulario expiró. Vuelve a intentarlo.', 'error');
        redirigir('login.php');
    }

    $email = trim($_POST['email'] ?? '');
    $clave = $_POST['clave'] ?? '';

    if ($email === '' || $clave === '') {
        flash('Escribe tu correo y tu contraseña.', 'error');
        redirigir('login.php');
    }

    $usuario = buscar_usuario($email, $clave);

    if (!$usuario) {
        flash('El correo o la contraseña no coinciden.', 'error');
        redirigir('login.php');
    }

    iniciar_sesion($usuario);
    flash('Bienvenido de nuevo, ' . $usuario['username'] . '.');
    redirigir('panel.php');
}

include __DIR__ . '/includes/header.php';
?>

<main class="acceso">
  <div class="acceso__imagen">
    <img src="img/estanteria.jpg" alt="Libros antiguos ordenados por número en una estantería">
    <p class="acceso__leyenda">Cada préstamo empieza con una búsqueda.</p>
  </div>

  <div class="acceso__panel">
    <div class="acceso__caja">
      <p class="rotulo">Acceso</p>
      <h1 class="acceso__titulo">Iniciar sesión</h1>
      <p class="acceso__texto">
        Entra con el correo y la contraseña de tu cuenta. El sistema te llevará al panel que
        corresponde a tu rol.
      </p>

      <form method="post" action="login.php" class="formulario">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="campo">
          <label for="email">Correo</label>
          <input type="email" id="email" name="email" autocomplete="email" required
                 placeholder="nombre@grupo04.edu.ec">
        </div>

        <div class="campo">
          <label for="clave">Contraseña</label>
          <div class="campo__fila">
            <input type="password" id="clave" name="clave" autocomplete="current-password"
                   required placeholder="Contraseña de la cuenta">
            <button type="button" class="campo__ver" data-ver-clave data-texto="Ocultar">Mostrar</button>
          </div>
        </div>

        <button type="submit" class="boton boton--ancho">Entrar</button>
      </form>

      <div class="demo">
        <p class="demo__titulo">Cuentas de prueba</p>
        <ul class="demo__lista">
          <li><span class="etiqueta">Administrador</span> jorge.villamar@grupo04.edu.ec</li>
          <li><span class="etiqueta">Bibliotecario</span> norka.mideros@grupo04.edu.ec</li>
          <li><span class="etiqueta">Lector</span> mauro.chinlle@grupo04.edu.ec</li>
        </ul>
        <p class="demo__nota">
          Contraseña para las cinco cuentas del grupo: <strong>Grupo04-2026!</strong>
        </p>
        <p class="demo__nota">
          Sitio de práctica: no uses correos ni contraseñas reales.
        </p>
      </div>

      <p class="acceso__volver">¿No tienes cuenta? <a href="registro.php">Crear una cuenta</a></p>
      <p class="acceso__volver acceso__volver--secundario"><a href="index.php">Volver al catálogo</a></p>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
