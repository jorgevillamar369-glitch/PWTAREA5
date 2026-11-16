<?php
/** Pie de página común: integrantes, créditos y scripts. */
?>
<footer class="pie">
  <div class="contenedor">
    <div class="row gy-4">
      <div class="col-lg-5">
        <p class="pie__marca">Biblioteca <span>04</span></p>
        <p class="pie__texto">
          Sistema de gestión de biblioteca en línea. Tarea 5 de Programación Web Avanzada,
          Universidad UBE. Hecho con HTML, Bootstrap 5, CSS, JavaScript, PHP y MySQL.
        </p>
      </div>

      <div class="col-lg-4">
        <p class="pie__titulo">Grupo 04</p>
        <ul class="pie__lista">
          <li>Jorge Villamar Sánchez</li>
          <li>Norka Mideros Quiñonez</li>
          <li>Bruce Balarezo Chacón</li>
          <li>Mauro Chinlle Carrillo</li>
          <li>Liseth Criollo</li>
        </ul>
      </div>

      <div class="col-lg-3">
        <p class="pie__titulo">Créditos</p>
        <p class="pie__texto pie__texto--pequeno">
          Fotografías: Unsplash.<br>
          Tipografías: Newsreader y Public Sans (Google Fonts).
        </p>
        <p class="pie__fecha"><?= date('Y') ?></p>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
