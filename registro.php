<?php
/**
 * Registro público de cuentas.
 * Toda cuenta nueva entra con el rol Lector (3).
 */
require __DIR__ . '/includes/auth.php';

if (usuario_actual()) {
    redirigir('panel.php');
}

$titulo = 'Crear cuenta';
$errores = [];
$nombre = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf'] ?? null)) {
        $errores[] = 'El formulario expiró. Vuelve a intentarlo.';
    } else {
        $nombre   = trim(preg_replace('/\s+/', ' ', $_POST['username'] ?? ''));
        $email    = trim($_POST['email'] ?? '');
        $clave    = $_POST['password'] ?? '';
        $repetida = $_POST['password2'] ?? '';
        $acepto   = isset($_POST['acepto']);

        if ($nombre === '' || $email === '') {
            $errores[] = 'Escribe tu nombre completo y tu correo.';
        } elseif (longitud($nombre) > 50 || longitud($email) > 100) {
            $errores[] = 'El nombre o el correo son demasiado largos.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo no tiene un formato válido.';
        }

        if (strlen($clave) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if ($clave !== $repetida) {
            $errores[] = 'Las dos contraseñas no coinciden.';
        }

        if (!$acepto) {
            $errores[] = 'Confirma que entiendes que este es un sitio de práctica.';
        }

        if (!$errores) {
            $pdo = conexion();
            $consulta = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
            $consulta->execute([':email' => $email]);

            if ((int) $consulta->fetchColumn() > 0) {
                $errores[] = 'Ya existe una cuenta con ese correo.';
            } else {
                $consulta = conexion()->prepare(
                    'INSERT INTO users (username, email, password, role_id)
                     VALUES (:username, :email, :password, 3)'
                );
                $consulta->execute([
                    ':username' => $nombre,
                    ':email'    => $email,
                    ':password' => password_hash($clave, PASSWORD_DEFAULT),
                ]);

                flash('Cuenta creada. Ya puedes iniciar sesión con tu correo.');
                redirigir('login.php');
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<main class="acceso acceso--registro">
  <div class="acceso__imagen">
    <img src="img/sala-lectura.jpg" alt="Estanterías curvas de una biblioteca llenas de libros">
    <p class="acceso__leyenda">Una cuenta nueva, un libro por descubrir.</p>
  </div>

  <div class="acceso__panel">
    <div class="acceso__caja">
      <p class="rotulo">Registro</p>
      <h1 class="acceso__titulo">Crear una cuenta</h1>
      <p class="acceso__texto">
        Las cuentas nuevas entran como Lector: puedes buscar en el catálogo, solicitar libros
        y devolverlos.
      </p>

      <div class="nota-practica">
        <p class="nota-practica__titulo">Sitio de práctica</p>
        <p>
          No uses un correo de Gmail ni una contraseña que utilices en otros servicios.
          Inventa un correo y una clave solo para esta tarea.
        </p>
      </div>

      <?php if ($errores): ?>
        <ul class="lista-errores">
          <?php foreach ($errores as $error): ?>
            <li><?= e($error) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <form method="post" action="registro.php" class="formulario">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <div class="campo">
          <label for="username">Nombre y apellido</label>
          <input type="text" id="username" name="username" maxlength="50" required
                 autocomplete="name" value="<?= e($nombre) ?>" placeholder="Nombre completo">
        </div>

        <div class="campo">
          <label for="email">Correo</label>
          <input type="email" id="email" name="email" maxlength="100" required
                 autocomplete="email" value="<?= e($email) ?>"
                 placeholder="nombre@ejemplo.com">
        </div>

        <div class="campo">
          <label for="password">Contraseña</label>
          <div class="campo__fila">
            <input type="password" id="password" name="password" minlength="8" required
                   autocomplete="new-password" placeholder="Al menos 8 caracteres">
            <button type="button" class="campo__ver" data-ver-clave data-texto="Ocultar">Mostrar</button>
          </div>
        </div>

        <div class="campo">
          <label for="password2">Repetir contraseña</label>
          <input type="password" id="password2" name="password2" minlength="8" required
                 autocomplete="new-password" placeholder="La misma contraseña">
        </div>

        <label class="casilla">
          <input type="checkbox" name="acepto" value="1" required>
          <span>Entiendo que es un sitio de práctica y que no debo usar datos reales.</span>
        </label>

        <button type="submit" class="boton boton--ancho">Crear cuenta</button>
      </form>

      <p class="acceso__volver">
        ¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a>
      </p>
      <p class="acceso__volver acceso__volver--secundario">
        <a href="index.php">Volver al catálogo</a>
      </p>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
