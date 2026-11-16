<?php
/**
 * Usuarios y roles. Solo para el Administrador.
 */
require __DIR__ . '/includes/auth.php';
requerir_admin();

$titulo = 'Usuarios';
$activo = 'usuarios';
$usuario = usuario_actual();
$pdo = conexion();

/* ---------- Acciones ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf'] ?? null)) {
        flash('El formulario expiró. Vuelve a intentarlo.', 'error');
        redirigir('usuarios.php');
    }

    $accion = $_POST['accion'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($accion === 'rol') {
        $rol = (int) ($_POST['role_id'] ?? 0);

        if (!in_array($rol, [1, 2, 3], true)) {
            flash('Ese rol no existe.', 'error');
            redirigir('usuarios.php');
        }

        if ($id === (int) $usuario['id']) {
            flash('No puedes cambiar tu propio rol.', 'error');
            redirigir('usuarios.php');
        }

        $consulta = $pdo->prepare('UPDATE users SET role_id = :rol WHERE id = :id');
        $consulta->execute([':rol' => $rol, ':id' => $id]);

        flash('Rol actualizado a ' . nombre_rol($rol) . '.');
        redirigir('usuarios.php');
    }

    if ($accion === 'crear') {
        $nombre = trim($_POST['username'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $clave  = $_POST['password'] ?? '';
        $rol    = (int) ($_POST['role_id'] ?? 0);

        if ($nombre === '' || $email === '' || strlen($clave) < 8 || !in_array($rol, [1, 2, 3], true)) {
            flash('Completa el nombre, el correo y una contraseña de al menos 8 caracteres.', 'error');
            redirigir('usuarios.php');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('El correo no tiene un formato válido.', 'error');
            redirigir('usuarios.php');
        }

        $repetido = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $repetido->execute([':email' => $email]);

        if ((int) $repetido->fetchColumn() > 0) {
            flash('Ya existe una cuenta con ese correo.', 'error');
            redirigir('usuarios.php');
        }

        $consulta = $pdo->prepare(
            'INSERT INTO users (username, email, password, role_id)
             VALUES (:username, :email, :password, :role_id)'
        );
        $consulta->execute([
            ':username' => $nombre,
            ':email'    => $email,
            ':password' => password_hash($clave, PASSWORD_DEFAULT),
            ':role_id'  => $rol,
        ]);

        flash('Cuenta creada para ' . $nombre . '.');
        redirigir('usuarios.php');
    }

    if ($accion === 'eliminar') {
        if ($id === (int) $usuario['id']) {
            flash('No puedes eliminar tu propia cuenta.', 'error');
            redirigir('usuarios.php');
        }

        try {
            $consulta = $pdo->prepare('DELETE FROM users WHERE id = :id');
            $consulta->execute([':id' => $id]);
            flash('Cuenta eliminada.');
        } catch (PDOException $e) {
            flash('No se puede eliminar: la cuenta tiene préstamos registrados.', 'error');
        }

        redirigir('usuarios.php');
    }
}

/* ---------- Datos para la vista ---------- */
$usuarios = $pdo->query(
    'SELECT u.id, u.username, u.email, u.role_id,
            (SELECT COUNT(*) FROM transactions t
              WHERE t.user_id = u.id AND t.date_of_return IS NULL) AS activos
     FROM users u
     ORDER BY u.role_id, u.username'
)->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<main class="contenedor pagina">
  <div class="pagina__cabeza">
    <div>
      <p class="rotulo">Administración</p>
      <h1 class="pagina__titulo">Usuarios y roles</h1>
      <p class="pagina__texto">
        Las cinco cuentas del grupo están creadas desde el inicio. También puedes crear cuentas
        nuevas, cambiar el rol de una cuenta o eliminarla si no tiene préstamos en curso.
      </p>
    </div>
  </div>

  <div class="roles">
    <div class="rol">
      <span class="rol__num">01</span>
      <h2 class="rol__titulo">Administrador</h2>
      <p class="rol__texto">Crea y elimina cuentas, asigna roles y revisa todas las transacciones.</p>
    </div>
    <div class="rol">
      <span class="rol__num">02</span>
      <h2 class="rol__titulo">Bibliotecario</h2>
      <p class="rol__texto">Agrega, edita y elimina libros; registra préstamos y devoluciones.</p>
    </div>
    <div class="rol">
      <span class="rol__num">03</span>
      <h2 class="rol__titulo">Lector</h2>
      <p class="rol__texto">Explora el catálogo, solicita libros y devuelve los que ya terminó.</p>
    </div>
  </div>

  <section class="bloque">
    <div class="bloque__cabeza">
      <h2 class="bloque__titulo"><?= count($usuarios) ?> cuentas</h2>
      <span class="bloque__meta">La contraseña inicial es Grupo04-2026!</span>
    </div>

    <div class="tabla-envoltura">
      <table class="tabla">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Rol</th>
            <th class="tabla__num">Préstamos activos</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $fila): ?>
            <tr>
              <td class="tabla__destacado">
                <?= e($fila['username']) ?>
                <?php if ((int) $fila['id'] === (int) $usuario['id']): ?>
                  <span class="tabla__secundario">(tu cuenta)</span>
                <?php endif; ?>
              </td>
              <td><?= e($fila['email']) ?></td>
              <td>
                <?php if ((int) $fila['id'] === (int) $usuario['id']): ?>
                  <span class="etiqueta etiqueta--activo"><?= e(nombre_rol((int) $fila['role_id'])) ?></span>
                <?php else: ?>
                  <form method="post" action="usuarios.php" class="formulario-rol">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="accion" value="rol">
                    <input type="hidden" name="id" value="<?= (int) $fila['id'] ?>">
                    <select name="role_id" class="select-compacto" aria-label="Rol de <?= e($fila['username']) ?>">
                      <?php foreach ([1 => 'Administrador', 2 => 'Bibliotecario', 3 => 'Lector'] as $valor => $texto): ?>
                        <option value="<?= $valor ?>" <?= (int) $fila['role_id'] === $valor ? 'selected' : '' ?>>
                          <?= $texto ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button class="enlace" type="submit">Guardar</button>
                  </form>
                <?php endif; ?>
              </td>
              <td class="tabla__num"><?= (int) $fila['activos'] ?></td>
              <td>
                <?php if ((int) $fila['id'] !== (int) $usuario['id']): ?>
                  <form method="post" action="usuarios.php" class="en-linea"
                        data-confirmar="¿Eliminar la cuenta de <?= e($fila['username']) ?>?">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= (int) $fila['id'] ?>">
                    <button class="enlace enlace--peligro" type="submit">Eliminar</button>
                  </form>
                <?php else: ?>
                  <span class="tabla__secundario">Sesión activa</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="bloque bloque--formulario">
    <div class="bloque__cabeza">
      <h2 class="bloque__titulo">Crear una cuenta</h2>
      <span class="bloque__meta">La contraseña se guarda cifrada con password_hash</span>
    </div>

    <form method="post" action="usuarios.php" class="formulario formulario--cuenta">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="accion" value="crear">

      <div class="campo">
        <label for="username">Nombre y apellido</label>
        <input type="text" id="username" name="username" maxlength="50" required
               placeholder="Nombre completo">
      </div>

      <div class="campo">
        <label for="email">Correo</label>
        <input type="email" id="email" name="email" maxlength="100" required
               placeholder="nombre@grupo04.edu.ec">
      </div>

      <div class="campo">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" minlength="8" required
               placeholder="Al menos 8 caracteres">
      </div>

      <div class="campo">
        <label for="role_id">Rol</label>
        <select id="role_id" name="role_id" required>
          <option value="3" selected>Lector</option>
          <option value="2">Bibliotecario</option>
          <option value="1">Administrador</option>
        </select>
      </div>

      <div class="formulario__acciones">
        <button type="submit" class="boton">Crear cuenta</button>
      </div>
    </form>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
