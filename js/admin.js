/**
 * Panel de administración: menú móvil, confirmaciones de borrado
 * y auto-ocultado de los mensajes flash.
 */
(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    /* ---------- Menú lateral en móvil ---------- */
    const toggle = document.getElementById('adminToggle');
    const sidebar = document.getElementById('adminSidebar');

    if (toggle && sidebar) {
      toggle.addEventListener('click', () => {
        const abierto = sidebar.classList.toggle('abierto');
        toggle.setAttribute('aria-expanded', String(abierto));
      });

      // Cerrar al pulsar fuera del menú
      document.addEventListener('click', (evento) => {
        if (
          sidebar.classList.contains('abierto') &&
          !sidebar.contains(evento.target) &&
          !toggle.contains(evento.target)
        ) {
          sidebar.classList.remove('abierto');
          toggle.setAttribute('aria-expanded', 'false');
        }
      });

      document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') {
          sidebar.classList.remove('abierto');
          toggle.setAttribute('aria-expanded', 'false');
        }
      });
    }

    /* ---------- Confirmación antes de eliminar ---------- */
    document.querySelectorAll('form[data-confirm]').forEach((formulario) => {
      formulario.addEventListener('submit', (evento) => {
        if (!window.confirm(formulario.dataset.confirm)) {
          evento.preventDefault();
        }
      });
    });

    /* ---------- Auto-ocultar alertas ---------- */
    const alertas = document.querySelectorAll('.alerta');
    if (alertas.length > 0) {
      setTimeout(() => {
        alertas.forEach((alerta) => {
          alerta.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
          alerta.style.opacity = '0';
          alerta.style.transform = 'translateY(-8px)';
          setTimeout(() => alerta.remove(), 550);
        });
      }, 5000);
    }
  });
})();
