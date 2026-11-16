<?php
/**
 * Gestión del catálogo. Solo para Administrador y Bibliotecario.
 */
require __DIR__ . '/includes/auth.php';
requerir_equipo();

$titulo = 'Libros';
$activo = 'libros';
$pdo = conexion();

/* ---------- Acciones del formulario ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf'] ?? null)) {
        flash('El formulario expiró. Vuelve a intentarlo.', 'error');
        redirigir('libros.php');
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);

        try {
            $consulta = $pdo->prepare('DELETE FROM books WHERE id = :id');
            $consulta->execute([':id' => $id]);
            flash('Libro eliminado del catálogo.');
        } catch (PDOException $e) {
            flash('No se puede eliminar: el libro tiene préstamos registrados.', 'error');
        }

        redirigir('libros.php');
    }

    if ($accion === 'guardar') {
        $id       = (int) ($_POST['id'] ?? 0);
        $nombre   = trim($_POST['title'] ?? '');
        $autor    = trim($_POST['author'] ?? '');
        $anio     = (int) ($_POST['year'] ?? 0);
        $genero   = trim($_POST['genre'] ?? '');
        $cantidad = (int) ($_POST['quantity'] ?? 0);

        if ($nombre === '' || $autor === '') {
            flash('El título y el autor son obligatorios.', 'error');
            redirigir($id > 0 ? 'libros.php?editar=' . $id : 'libros.php');
        }

        if ($cantidad < 0) {
            flash('La cantidad de ejemplares no puede ser negativa.', 'error');
            redirigir($id > 0 ? 'libros.php?editar=' . $id : 'libros.php');
        }

        if ($anio > 0 && ($anio < 1400 || $anio > (int) date('Y'))) {
            flash('El año debe estar entre 1400 y el año actual.', 'error');
            redirigir($id > 0 ? 'libros.php?editar=' . $id : 'libros.php');
        }

        if ($id > 0) {
            $sql = 'UPDATE books
                    SET title = :title, author = :author, `year` = :anio,
                        genre = :genre, quantity = :cantidad
                    WHERE id = :id';
        } else {
            $sql = 'INSERT INTO books (title, author, `year`, genre, quantity)
                    VALUES (:title, :author, :anio, :genre, :cantidad)';
        }

        $consulta = $pdo->prepare($sql);
        $datos = [
            ':title'    => $nombre,
            ':author'   => $autor,
            ':anio'     => $anio > 0 ? $anio : null,
            ':genre'    => $genero,
            ':cantidad' => $cantidad,
        ];

        if ($id > 0) {
            $datos[':id'] = $id;
        }

        $consulta->execute($datos);
        flash($id > 0 ? 'Libro actualizado.' : 'Libro agregado al catálogo.');
        redirigir('libros.php');
    }
}

/* ---------- Datos para la vista ---------- */
$libros = $pdo->query(
    'SELECT b.id, b.title, b.author, b.year, b.genre, b.quantity,
            (SELECT COUNT(*) FROM transactions t
              WHERE t.book_id = b.id AND t.date_of_return IS NULL) AS prestados
     FROM books b
     ORDER BY b.title'
)->fetchAll();

$editando = null;
if (isset($_GET['editar'])) {
    $consulta = $pdo->prepare('SELECT * FROM books WHERE id = :id');
    $consulta->execute([':id' => (int) $_GET['editar']]);
    $editando = $consulta->fetch() ?: null;
}

include __DIR__ . '/includes/header.php';
?>

<main class="contenedor pagina">
  <div class="pagina__cabeza">
    <div>
      <p class="rotulo">Catálogo · equipo</p>
      <h1 class="pagina__titulo">Libros</h1>
      <p class="pagina__texto">
        Altas, ediciones y bajas del catálogo. Los ejemplares prestados se calculan solos,
        no se escriben a mano.
      </p>
    </div>
    <div class="pagina__acciones">
      <a class="boton boton--linea" href="prestamos.php">Ir a préstamos</a>
    </div>
  </div>

  <div class="gestion">
    <section class="bloque gestion__lista">
      <div class="bloque__cabeza">
        <h2 class="bloque__titulo"><?= count($libros) ?> títulos en el catálogo</h2>
      </div>

      <div class="tabla-envoltura">
        <table class="tabla">
          <thead>
            <tr>
              <th>Título</th>
              <th>Año</th>
              <th>Género</th>
              <th class="tabla__num">Ejemplares</th>
              <th class="tabla__num">Prestados</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($libros as $libro): ?>
              <tr>
                <td>
                  <span class="tabla__destacado"><?= e($libro['title']) ?></span><br>
                  <span class="tabla__secundario"><?= e($libro['author']) ?></span>
                </td>
                <td><?= $libro['year'] ? (int) $libro['year'] : '—' ?></td>
                <td><?= e($libro['genre'] ?? '—') ?></td>
                <td class="tabla__num"><?= (int) $libro['quantity'] ?></td>
                <td class="tabla__num"><?= (int) $libro['prestados'] ?></td>
                <td class="tabla__acciones">
                  <a class="enlace" href="libros.php?editar=<?= (int) $libro['id'] ?>">Editar</a>
                  <form method="post" action="libros.php" class="en-linea"
                        data-confirmar="¿Eliminar «<?= e($libro['title']) ?>» del catálogo?">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= (int) $libro['id'] ?>">
                    <button class="enlace enlace--peligro" type="submit">Eliminar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <aside class="bloque gestion__form">
      <h2 class="bloque__titulo"><?= $editando ? 'Editar libro' : 'Agregar libro' ?></h2>

      <form method="post" action="libros.php" class="formulario">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="accion" value="guardar">
        <?php if ($editando): ?>
          <input type="hidden" name="id" value="<?= (int) $editando['id'] ?>">
        <?php endif; ?>

        <div class="campo">
          <label for="title">Título</label>
          <input type="text" id="title" name="title" maxlength="50" required
                 value="<?= e($editando['title'] ?? '') ?>">
        </div>

        <div class="campo">
          <label for="author">Autor</label>
          <input type="text" id="author" name="author" maxlength="50" required
                 value="<?= e($editando['author'] ?? '') ?>">
        </div>

        <div class="row g-3">
          <div class="col-6">
            <div class="campo">
              <label for="year">Año</label>
              <input type="number" id="year" name="year" min="1400" max="<?= date('Y') ?>"
                     value="<?= e((string) ($editando['year'] ?? '')) ?>">
            </div>
          </div>
          <div class="col-6">
            <div class="campo">
              <label for="quantity">Ejemplares</label>
              <input type="number" id="quantity" name="quantity" min="0" max="999" required
                     value="<?= e((string) ($editando['quantity'] ?? '1')) ?>">
            </div>
          </div>
        </div>

        <div class="campo">
          <label for="genre">Género</label>
          <input type="text" id="genre" name="genre" maxlength="50" list="generos"
                 value="<?= e($editando['genre'] ?? '') ?>">
          <datalist id="generos">
            <option value="Novela">
            <option value="Cuento">
            <option value="Poesía">
            <option value="Historia">
            <option value="Informática">
            <option value="Divulgación">
          </datalist>
        </div>

        <div class="formulario__acciones">
          <button type="submit" class="boton"><?= $editando ? 'Guardar cambios' : 'Agregar libro' ?></button>
          <?php if ($editando): ?>
            <a class="boton boton--linea" href="libros.php">Cancelar</a>
          <?php endif; ?>
        </div>
      </form>
    </aside>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
