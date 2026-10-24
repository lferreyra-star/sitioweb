<?php
/**
 * Portafolio público — vista principal.
 *
 * Todo el contenido se lee desde SQLite; se gestiona desde el panel /admin.
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Repos\EducacionRepo;
use App\Repos\ExperienciaRepo;
use App\Repos\HabilidadRepo;
use App\Repos\ProyectoRepo;

$habilidades  = (new HabilidadRepo())->listar();
$educaciones  = (new EducacionRepo())->listar();
$experiencias = (new ExperienciaRepo())->listar();
$proyectos    = (new ProyectoRepo())->listar();
$old          = old_obtener();

$roles = array_filter(array_map('trim', explode('|', cfg('hero_roles'))));

// Marca de tiempo anti-bots: si el formulario se envía antes de los 3 s, es un bot.
$_SESSION['formulario_ts'] = time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e(cfg('meta_descripcion')) ?>">
  <meta name="author" content="<?= e(cfg('perfil_nombre')) ?>">
  <meta property="og:title" content="<?= e(cfg('sitio_titulo')) ?>">
  <meta property="og:description" content="<?= e(cfg('meta_descripcion')) ?>">
  <meta property="og:type" content="website">
  <meta name="theme-color" content="#0b1120">
  <title><?= e(cfg('sitio_titulo')) ?></title>
  <link rel="stylesheet" href="<?= e(url('css/styles.css')) ?>">
</head>
<body>

  <!-- Mensajes de la sesión (flash) -->
  <div class="alertas" aria-live="polite"><?= flash_html() ?></div>

  <!-- Barra de navegación -->
  <header class="navbar">
    <div class="navbar__container">
      <a href="#inicio" class="navbar__logo"><?= e(cfg('logo_primero')) ?><span><?= e(cfg('logo_segundo')) ?></span></a>
      <div class="navbar__acciones">
        <?php if (App\Auth::check()): ?>
          <!-- Ya hay sesión activa: acceso directo al panel -->
          <a class="navbar__acceso navbar__acceso--panel" href="<?= e(url('admin/')) ?>" title="Ir al panel de administración">
            <span aria-hidden="true">🛠️</span><span class="navbar__acceso-texto">Panel</span>
          </a>
        <?php else: ?>
          <!-- Abre la ventana modal de acceso -->
          <button type="button" class="navbar__acceso" id="loginBtn" aria-haspopup="dialog" aria-controls="loginModal">
            <span aria-hidden="true">🔑</span><span class="navbar__acceso-texto">Acceso</span>
          </button>
        <?php endif; ?>
        <button class="navbar__tema" id="temaBtn" aria-label="Cambiar tema">
          <span class="icono-sol" aria-hidden="true">&#9728;</span>
          <span class="icono-luna" aria-hidden="true">&#9789;</span>
        </button>
        <button class="navbar__toggle" id="menuToggle" aria-label="Abrir menú" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
      </div>
      <nav class="navbar__menu" id="navMenu">
        <ul>
          <li><a href="#inicio" class="nav-link">Inicio</a></li>
          <li><a href="#sobre-mi" class="nav-link">Sobre mí</a></li>
          <li><a href="#educacion" class="nav-link">Educación</a></li>
          <li><a href="#experiencia" class="nav-link">Experiencia</a></li>
          <li><a href="#habilidades" class="nav-link">Habilidades</a></li>
          <li><a href="#proyectos" class="nav-link">Proyectos</a></li>
          <li><a href="#contacto" class="nav-link">Contacto</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <!-- Hero -->
  <section id="inicio" class="hero">
    <div class="hero__content">
      <div class="hero__layout">
        <div class="hero__texto">
          <p class="hero__greeting"><?= e(cfg('hero_saludo')) ?></p>
          <h1 class="hero__name">
            <span class="grad"><?= e(cfg('hero_nombre_grad')) ?></span><br><?= e(cfg('hero_nombre_resto')) ?>
          </h1>
          <h2 class="hero__rol" id="typed" data-roles="<?= e(implode('|', $roles)) ?>"></h2>
          <p class="hero__desc"><?= e(cfg('hero_descripcion')) ?></p>
          <div class="hero__buttons">
            <a href="#proyectos" class="btn btn--primary">Ver proyectos</a>
            <a href="#contacto" class="btn btn--outline">Contáctame</a>
          </div>
        </div>
        <div class="hero__foto">
          <img src="<?= e(url(cfg('foto', 'img/foto.jpg'))) ?>" alt="Foto de <?= e(cfg('perfil_nombre')) ?>" width="260" height="260">
        </div>
      </div>
    </div>
    <div class="hero__grid" aria-hidden="true"></div>
  </section>

  <!-- Sobre mí -->
  <section id="sobre-mi" class="section">
    <div class="container">
      <h2 class="section__title">Sobre mí</h2>
      <div class="sobre-mi">
        <div class="sobre-mi__avatar">
          <img src="<?= e(url(cfg('foto', 'img/foto.jpg'))) ?>" alt="Foto de <?= e(cfg('perfil_nombre')) ?>" loading="lazy">
        </div>
        <div class="sobre-mi__texto">
          <!-- Los párrafos admiten etiquetas HTML sencillas (<strong>, <em>, <a>) -->
          <p><?= cfg('sobre_parrafo_1') ?></p>
          <p><?= cfg('sobre_parrafo_2') ?></p>
          <div class="sobre-mi__datos">
            <div><span>Nombre:</span> <?= e(cfg('perfil_nombre')) ?></div>
            <div><span>Perfil:</span> <?= e(cfg('perfil_perfil')) ?></div>
            <div><span>Idiomas:</span> <?= e(cfg('perfil_idiomas')) ?></div>
            <div><span>Email:</span> <?= e(cfg('perfil_email')) ?></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Habilidades -->
  <?php if ($habilidades): ?>
  <section id="habilidades" class="section section--alt">
    <div class="container">
      <h2 class="section__title">Habilidades</h2>
      <div class="skills">
        <?php foreach ($habilidades as $habilidad): ?>
        <div class="skill-card">
          <div class="skill-card__icon" aria-hidden="true"><?= e($habilidad['icono']) ?></div>
          <h3><?= e($habilidad['nombre']) ?></h3>
          <div class="progress" role="progressbar" aria-valuenow="<?= (int) $habilidad['porcentaje'] ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="progress__bar" data-value="<?= (int) $habilidad['porcentaje'] ?>"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Educación -->
  <?php if ($educaciones): ?>
  <section id="educacion" class="section">
    <div class="container">
      <h2 class="section__title">Educación</h2>
      <div class="timeline">
        <?php foreach ($educaciones as $item): ?>
        <div class="timeline__item">
          <div class="timeline__punto"></div>
          <div class="timeline__card">
            <span class="timeline__fecha"><?= e($item['fecha']) ?></span>
            <h3><?= e($item['titulo']) ?></h3>
            <p><?= e($item['institucion']) ?></p>
            <?php if ($item['descripcion'] !== ''): ?>
              <small><?= e($item['descripcion']) ?></small>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Experiencia laboral -->
  <?php if ($experiencias): ?>
  <section id="experiencia" class="section section--alt">
    <div class="container">
      <h2 class="section__title">Experiencia Laboral</h2>
      <div class="timeline">
        <?php foreach ($experiencias as $item): ?>
        <div class="timeline__item">
          <div class="timeline__punto"></div>
          <div class="timeline__card">
            <span class="timeline__fecha"><?= e($item['fecha']) ?></span>
            <h3><?= e($item['titulo']) ?></h3>
            <p><?= e($item['institucion']) ?></p>
            <?php if ($item['descripcion'] !== ''): ?>
              <small><?= e($item['descripcion']) ?></small>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Proyectos -->
  <?php if ($proyectos): ?>
  <section id="proyectos" class="section">
    <div class="container">
      <h2 class="section__title">Proyectos</h2>
      <div class="proyectos">
        <?php foreach ($proyectos as $proyecto): ?>
        <article class="project-card" data-categoria="<?= e($proyecto['categoria']) ?>">
          <div class="project-card__img" aria-hidden="true"><?= e($proyecto['icono']) ?></div>
          <div class="project-card__body">
            <h3><?= e($proyecto['titulo']) ?></h3>
            <p><?= e($proyecto['descripcion']) ?></p>
            <div class="project-card__tags">
              <?php foreach (array_filter(array_map('trim', explode(',', $proyecto['tecnologias']))) as $tecnologia): ?>
                <span><?= e($tecnologia) ?></span>
              <?php endforeach; ?>
            </div>
            <?php if ($proyecto['enlace'] !== ''): ?>
              <a class="project-card__enlace" href="<?= e($proyecto['enlace']) ?>" target="_blank" rel="noopener noreferrer">
                Ver proyecto &rarr;
              </a>
            <?php endif; ?>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Contacto -->
  <section id="contacto" class="section section--alt">
    <div class="container container--narrow">
      <h2 class="section__title">Contacto</h2>
      <p class="section__sub"><?= e(cfg('contacto_titulo')) ?></p>
      <form id="formulario" class="form" action="<?= e(url('contacto.php')) ?>" method="post" novalidate>
        <?= csrf_campo() ?>
        <!-- Protección anti-bots: si este campo se rellena, se ignora el envío -->
        <input type="text" name="sitio" id="sitio" tabindex="-1" autocomplete="off" class="campo-fantasma" aria-hidden="true">
        <label class="sr-only" for="nombre">Tu nombre</label>
        <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" required
               maxlength="120" value="<?= e($old['nombre'] ?? '') ?>">
        <label class="sr-only" for="email">Tu correo electrónico</label>
        <input type="email" id="email" name="email" placeholder="Tu correo electrónico" required
               maxlength="190" value="<?= e($old['email'] ?? '') ?>">
        <label class="sr-only" for="mensaje">Tu mensaje</label>
        <textarea id="mensaje" name="mensaje" rows="5" placeholder="Tu mensaje" required
                  maxlength="3000"><?= e($old['mensaje'] ?? '') ?></textarea>
        <p class="form__error" id="formError"></p>
        <button type="submit" class="btn btn--primary btn--full" id="btnEnviar">Enviar mensaje</button>
      </form>
    </div>
  </section>

  <footer class="footer">
    <p>&copy; <span id="anio"></span> <?= e(cfg('footer_texto')) ?></p>
    <p class="footer__admin"><a href="<?= e(url('login.php')) ?>">Administración</a></p>
  </footer>

  <button class="scroll-top" id="scrollTop" aria-label="Volver arriba">&#8593;</button>

  <?php $whatsapp = preg_replace('/\D/', '', cfg('whatsapp')); ?>
  <?php if ($whatsapp !== ''): ?>
  <a class="wa-btn" href="https://wa.me/<?= e($whatsapp) ?>" target="_blank" rel="noopener" aria-label="Escríbeme por WhatsApp">
    <svg viewBox="0 0 32 32" width="26" height="26" aria-hidden="true">
      <path fill="currentColor" d="M16.04 4C9.06 4 3.38 9.68 3.38 16.66c0 2.23.58 4.4 1.69 6.32L3.24 29l6.16-1.61a12.6 12.6 0 0 0 6.63 1.83h.01c6.98 0 12.66-5.68 12.66-12.66C28.7 9.68 23.02 4 16.04 4Zm0 23.12h-.01a10.5 10.5 0 0 1-5.36-1.47l-.38-.23-3.66.96.97-3.57-.25-.38a10.44 10.44 0 0 1-1.61-5.58c0-5.79 4.71-10.5 10.5-10.5 2.8 0 5.43 1.09 7.42 3.08a10.43 10.43 0 0 1 3.07 7.42c0 5.79-4.71 10.5-10.5 10.5Zm5.76-7.86c-.32-.16-1.87-.92-2.16-1.03-.29-.11-.5-.16-.71.16-.21.32-.81 1.03-1 1.24-.18.21-.37.24-.69.08a8.66 8.66 0 0 1-2.54-1.56 9.51 9.51 0 0 1-1.76-2.19c-.18-.32-.02-.49.14-.65.14-.14.32-.37.47-.55.16-.21.21-.37.32-.61.11-.24.05-.45-.03-.61-.08-.16-.71-1.71-.97-2.34-.26-.61-.52-.53-.71-.54h-.61c-.21 0-.55.08-.84.37-.29.29-1.1 1.08-1.1 2.64 0 1.55 1.13 3.05 1.29 3.26.16.21 2.22 3.39 5.38 4.75.75.32 1.34.52 1.8.66.76.24 1.44.21 1.99.13.61-.09 1.87-.76 2.13-1.5.26-.73.26-1.37.18-1.5-.08-.13-.29-.21-.61-.37Z"/>
    </svg>
  </a>
  <?php endif; ?>

  <!-- ============================================================
       VENTANA MODAL DE LOGIN (acceso al panel de administración)
       ============================================================ -->
  <?php if (!App\Auth::check()): ?>
  <div class="modal" id="loginModal" role="dialog" aria-modal="true" aria-labelledby="loginModalTitulo" aria-hidden="true">
    <div class="modal__fondo" data-cerrar></div>

    <div class="modal__caja" role="document">
      <button type="button" class="modal__cerrar" data-cerrar aria-label="Cerrar la ventana">&times;</button>

      <div class="modal__icono" aria-hidden="true">&#128274;</div>
      <h2 class="modal__titulo" id="loginModalTitulo">Iniciar sesión</h2>
      <p class="modal__sub">Acceso exclusivo para el administrador del sitio.</p>

      <form id="loginModalForm" class="modal__form" action="<?= e(url('login.php')) ?>" method="post" novalidate>
        <?= csrf_campo() ?>
        <input type="hidden" name="ajax" value="1">

        <label class="modal__campo" for="modalUsuario">
          <span>Usuario</span>
          <input type="text" id="modalUsuario" name="usuario" placeholder="Tu usuario" required
                 maxlength="60" autocomplete="username">
        </label>

        <label class="modal__campo" for="modalClave">
          <span>Contraseña</span>
          <span class="modal__clave">
            <input type="password" id="modalClave" name="clave" placeholder="••••••••" required
                   maxlength="200" autocomplete="current-password">
            <button type="button" class="modal__ojo" id="modalOjo" aria-label="Mostrar u ocultar contraseña">&#128065;</button>
          </span>
        </label>

        <div class="modal__opciones">
          <label class="check">
            <input type="checkbox" name="recordar" value="1">
            <span>Recordarme</span>
          </label>
          <a class="modal__recuperar" href="<?= e(url('recuperar.php')) ?>">¿Olvidaste tu contraseña?</a>
        </div>

        <p class="modal__error" id="loginModalError" role="alert" hidden></p>

        <button type="submit" class="btn btn--primary btn--full" id="loginModalBtn">Entrar</button>
      </form>

      <a class="modal__enlace" href="<?= e(url('login.php')) ?>">
        ¿Problemas? Usa la página de acceso completa &rarr;
      </a>
    </div>
  </div>
  <?php endif; ?>

  <script src="<?= e(url('js/script.js')) ?>" defer></script>
</body>
</html>
