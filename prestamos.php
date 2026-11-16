<?php
/**
 * Préstamos y devoluciones.
 *
 * - Lector: solicita un libro desde el catálogo y devuelve los suyos.
 * - Bibliotecario y Administrador: registran salidas y devoluciones de cualquier lector.
 */
require __DIR__ . '/includes/auth.php';
requerir_sesion();

$titulo = 'Préstamos';
$activo = 'prestamos';
$usuario = usuario_actual();
$pdo = conexion();
$equipo = es_equipo();
$hoy = date('Y-m-d');

/** Devuelve un libro con sus ejemplares y los que están prestados. */
function buscar_libro(int $id): ?array
{
    $sql = 'SELECT b.id, b.title, b.quantity,
                   (SELECT COUNT(*) FROM transactions t
                     WHERE t.book_id = b.id AND t.date_of_return IS NULL) AS prestados
            FROM books b
            WHERE b.id = :id';

    $consulta = conexion()->prepare($sql);
    $consulta->execute([':id' => $id]);

    return $consulta->fetch() ?: null;
}

/* ---------- Acciones ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf'] ?? null)) {
        flash('El formulario expiró. Vuelve a intentarlo.', 'error');
        redirigir('prestamos.php');
    }

    $accion = $_POST['accion'] ?? '';

    /* Solicitud del lector desde el catálogo */
    if ($accion === 'solicitar') {
        if ($equipo) {
            flash('El equipo registra los préstamos desde esta misma página.', 'error');
            redirigir('prestamos.php');
        }

        $libro = buscar_libro((int) ($_POST['book_id'] ?? 0));

        if (!$libro) {
            flash('Ese libro no existe en el catálogo.', 'error');
            redirigir('index.php#catalogo');
        }

        $repetido = $pdo->prepare(
            'SELECT COUNT(*) FROM transactions
             WHERE user_id = :usuario AND book_id = :libro AND date_of_return IS NULL'
        );
        $repetido->execute([':usuario' => $usuario['id'], ':libro' => $libro['id']]);

        if ((int) $repetido->fetchColumn() > 0) {
            flash('Ya tienes ese libro en préstamo.', 'error');
            redirigir('prestamos.php');
        }

        if ((int) $libro['quantity'] - (int) $libro['prestados'] <= 0) {
            flash('No quedan ejemplares de «' . $libro['title'] . '».', 'error');
            redirigir('index.php#catalogo');
        }

        $consulta = $pdo->prepare(
            'INSERT INTO transactions (user_id, book_id, date_of_issue, date_of_return)
             VALUES (:usuario, :libro, :salida, NULL)'
        );
        $consulta->execute([
            ':usuario' => $usuario['id'],
            ':libro'   => $libro['id'],
            ':salida'  => $hoy,
        ]);

        flash('Préstamo solicitado: «' . $libro['title'] . '».');
        redirigir('prestamos.php');
    }

    /* Registro del equipo */
    if ($accion === 'prestar' && $equipo) {
        $lectorId = (int) ($_POST['user_id'] ?? 0);
        $libro = buscar_libro((int) ($_POST['book_id'] ?? 0));

        $lector = $pdo->prepare('SELECT username FROM users WHERE id = :id AND role_id = 3');
        $lector->execute([':id' => $lectorId]);
        $nombreLector = $lector->fetchColumn();

        if (!$nombreLector || !$libro) {
            flash('Elige un lector registrado y un libro del catálogo.', 'error');
            redirigir('prestamos.php');
        }

        $repetido = $pdo->prepare(
            'SELECT COUNT(*) FROM transactions
             WHERE user_id = :usuario AND book_id = :libro AND date_of_return IS NULL'
        );
        $repetido->execute([':usuario' => $lectorId, ':libro' => $libro['id']]);

        if ((int) $repetido->fetchColumn() > 0) {
            flash('Ese lector ya tiene el libro en préstamo.', 'error');
            redirigir('prestamos.php');
        }

        if ((int) $libro['quantity'] - (int) $libro['prestados'] <= 0) {
            flash('No quedan ejemplares de «' . $libro['title'] . '».', 'error');
            redirigir('prestamos.php');
        }

        $consulta = $pdo->prepare(
            'INSERT INTO transactions (user_id, book_id, date_of_issue, date_of_return)
             VALUES (:usuario, :libro, :salida, NULL)'
        );
        $consulta->execute([
            ':usuario' => $lectorId,
            ':libro'   => $libro['id'],
            ':salida'  => $hoy,
        ]);

        flash('Préstamo registrado para ' . $nombreLector . '.');
        redirigir('prestamos.php');
    }

    /* Devolución */
    if ($accion === 'devolver') {
        $id = (int) ($_POST['id'] ?? 0);

        if ($equipo) {
            $consulta = $pdo->prepare(
                'UPDATE transactions SET date_of_return = :fecha
                 WHERE id = :id AND date_of_return IS NULL'
            );
            $consulta->execute([':fecha' => $hoy, ':id' => $id]);
        } else {
            $consulta = $pdo->prepare(
                'UPDATE transactions SET date_of_return = :fecha
                 WHERE id = :id AND user_id = :usuario AND date_of_return IS NULL'
            );
            $consulta->execute([':fecha' => $hoy, ':id' => $id, ':usuario' => $usuario['id']]);
        }

        if ($consulta->rowCount() > 0) {
            flash('Devolución registrada con fecha de hoy.');
        } else {
            flash('No se encontró un préstamo activo con ese número.', 'error');
        }

        redirigir('prestamos.php');
    }
}

/* ---------- Datos para la vista ---------- */
$where = $equipo ? '' : 'WHERE t.user_id = ' . (int) $usuario['id'];

$prestamos = $pdo->query(
    'SELECT t.id, t.date_of_issue, t.date_of_return, u.username, b.title
     FROM transactions t
     JOIN users u ON u.id = t.user_id
     JOIN books b ON b.id = t.book_id
     ' . $where . '
     ORDER BY (t.date_of_return IS NULL) DESC, t.date_of_issue DESC, t.id DESC'
)->fetchAll();

$activos = 0;
$devueltos = 0;
foreach ($prestamos as $prestamo) {
    if (empty($prestamo['date_of_return'])) {
        $activos++;
    } else {
        $devueltos++;
    }
}

$lectores = [];
$librosLibres = [];

if ($equipo) {
    $lectores = $pdo->query(
        'SELECT id, username FROM users WHERE role_id = 3 ORDER BY username'
    )->fetchAll();

    $libros = $pdo->query(
        'SELECT b.id, b.title, b.quantity,
                (SELECT COUNT(*) FROM transactions t
                  WHERE t.book_id = b.id AND t.date_of_return IS NULL) AS prestados
         FROM books b
         ORDER BY b.title'
    )->fetchAll();

    foreach ($libros as $libro) {
        if ((int) $libro['quantity'] - (int) $libro['prestados'] > 0) {
            $librosLibres[] = $libro;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<main class="contenedor pagina">
  <div class="pagina__cabeza">
    <div>
      <p class="rotulo">Circulación</p>
      <h1 class="pagina__titulo">Préstamos</h1>
      <p class="pagina__texto">
        <?php if ($equipo): ?>
          Cada salida y cada devolución quedan registradas con su fecha. Puedes prestar a
          cualquier lector y cerrar el préstamo cuando devuelva el libro.
        <?php else: ?>
          Aquí ves los libros que tienes en casa y los que ya devolviste. Para devolver uno,
          usa el botón <strong>Devolver</strong>.
        <?php endif; ?>
      </p>
    </div>
    <div class="pagina__acciones">
      <?php if ($equipo): ?>
        <a class="boton" href="#registrar">Registrar préstamo</a>
      <?php else: ?>
        <a class="boton" href="index.php#catalogo">Buscar un libro</a>
      <?php endif; ?>
    </div>
  </div>

  <dl class="cifras cifras--panel cifras--tres">
    <div class="cifras__item">
      <dt class="cifras__rotulo"><?= $equipo ? 'Préstamos activos' : 'Mis préstamos activos' ?></dt>
      <dd class="cifras__valor"><?= $activos ?></dd>
    </div>
    <div class="cifras__item">
      <dt class="cifras__rotulo">Devueltos</dt>
      <dd class="cifras__valor"><?= $devueltos ?></dd>
    </div>
    <div class="cifras__item">
      <dt class="cifras__rotulo">Total registrado</dt>
      <dd class="cifras__valor"><?= count($prestamos) ?></dd>
    </div>
  </dl>

  <?php if ($equipo): ?>
    <section class="bloque bloque--formulario" id="registrar">
      <h2 class="bloque__titulo">Registrar una salida</h2>

      <?php if ($lectores && $librosLibres): ?>
        <form method="post" action="prestamos.php" class="formulario formulario--horizontal">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="accion" value="prestar">

          <div class="campo">
            <label for="user_id">Lector</label>
            <select id="user_id" name="user_id" required>
              <option value="">Elige un lector</option>
              <?php foreach ($lectores as $lector): ?>
                <option value="<?= (int) $lector['id'] ?>"><?= e($lector['username']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="campo">
            <label for="book_id">Libro</label>
            <select id="book_id" name="book_id" required>
              <option value="">Elige un libro con ejemplares libres</option>
              <?php foreach ($librosLibres as $libro): ?>
                <option value="<?= (int) $libro['id'] ?>">
                  <?= e($libro['title']) ?> — <?= (int) $libro['quantity'] - (int) $libro['prestados'] ?> libre(s)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="formulario__acciones">
            <button type="submit" class="boton">Registrar préstamo</button>
          </div>
        </form>

        <p class="bloque__nota">La fecha de salida se guarda con la fecha de hoy (<?= e(fecha_legible($hoy)) ?>).</p>
      <?php else: ?>
        <p class="tabla__vacio">
          No hay lectores registrados o no quedan ejemplares libres en el catálogo.
        </p>
      <?php endif; ?>
    </section>
  <?php endif; ?>

  <section class="bloque">
    <div class="bloque__cabeza">
      <h2 class="bloque__titulo"><?= $equipo ? 'Todos los movimientos' : 'Mi historial' ?></h2>
      <span class="bloque__meta"><?= count($prestamos) ?> registros</span>
    </div>

    <?php if ($prestamos): ?>
      <div class="tabla-envoltura">
        <table class="tabla">
          <thead>
            <tr>
              <?php if ($equipo): ?>
                <th>Lector</th>
              <?php endif; ?>
              <th>Libro</th>
              <th>Salida</th>
              <th>Devolución</th>
              <th>Estado</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($prestamos as $prestamo): ?>
              <tr>
                <?php if ($equipo): ?>
                  <td><?= e($prestamo['username']) ?></td>
                <?php endif; ?>
                <td class="tabla__destacado"><?= e($prestamo['title']) ?></td>
                <td><?= e(fecha_legible($prestamo['date_of_issue'])) ?></td>
                <td><?= e(fecha_legible($prestamo['date_of_return'])) ?></td>
                <td>
                  <?php if (empty($prestamo['date_of_return'])): ?>
                    <span class="etiqueta etiqueta--activo">En préstamo</span>
                  <?php else: ?>
                    <span class="etiqueta etiqueta--tenue">Devuelto</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (empty($prestamo['date_of_return'])): ?>
                    <form method="post" action="prestamos.php">
                      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="accion" value="devolver">
                      <input type="hidden" name="id" value="<?= (int) $prestamo['id'] ?>">
                      <button class="boton boton--linea boton--pequeno" type="submit">Devolver</button>
                    </form>
                  <?php else: ?>
                    <span class="tabla__secundario">Cerrado</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="tabla__vacio">Todavía no hay préstamos registrados.</p>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
