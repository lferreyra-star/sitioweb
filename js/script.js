document.addEventListener('DOMContentLoaded', () => {
  // ===== Menú móvil =====
  const menuToggle = document.getElementById('menuToggle');
  const navMenu = document.getElementById('navMenu');

  menuToggle.addEventListener('click', () => {
    menuToggle.classList.toggle('abierto');
    navMenu.classList.toggle('abierto');
  });

  document.querySelectorAll('.nav-link').forEach((link) => {
    link.addEventListener('click', () => {
      menuToggle.classList.remove('abierto');
      navMenu.classList.remove('abierto');
    });
  });

  // ===== Efecto de texto mecanografiado =====
  const roles = [
    'Desarrollador Web',
    'Diseñador Front-End',
    'Creador de Experiencias Digitales'
  ];
  const elementoRol = document.getElementById('typed');
  let indiceRol = 0;
  let indiceTexto = 0;
  let escribiendo = true;

  function escribir() {
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
  }

  escribir();

  // ===== Animación de barras de progreso =====
  const barras = document.querySelectorAll('.progress__bar');

  function animarBarras() {
    barras.forEach((barra) => {
      const valor = barra.getAttribute('data-value');
      const posicion = barra.getBoundingClientRect().top;
      if (posicion < window.innerHeight - 60 && !barra.classList.contains('animado')) {
        barra.style.width = valor + '%';
        barra.classList.add('animado');
      }
    });
  }

  // ===== Animación de aparición (reveal) =====
  const observador = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
        }
      });
    },
    { threshold: 0.15 }
  );

  document.querySelectorAll('.section, .project-card, .skill-card, .timeline__card').forEach((el) => {
    el.classList.add('reveal');
    observador.observe(el);
  });

  window.addEventListener('scroll', animarBarras);
  animarBarras();

  // ===== Link activo según scroll =====
  const secciones = document.querySelectorAll('section[id]');
  const links = document.querySelectorAll('.nav-link');

  function resaltarLink() {
    const scrollY = window.scrollY + 120;
    secciones.forEach((seccion) => {
      const top = seccion.offsetTop;
      const altura = seccion.offsetHeight;
      if (scrollY >= top && scrollY < top + altura) {
        links.forEach((link) => {
          link.classList.toggle('activo', link.getAttribute('href') === '#' + seccion.id);
        });
      }
    });
  }

  window.addEventListener('scroll', resaltarLink);

  // ===== Botón volver arriba =====
  const scrollTop = document.getElementById('scrollTop');

  window.addEventListener('scroll', () => {
    if (window.scrollY > 400) {
      scrollTop.classList.add('visible');
    } else {
      scrollTop.classList.remove('visible');
    }
  });

  scrollTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // ===== Validación del formulario =====
  const formulario = document.getElementById('formulario');
  const formError = document.getElementById('formError');
  const campos = ['nombre', 'email', 'mensaje'];

  function validarCampo(campo) {
    const valor = campo.value.trim();
    if (!valor) return false;
    if (campo.type === 'email') {
      const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return regex.test(valor);
    }
    return true;
  }

  campos.forEach((id) => {
    const campo = document.getElementById(id);
    campo.addEventListener('input', () => {
      if (campo.classList.contains('error')) {
        campo.classList.remove('error');
        formError.classList.remove('visible');
      }
    });
  });

  formulario.addEventListener('submit', (e) => {
    e.preventDefault();
    let valido = true;

    campos.forEach((id) => {
      const campo = document.getElementById(id);
      const esValido = validarCampo(campo);
      campo.classList.toggle('error', !esValido);
      if (!esValido) valido = false;
    });

    if (!valido) {
      formError.textContent = 'Por favor, completa todos los campos correctamente.';
      formError.classList.add('visible');
      return;
    }

    formError.classList.remove('visible');
    formulario.innerHTML =
      '<div class="form__exito">¡Gracias por tu mensaje! Te responderé pronto.</div>';
  });

  // ===== Año automático en el footer =====
  document.getElementById('anio').textContent = new Date().getFullYear();
});