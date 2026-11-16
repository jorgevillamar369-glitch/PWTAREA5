/* ============================================================
   Biblioteca 04 · interacciones de la interfaz
   Sin librerías: filtro del catálogo, contraseña visible,
   confirmación de borrados y cierre de avisos.
   ============================================================ */
(function () {
  'use strict';

  /* ---------- 1. Filtro del catálogo ---------- */
  const buscar = document.querySelector('#buscar');
  const genero = document.querySelector('#genero');
  const filas = Array.prototype.slice.call(document.querySelectorAll('.libro'));
  const cuenta = document.querySelector('[data-cuenta]');
  const vacio = document.querySelector('[data-vacio]');

  function filtrarCatalogo() {
    const texto = (buscar ? buscar.value : '').trim().toLowerCase();
    const tipo = genero ? genero.value : '';
    let visibles = 0;

    filas.forEach(function (fila) {
      const titulo = fila.getAttribute('data-titulo') || '';
      const generoFila = fila.getAttribute('data-genero') || '';
      const coincideTexto = texto === '' || titulo.indexOf(texto) !== -1;
      const coincideGenero = tipo === '' || generoFila === tipo;

      if (coincideTexto && coincideGenero) {
        fila.hidden = false;
        visibles++;
      } else {
        fila.hidden = true;
      }
    });

    if (cuenta) {
      cuenta.textContent = visibles + (visibles === 1 ? ' título' : ' títulos');
    }

    if (vacio) {
      vacio.hidden = visibles > 0;
    }
  }

  if (filas.length) {
    [buscar, genero].forEach(function (campo) {
      if (!campo) {
        return;
      }
      campo.addEventListener('input', filtrarCatalogo);
      campo.addEventListener('change', filtrarCatalogo);
    });
  }

  /* ---------- 2. Mostrar u ocultar la contraseña ---------- */
  document.querySelectorAll('[data-ver-clave]').forEach(function (boton) {
    boton.addEventListener('click', function () {
      const fila = boton.closest('.campo__fila');
      const campo = fila ? fila.querySelector('input') : null;

      if (!campo) {
        return;
      }

      const estabaOculta = campo.type === 'password';
      campo.type = estabaOculta ? 'text' : 'password';
      boton.textContent = estabaOculta ? 'Ocultar' : 'Mostrar';
      campo.focus();
    });
  });

  /* ---------- 3. Confirmar acciones destructivas ---------- */
  document.querySelectorAll('[data-confirmar]').forEach(function (formulario) {
    formulario.addEventListener('submit', function (evento) {
      if (!window.confirm(formulario.getAttribute('data-confirmar'))) {
        evento.preventDefault();
      }
    });
  });

  /* ---------- 4. Cerrar los avisos ---------- */
  document.querySelectorAll('[data-aviso]').forEach(function (aviso) {
    const cerrar = aviso.querySelector('[data-cerrar-aviso]');

    function quitar() {
      aviso.remove();
    }

    if (cerrar) {
      cerrar.addEventListener('click', quitar);
    }

    window.setTimeout(quitar, 9000);
  });
})();
