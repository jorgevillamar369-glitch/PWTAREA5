<?php
/**
 * Portada pública: presentación, catálogo y equipo.
 * Cualquier visitante puede ver el catálogo; para solicitar un libro hay que entrar.
 */
require __DIR__ . '/includes/auth.php';

$titulo = 'Inicio';
$activo = 'inicio';

$pdo = conexion();

$libros = $pdo->query(
    'SELECT b.id, b.title, b.author, b.year, b.genre, b.quantity,
            (SELECT COUNT(*) FROM transactions t
              WHERE t.book_id = b.id AND t.date_of_return IS NULL) AS prestados
     FROM books b
     ORDER BY b.title'
)->fetchAll();

$generos = $pdo->query(
    'SELECT DISTINCT genre FROM books WHERE genre IS NOT NULL AND genre <> "" ORDER BY genre'
)->fetchAll(PDO::FETCH_COLUMN);

$totales = $pdo->query(
    'SELECT COUNT(*) AS titulos, COALESCE(SUM(quantity), 0) AS ejemplares FROM books'
)->fetch();

$activos = (int) $pdo->query(
    'SELECT COUNT(*) FROM transactions WHERE date_of_return IS NULL'
)->fetchColumn();

$equipo = [
    ['Jorge Villamar Sánchez', 'Administrador', 'Crea cuentas, asigna roles y revisa todas las transacciones.'],
    ['Norka Mideros Quiñonez', 'Bibliotecario', 'Mantiene el catálogo y registra préstamos y devoluciones.'],
    ['Bruce Balarezo Chacón', 'Bibliotecario', 'Mantiene el catálogo y registra préstamos y devoluciones.'],
    ['Mauro Chinlle Carrillo', 'Lector', 'Busca libros, solicita préstamos y devuelve los suyos.'],
    ['Liseth Criollo', 'Lector', 'Busca libros, solicita préstamos y devuelve los suyos.'],
];

include __DIR__ . '/includes/header.php';
?>

<main>
  <section class="portada">
    <div class="contenedor portada__fila">
      <div class="portada__texto">
        <p class="rotulo">Tarea 5 · Grupo 04</p>
        <h1 class="portada__titulo">
          Préstamo de libros<span class="punto">.</span>
        </h1>
        <p class="portada__entrada">
          Consulta el catálogo de la Biblioteca 04, solicita un ejemplar y devuélvelo cuando
          termines.
        </p>

        <div class="portada__acciones">
          <a class="boton" href="#catalogo">Ver el catálogo</a>
          <?php if (usuario_actual()): ?>
            <a class="boton boton--linea" href="panel.php">Ir a mi panel</a>
          <?php else: ?>
            <a class="boton boton--linea" href="login.php">Iniciar sesión</a>
          <?php endif; ?>
        </div>
        <?php if (!usuario_actual()): ?>
          <p class="portada__registro">
            ¿No tienes cuenta? <a href="registro.php">Crear una cuenta</a>
          </p>
        <?php endif; ?>

        <dl class="cifras">
          <div class="cifras__item">
            <dt class="cifras__rotulo">Títulos</dt>
            <dd class="cifras__valor"><?= (int) $totales['titulos'] ?></dd>
          </div>
          <div class="cifras__item">
            <dt class="cifras__rotulo">Ejemplares</dt>
            <dd class="cifras__valor"><?= (int) $totales['ejemplares'] ?></dd>
          </div>
          <div class="cifras__item">
            <dt class="cifras__rotulo">Préstamos activos</dt>
            <dd class="cifras__valor"><?= $activos ?></dd>
          </div>
        </dl>
      </div>

      <figure class="portada__figura">
        <img src="img/sala-lectura.jpg" alt="Estanterías curvas de una biblioteca llenas de libros"
             width="1600" height="1067">
        <figcaption>Estantería general · fondo abierto</figcaption>
      </figure>
    </div>
  </section>

  <section class="seccion" id="catalogo">
    <div class="contenedor">
      <div class="seccion__cabeza">
        <p class="rotulo">Catálogo</p>
        <h2 class="seccion__titulo">Los libros disponibles</h2>
      </div>

      <div class="filtros">
        <div class="filtros__campo">
          <label for="buscar">Buscar</label>
          <input type="search" id="buscar" placeholder="Título, autor o género" autocomplete="off">
        </div>
        <div class="filtros__campo filtros__campo--genero">
          <label for="genero">Género</label>
          <select id="genero">
            <option value="">Todos</option>
            <?php foreach ($generos as $genero): ?>
              <option value="<?= e($genero) ?>"><?= e($genero) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <p class="filtros__cuenta" data-cuenta><?= count($libros) ?> títulos</p>
      </div>

      <div class="listado" data-listado>
        <div class="listado__cabecera" aria-hidden="true">
          <span>N.º</span>
          <span>Libro</span>
          <span>Género</span>
          <span>Disponibilidad</span>
          <span>Acción</span>
        </div>

        <?php foreach ($libros as $indice => $libro): ?>
          <?php
            $disponibles = (int) $libro['quantity'] - (int) $libro['prestados'];
            $buscable = minusculas($libro['title'] . ' ' . $libro['author']);
          ?>
          <article class="libro"
                   data-titulo="<?= e($buscable) ?>"
                   data-genero="<?= e($libro['genre'] ?? '') ?>">
            <span class="libro__num"><?= str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT) ?></span>

            <div class="libro__datos">
              <h3 class="libro__titulo"><?= e($libro['title']) ?></h3>
              <p class="libro__autor"><?= e($libro['author']) ?> · <?= (int) $libro['year'] ?></p>
            </div>

            <span class="libro__genero"><?= e($libro['genre'] ?? 'Sin género') ?></span>

            <div class="libro__estado">
              <span class="libro__cifra"><?= $disponibles ?> de <?= (int) $libro['quantity'] ?></span>
              <span class="libro__nota"><?= $disponibles > 0 ? 'ejemplares libres' : 'sin ejemplares' ?></span>
            </div>

            <div class="libro__accion">
              <?php if (!usuario_actual()): ?>
                <a class="enlace" href="login.php">Entrar para solicitar</a>
              <?php elseif (es_equipo()): ?>
                <a class="boton boton--linea boton--pequeno" href="libros.php">Gestionar</a>
              <?php elseif ($disponibles > 0): ?>
                <form method="post" action="prestamos.php">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="accion" value="solicitar">
                  <input type="hidden" name="book_id" value="<?= (int) $libro['id'] ?>">
                  <button class="boton boton--pequeno" type="submit">Solicitar</button>
                </form>
              <?php else: ?>
                <span class="libro__agotado">Sin ejemplares</span>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>

        <p class="listado__vacio" data-vacio hidden>
          No hay libros que coincidan con la búsqueda. Prueba con otro título o cambia el género.
        </p>
      </div>
    </div>
  </section>

  <section class="seccion seccion--proceso">
    <div class="contenedor">
      <div class="seccion__cabeza seccion__cabeza--ancha">
        <p class="rotulo">Cómo funciona</p>
        <h2 class="seccion__titulo">Tres pasos, tres permisos distintos</h2>
      </div>

      <ol class="pasos">
        <li class="paso">
          <span class="paso__num">01</span>
          <h3 class="paso__titulo">Entra con tu cuenta</h3>
          <p class="paso__texto">
            Cada integrante del grupo tiene un rol asignado: Administrador, Bibliotecario o Lector.
            El menú cambia según el rol de la sesión.
          </p>
        </li>
        <li class="paso">
          <span class="paso__num">02</span>
          <h3 class="paso__titulo">Busca en el catálogo</h3>
          <p class="paso__texto">
            Filtra por título, autor o género. La disponibilidad se calcula con los préstamos
            abiertos, así que siempre muestra el número real de ejemplares libres.
          </p>
        </li>
        <li class="paso">
          <span class="paso__num">03</span>
          <h3 class="paso__titulo">Solicita o registra</h3>
          <p class="paso__texto">
            El Lector solicita el libro y lo devuelve al terminarlo. El Bibliotecario registra
            la salida y la devolución; el Administrador supervisa todo el movimiento.
          </p>
        </li>
      </ol>
    </div>
  </section>

  <section class="seccion seccion--equipo" id="equipo">
    <div class="contenedor equipo">
      <div class="equipo__intro">
        <p class="rotulo">Equipo</p>
        <h2 class="seccion__titulo">Grupo 04</h2>
        <p class="seccion__nota">
          Cinco integrantes, cinco cuentas y tres roles. La contraseña inicial de todas las
          cuentas es <strong>Grupo04-2026!</strong>.
        </p>
        <img class="equipo__imagen" src="img/estanteria.jpg"
             alt="Libros antiguos ordenados por número en una estantería de madera"
             width="1400" height="933">
      </div>

      <ul class="equipo__lista">
        <?php foreach ($equipo as $indice => $persona): ?>
          <li class="integrante">
            <span class="integrante__num"><?= str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <div class="integrante__datos">
              <h3 class="integrante__nombre"><?= e($persona[0]) ?></h3>
              <p class="integrante__texto"><?= e($persona[2]) ?></p>
            </div>
            <span class="etiqueta"><?= e($persona[1]) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
