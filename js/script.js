/**
 * Portafolio — JavaScript público.
 *
 * Tema claro/oscuro, menú móvil, efecto máquina de escribir, animaciones
 * de entrada, validación del formulario de contacto y utilidades de scroll.
 * Todos los selectores están protegidos para que el archivo también funcione
 * en páginas parciales (login, error, etc.).
 */
(() => {
  'use strict';

  /** Limita la frecuencia de ejecución de un manejador de scroll. */
  const limitar = (funcion, espera = 100) => {
    let pendiente = false;
    return (...args) => {
      if (pendiente) return;
      pendiente = true;
      requestAnimationFrame(() => {
        funcion(...args);
        pendiente = false;
      });
    };
  };

  document.addEventListener('DOMContentLoaded', () => {
    /* ================================================
     *  1. Mensajes flash: se ocultan automáticamente
     * ============================================== */
    const alertas = document.querySelectorAll('.alerta');
    if (alertas.length > 0) {
      setTimeout(() => {
        alertas.forEach((alerta) => {
          alerta.style.transition = 'opacity .5s ease, transform .5s ease';
          alerta.style.opacity = '0';
          alerta.style.transform = 'translateY(-8px)';
          setTimeout(() => alerta.remove(), 550);
        });
      }, 5000);
    }

    /* ================================================
     *  2. Modo claro / oscuro (persistente)
     * ============================================== */
    const temaBtn = document.getElementById('temaBtn');
    if (temaBtn) {
      const guardado = localStorage.getItem('tema');
      const prefiereClaro = window.matchMedia('(prefers-color-scheme: light)').matches;
      document.body.classList.toggle('tema-claro', guardado ? guardado === 'claro' : prefiereClaro);

      temaBtn.addEventListener('click', () => {
        const claro = document.body.classList.toggle('tema-claro');
        localStorage.setItem('tema', claro ? 'claro' : 'oscuro');
      });
    }

    /* ================================================
     *  3. Menú de navegación móvil
     * ============================================== */
    const menuToggle = document.getElementById('menuToggle');
    const navMenu = document.getElementById('navMenu');

    if (menuToggle && navMenu) {
      const cerrarMenu = () => {
        menuToggle.classList.remove('abierto');
        navMenu.classList.remove('abierto');
        menuToggle.setAttribute('aria-expanded', 'false');
      };

      menuToggle.addEventListener('click', () => {
        const abierto = navMenu.classList.toggle('abierto');
        menuToggle.classList.toggle('abierto', abierto);
        menuToggle.setAttribute('aria-expanded', String(abierto));
      });

      document.querySelectorAll('.nav-link').forEach((enlace) => {
        enlace.addEventListener('click', cerrarMenu);
      });

      document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') cerrarMenu();
      });
    }

    /* ================================================
     *  4. Efecto máquina de escribir (data-roles)
     * ============================================== */
    const elementoRol = document.getElementById('typed');
    if (elementoRol) {
      const roles = (elementoRol.dataset.roles || '')
        .split('|')
        .map((rol) => rol.trim())
        .filter(Boolean);

      if (roles.length > 0) {
        let indiceRol = 0;
        let indiceTexto = 0;
        let escribiendo = true;

        const escribir = () => {
          const rolActual = roles[indiceRol];

          if (escribiendo) {
            elementoRol.textContent = rolActual.slice(0, ++indiceTexto);
            if (indiceTexto === rolActual.length) {
              escribiendo = false;
              setTimeout(escribir, 1600);
              return;
            }
            setTimeout(escribir, 80);
          } else {
            elementoRol.textContent = rolActual.slice(0, --indiceTexto);
            if (indiceTexto === 0) {
              escribiendo = true;
              indiceRol = (indiceRol + 1) % roles.length;
            }
            setTimeout(escribir, 45);
          }
        };

        escribir();
      }
    }

    /* ================================================
     *  5. Animaciones de entrada (reveal + progreso)
     * ============================================== */
    const observador = new IntersectionObserver(
      (entradas) => {
        entradas.forEach((entrada) => {
          if (!entrada.isIntersecting) return;

          entrada.target.classList.add('visible');

          // Barras de progreso dentro del elemento que aparece
          entrada.target.querySelectorAll('.progress__bar').forEach((barra) => {
            barra.style.width = `${barra.dataset.value || 0}%`;
          });

          observador.unobserve(entrada.target);
        });
      },
      { threshold: 0.15 }
    );

    document
      .querySelectorAll('.section, .project-card, .skill-card, .timeline__card')
      .forEach((elemento) => {
        elemento.classList.add('reveal');
        observador.observe(elemento);
      });

    // Si el navegador no soporta IntersectionObserver, mostrar todo ya.
    if (!('IntersectionObserver' in window)) {
      document.querySelectorAll('.reveal').forEach((el) => el.classList.add('visible'));
      document.querySelectorAll('.progress__bar').forEach((barra) => {
        barra.style.width = `${barra.dataset.value || 0}%`;
      });
    }

    /* ================================================
     *  6. Enlace activo según la sección visible
     * ============================================== */
    const secciones = document.querySelectorAll('section[id]');
    const enlaces = document.querySelectorAll('.nav-link');

    const resaltarEnlace = () => {
      const posicion = window.scrollY + 140;
      let actual = '';

      secciones.forEach((seccion) => {
        if (posicion >= seccion.offsetTop && posicion < seccion.offsetTop + seccion.offsetHeight) {
          actual = seccion.id;
        }
      });

      enlaces.forEach((enlace) => {
        enlace.classList.toggle('activo', enlace.getAttribute('href') === `#${actual}`);
      });
    };

    /* ================================================
     *  7. Botón "volver arriba"
     * ============================================== */
    const scrollTop = document.getElementById('scrollTop');
    if (scrollTop) {
      window.addEventListener(
        'scroll',
        limitar(() => {
          scrollTop.classList.toggle('visible', window.scrollY > 400);
        }),
        { passive: true }
      );

      scrollTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }

    window.addEventListener('scroll', limitar(resaltarEnlace), { passive: true });
    resaltarEnlace();

    /* ================================================
     *  8. Formulario de contacto (validación + envío)
     * ============================================== */
    const formulario = document.getElementById('formulario');
    if (formulario) {
      const formError = document.getElementById('formError');
      const btnEnviar = document.getElementById('btnEnviar');
      const campos = ['nombre', 'email', 'mensaje'].map((id) => document.getElementById(id));

      const validarCampo = (campo) => {
        const valor = campo.value.trim();
        if (!valor) return false;
        if (campo.type === 'email') {
          return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor);
        }
        if (campo.id === 'mensaje') return valor.length >= 10;
        return valor.length >= 2;
      };

      const marcarError = (campo, mal) => {
        campo.classList.toggle('error', mal);
        campo.setAttribute('aria-invalid', String(mal));
      };

      campos.forEach((campo) => {
        campo?.addEventListener('input', () => {
          if (campo.classList.contains('error') && validarCampo(campo)) {
            marcarError(campo, false);
            formError?.classList.remove('visible');
          }
        });
      });

      formulario.addEventListener('submit', (evento) => {
        let valido = true;

        campos.forEach((campo) => {
          if (!campo) return;
          const esValido = validarCampo(campo);
          marcarError(campo, !esValido);
          if (!esValido) valido = false;
        });

        if (!valido) {
          evento.preventDefault();
          if (formError) {
            formError.textContent = 'Por favor, completa todos los campos correctamente.';
            formError.classList.add('visible');
          }
          campos.find((campo) => campo?.classList.contains('error'))?.focus();
          return;
        }

        // Envío real al servidor: bloquear el botón para evitar dobles clics.
        if (btnEnviar) {
          btnEnviar.disabled = true;
          btnEnviar.classList.add('is-loading');
          btnEnviar.textContent = 'Enviando…';
        }
      });
    }

    /* ================================================
     *  9. Ventana modal de acceso (login del index)
     * ============================================== */
    const modal = document.getElementById('loginModal');
    const loginBtn = document.getElementById('loginBtn');

    if (modal && loginBtn) {
      const formModal    = document.getElementById('loginModalForm');
      const errorModal   = document.getElementById('loginModalError');
      const btnModal     = document.getElementById('loginModalBtn');
      const campoUsuario = document.getElementById('modalUsuario');
      const campoClave   = document.getElementById('modalClave');
      const ojo          = document.getElementById('modalOjo');
      const cajaModal    = modal.querySelector('.modal__caja');
      let focoPrevio     = null;

      const mostrarError = (mensaje) => {
        if (!errorModal) return;
        errorModal.textContent = mensaje || '';
        errorModal.hidden = !mensaje;
      };

      const abrirModal = () => {
        focoPrevio = document.activeElement;
        modal.classList.add('abierto');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-abierto');
        setTimeout(() => campoUsuario?.focus(), 80);
      };

      const cerrarModal = () => {
        modal.classList.remove('abierto');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-abierto');
        mostrarError('');
        if (focoPrevio && typeof focoPrevio.focus === 'function') focoPrevio.focus();
      };

      loginBtn.addEventListener('click', abrirModal);

      modal.querySelectorAll('[data-cerrar]').forEach((elemento) => {
        elemento.addEventListener('click', cerrarModal);
      });

      document.addEventListener('keydown', (evento) => {
        if (!modal.classList.contains('abierto')) return;

        // ESC cierra la ventana
        if (evento.key === 'Escape') {
          cerrarModal();
          return;
        }

        // Tabulación atrapada dentro del modal (accesibilidad)
        if (evento.key === 'Tab' && cajaModal) {
          const focables = cajaModal.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled])'
          );
          if (focables.length === 0) return;
          const primero = focables[0];
          const ultimo  = focables[focables.length - 1];

          if (evento.shiftKey && document.activeElement === primero) {
            evento.preventDefault();
            ultimo.focus();
          } else if (!evento.shiftKey && document.activeElement === ultimo) {
            evento.preventDefault();
            primero.focus();
          }
        }
      });

      // Mostrar / ocultar contraseña
      ojo?.addEventListener('click', () => {
        const visible = campoClave.type === 'text';
        campoClave.type = visible ? 'password' : 'text';
        ojo.setAttribute(
          'aria-label',
          visible ? 'Mostrar contraseña' : 'Ocultar contraseña'
        );
      });

      // Envío AJAX: valida, autentica y redirige sin recargar la página
      formModal?.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        mostrarError('');

        if (!campoUsuario.value.trim() || !campoClave.value) {
          mostrarError('Introduce tu usuario y contraseña.');
          (campoUsuario.value.trim() ? campoClave : campoUsuario).focus();
          return;
        }

        let accesoConcedido = false;
        btnModal.disabled = true;
        btnModal.classList.add('is-loading');
        btnModal.textContent = 'Entrando…';

        try {
          const respuesta = await fetch(formModal.action, {
            method: 'POST',
            body: new FormData(formModal),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
          });
          const datos = await respuesta.json();

          if (datos.ok) {
            accesoConcedido = true;
            btnModal.textContent = '¡Bienvenido! Redirigiendo…';
            window.location.assign(datos.redirect);
            return;
          }

          mostrarError(datos.error || 'No se pudo iniciar sesión.');
          campoClave.value = '';
          campoClave.focus();
        } catch {
          mostrarError('Error de conexión. Inténtalo de nuevo en unos segundos.');
        } finally {
          if (!accesoConcedido) {
            btnModal.disabled = false;
            btnModal.classList.remove('is-loading');
            btnModal.textContent = 'Entrar';
          }
        }
      });
    }

    /* ================================================
     *  10. Año automático del pie de página
     * ============================================== */
    const anio = document.getElementById('anio');
    if (anio) anio.textContent = String(new Date().getFullYear());
  });
})();
