<?php
/**
 * Panel de inicio según el rol: resumen y últimos movimientos.
 */
require __DIR__ . '/includes/auth.php';
requerir_sesion();

$titulo = 'Mi panel';
$activo = 'panel';
$usuario = usuario_actual();
$pdo = conexion();

$totales = $pdo->query(
    'SELECT COUNT(*) AS titulos, COALESCE(SUM(quantity), 0) AS ejemplares FROM books'
)->fetch();

$activos = (int) $pdo->query(
    'SELECT COUNT(*) FROM transactions WHERE date_of_return IS NULL'
)->fetchColumn();

$nombre = explode(' ', $usuario['username'])[0];

if (es_admin()) {
    $usuarios = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

    $movimientos = $pdo->query(
        'SELECT t.id, t.date_of_issue, t.date_of_return, u.username, b.title
         FROM transactions t
         JOIN users u ON u.id = t.user_id
         JOIN books b ON b.id = t.book_id
         ORDER BY t.date_of_issue DESC, t.id DESC
         LIMIT 6'
    )->fetchAll();

    $intro = 'Tienes el control completo: cuentas, roles, catálogo y todas las transacciones de la biblioteca.';
    $tituloTabla = 'Últimos movimientos';
} elseif (es_equipo()) {
    $movimientos = $pdo->query(
        'SELECT t.id, t.date_of_issue, t.date_of_return, u.username, b.title
         FROM transactions t
         JOIN users u ON u.id = t.user_id
         JOIN books b ON b.id = t.book_id
         WHERE t.date_of_return IS NULL
         ORDER BY t.date_of_issue
         LIMIT 6'
    )->fetchAll();

    $intro = 'Desde aquí mantienes el catálogo y registras las salidas y devoluciones del día.';
    $tituloTabla = 'Préstamos activos';
} else {
    $movimientos = $pdo->query(
        'SELECT t.id, t.date_of_issue, t.date_of_return, b.title, u.username
         FROM transactions t
         JOIN books b ON b.id = t.book_id
         JOIN users u ON u.id = t.user_id
         WHERE t.user_id = ' . (int) $usuario['id'] . '
         ORDER BY t.date_of_issue DESC, t.id DESC
         LIMIT 8'
    )->fetchAll();

    $intro = 'Consulta tus préstamos, devuelve los libros que ya terminaste y sigue explorando el catálogo.';
    $tituloTabla = 'Mis préstamos';
}

$miosActivos = 0;
$miosDevueltos = 0;
if (!es_equipo()) {
    $miosActivos = (int) $pdo->query(
        'SELECT COUNT(*) FROM transactions
         WHERE user_id = ' . (int) $usuario['id'] . ' AND date_of_return IS NULL'
    )->fetchColumn();

    $miosDevueltos = (int) $pdo->query(
        'SELECT COUNT(*) FROM transactions
         WHERE user_id = ' . (int) $usuario['id'] . ' AND date_of_return IS NOT NULL'
    )->fetchColumn();
}

include __DIR__ . '/includes/header.php';
?>

<main class="contenedor pagina">
  <div class="pagina__cabeza">
    <div>
      <p class="rotulo"><?= e(nombre_rol((int) $usuario['role_id'])) ?></p>
      <h1 class="pagina__titulo">Hola, <?= e($nombre) ?>.</h1>
      <p class="pagina__texto"><?= e($intro) ?></p>
    </div>

    <div class="pagina__acciones">
      <?php if (es_equipo()): ?>
        <a class="boton" href="prestamos.php">Registrar préstamo</a>
        <a class="boton boton--linea" href="libros.php">Gestionar libros</a>
      <?php else: ?>
        <a class="boton" href="index.php#catalogo">Buscar un libro</a>
        <a class="boton boton--linea" href="prestamos.php">Mis préstamos</a>
      <?php endif; ?>
    </div>
  </div>

  <dl class="cifras cifras--panel">
    <div class="cifras__item">
      <dt class="cifras__rotulo">Títulos</dt>
      <dd class="cifras__valor"><?= (int) $totales['titulos'] ?></dd>
    </div>
    <div class="cifras__item">
      <dt class="cifras__rotulo">Ejemplares</dt>
      <dd class="cifras__valor"><?= (int) $totales['ejemplares'] ?></dd>
    </div>
    <?php if (es_admin()): ?>
      <div class="cifras__item">
        <dt class="cifras__rotulo">Préstamos activos</dt>
        <dd class="cifras__valor"><?= $activos ?></dd>
      </div>
      <div class="cifras__item">
        <dt class="cifras__rotulo">Usuarios</dt>
        <dd class="cifras__valor"><?= $usuarios ?></dd>
      </div>
    <?php elseif (es_equipo()): ?>
      <div class="cifras__item">
        <dt class="cifras__rotulo">Préstamos activos</dt>
        <dd class="cifras__valor"><?= $activos ?></dd>
      </div>
      <div class="cifras__item">
        <dt class="cifras__rotulo">Roles</dt>
        <dd class="cifras__valor">3</dd>
      </div>
    <?php else: ?>
      <div class="cifras__item">
        <dt class="cifras__rotulo">Mis préstamos activos</dt>
        <dd class="cifras__valor"><?= $miosActivos ?></dd>
      </div>
      <div class="cifras__item">
        <dt class="cifras__rotulo">Devoluciones</dt>
        <dd class="cifras__valor"><?= $miosDevueltos ?></dd>
      </div>
    <?php endif; ?>
  </dl>

  <div class="tablero">
    <section class="tablero__principal">
      <div class="bloque">
        <div class="bloque__cabeza">
          <h2 class="bloque__titulo"><?= e($tituloTabla) ?></h2>
          <a class="enlace" href="prestamos.php">Ver todo</a>
        </div>

        <?php if ($movimientos): ?>
          <div class="tabla-envoltura">
            <table class="tabla">
              <thead>
                <tr>
                  <?php if (es_equipo()): ?>
                    <th>Lector</th>
                    <th>Libro</th>
                  <?php else: ?>
                    <th>Libro</th>
                  <?php endif; ?>
                  <th>Salida</th>
                  <th>Devolución</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($movimientos as $fila): ?>
                  <tr>
                    <?php if (es_equipo()): ?>
                      <td><?= e($fila['username']) ?></td>
                      <td class="tabla__destacado"><?= e($fila['title']) ?></td>
                    <?php else: ?>
                      <td class="tabla__destacado"><?= e($fila['title']) ?></td>
                    <?php endif; ?>
                    <td><?= e(fecha_legible($fila['date_of_issue'])) ?></td>
                    <td><?= e(fecha_legible($fila['date_of_return'])) ?></td>
                    <td>
                      <?php if (empty($fila['date_of_return'])): ?>
                        <span class="etiqueta etiqueta--activo">En préstamo</span>
                      <?php else: ?>
                        <span class="etiqueta etiqueta--tenue">Devuelto</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="tabla__vacio">
            <?= es_equipo() ? 'No hay préstamos activos en este momento.' : 'Todavía no tienes préstamos registrados.' ?>
          </p>
        <?php endif; ?>
      </div>
    </section>

    <aside class="tablero__lateral">
      <div class="bloque">
        <h2 class="bloque__titulo">Atajos</h2>
        <ul class="atajos">
          <li><a href="index.php#catalogo">Catálogo completo</a></li>
          <?php if (es_equipo()): ?>
            <li><a href="prestamos.php">Préstamos y devoluciones</a></li>
            <li><a href="libros.php">Altas y ediciones de libros</a></li>
          <?php else: ?>
            <li><a href="prestamos.php">Mis préstamos y devoluciones</a></li>
          <?php endif; ?>
          <?php if (es_admin()): ?>
            <li><a href="usuarios.php">Usuarios y roles</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="bloque bloque--nota">
        <h2 class="bloque__titulo">Recordatorio</h2>
        <p class="bloque__texto">
          La disponibilidad del catálogo se calcula restando los préstamos sin devolver.
          Cuando un libro vuelve, el ejemplar queda libre otra vez.
        </p>
        <?php if (es_admin()): ?>
          <p class="bloque__texto">
            Desde Usuarios puedes cambiar el rol de cualquier cuenta o eliminarla si no tiene
            préstamos registrados. Las cinco cuentas del grupo ya están creadas.
          </p>
        <?php endif; ?>
      </div>
    </aside>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
