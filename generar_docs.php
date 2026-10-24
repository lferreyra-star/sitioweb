<?php
require __DIR__ . '/app/bootstrap.php';

use App\Repos\ConfigRepo;
use App\Repos\EducacionRepo;
use App\Repos\ExperienciaRepo;
use App\Repos\HabilidadRepo;
use App\Repos\ProyectoRepo;

@mkdir(__DIR__ . '/docs', 0775, true);
@mkdir(__DIR__ . '/docs/css', 0775, true);
@mkdir(__DIR__ . '/docs/js', 0775, true);
@mkdir(__DIR__ . '/docs/img', 0775, true);

@copy(__DIR__ . '/css/styles.css', __DIR__ . '/docs/css/styles.css');
@copy(__DIR__ . '/js/script.js', __DIR__ . '/docs/js/script.js');
@copy(__DIR__ . '/img/foto.jpg', __DIR__ . '/docs/img/foto.jpg');

$config       = (new ConfigRepo())->comoArreglo();
$habilidades  = (new HabilidadRepo())->listar();
$educaciones  = (new EducacionRepo())->listar();
$experiencias = (new ExperienciaRepo())->listar();
$proyectos    = (new ProyectoRepo())->listar();

function ee($v){ return htmlspecialchars((string)($v??''), ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function cv($c,$k,$d=''){ return $c[$k] ?? $d; }

$roles = array_filter(array_map('trim', explode('|', cv($config,'hero_roles'))));
$whatsapp = preg_replace('/\D/', '', cv($config,'whatsapp'));

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= ee(cv($config,'meta_descripcion')) ?>">
  <meta name="author" content="<?= ee(cv($config,'perfil_nombre')) ?>">
  <meta property="og:title" content="<?= ee(cv($config,'sitio_titulo')) ?>">
  <meta property="og:description" content="<?= ee(cv($config,'meta_descripcion')) ?>">
  <meta property="og:type" content="website">
  <meta name="theme-color" content="#0b1120">
  <title><?= ee(cv($config,'sitio_titulo')) ?></title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <header class="navbar">
    <div class="navbar__container">
      <a href="#inicio" class="navbar__logo"><?= ee(cv($config,'logo_primero')) ?><span><?= ee(cv($config,'logo_segundo')) ?></span></a>
      <div class="navbar__acciones">
        <button class="navbar__tema" id="temaBtn" aria-label="Cambiar tema"><span class="icono-sol">&#9728;</span><span class="icono-luna">&#9789;</span></button>
        <button class="navbar__toggle" id="menuToggle" aria-label="Abrir menú" aria-expanded="false"><span></span><span></span><span></span></button>
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

  <section id="inicio" class="hero">
    <div class="hero__content">
      <div class="hero__layout">
        <div class="hero__texto">
          <p class="hero__greeting"><?= ee(cv($config,'hero_saludo')) ?></p>
          <h1 class="hero__name"><span class="grad"><?= ee(cv($config,'hero_nombre_grad')) ?></span><br><?= ee(cv($config,'hero_nombre_resto')) ?></h1>
          <h2 class="hero__rol" id="typed" data-roles="<?= ee(implode('|', $roles)) ?>"></h2>
          <p class="hero__desc"><?= ee(cv($config,'hero_descripcion')) ?></p>
          <div class="hero__buttons">
            <a href="#proyectos" class="btn btn--primary">Ver proyectos</a>
            <a href="#contacto" class="btn btn--outline">Contáctame</a>
          </div>
        </div>
        <div class="hero__foto"><img src="img/foto.jpg" alt="Foto de <?= ee(cv($config,'perfil_nombre')) ?>" width="260" height="260"></div>
      </div>
    </div>
    <div class="hero__grid" aria-hidden="true"></div>
  </section>

  <section id="sobre-mi" class="section">
    <div class="container">
      <h2 class="section__title">Sobre mí</h2>
      <div class="sobre-mi">
        <div class="sobre-mi__avatar"><img src="img/foto.jpg" alt="Foto de <?= ee(cv($config,'perfil_nombre')) ?>" loading="lazy"></div>
        <div class="sobre-mi__texto">
          <p><?= cv($config,'sobre_parrafo_1') ?></p>
          <p><?= cv($config,'sobre_parrafo_2') ?></p>
          <div class="sobre-mi__datos">
            <div><span>Nombre:</span> <?= ee(cv($config,'perfil_nombre')) ?></div>
            <div><span>Perfil:</span> <?= ee(cv($config,'perfil_perfil')) ?></div>
            <div><span>Idiomas:</span> <?= ee(cv($config,'perfil_idiomas')) ?></div>
            <div><span>Email:</span> <?= ee(cv($config,'perfil_email')) ?></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php if ($habilidades): ?>
  <section id="habilidades" class="section section--alt">
    <div class="container">
      <h2 class="section__title">Habilidades</h2>
      <div class="skills">
        <?php foreach ($habilidades as $h): ?>
        <div class="skill-card">
          <div class="skill-card__icon"><?= ee($h['icono']) ?></div>
          <h3><?= ee($h['nombre']) ?></h3>
          <div class="progress" role="progressbar" aria-valuenow="<?= (int)$h['porcentaje'] ?>" aria-valuemin="0" aria-valuemax="100"><div class="progress__bar" data-value="<?= (int)$h['porcentaje'] ?>"></div></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($educaciones): ?>
  <section id="educacion" class="section">
    <div class="container">
      <h2 class="section__title">Educación</h2>
      <div class="timeline">
        <?php foreach ($educaciones as $item): ?>
        <div class="timeline__item">
          <div class="timeline__punto"></div>
          <div class="timeline__card">
            <span class="timeline__fecha"><?= ee($item['fecha']) ?></span>
            <h3><?= ee($item['titulo']) ?></h3>
            <p><?= ee($item['institucion']) ?></p>
            <?php if ($item['descripcion'] !== ''): ?><small><?= ee($item['descripcion']) ?></small><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($experiencias): ?>
  <section id="experiencia" class="section section--alt">
    <div class="container">
      <h2 class="section__title">Experiencia Laboral</h2>
      <div class="timeline">
        <?php foreach ($experiencias as $item): ?>
        <div class="timeline__item">
          <div class="timeline__punto"></div>
          <div class="timeline__card">
            <span class="timeline__fecha"><?= ee($item['fecha']) ?></span>
            <h3><?= ee($item['titulo']) ?></h3>
            <p><?= ee($item['institucion']) ?></p>
            <?php if ($item['descripcion'] !== ''): ?><small><?= ee($item['descripcion']) ?></small><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($proyectos): ?>
  <section id="proyectos" class="section">
    <div class="container">
      <h2 class="section__title">Proyectos</h2>
      <div class="proyectos">
        <?php foreach ($proyectos as $proyecto): ?>
        <article class="project-card" data-categoria="<?= ee($proyecto['categoria']) ?>">
          <div class="project-card__img"><?= ee($proyecto['icono']) ?></div>
          <div class="project-card__body">
            <h3><?= ee($proyecto['titulo']) ?></h3>
            <p><?= ee($proyecto['descripcion']) ?></p>
            <div class="project-card__tags">
              <?php foreach (array_filter(array_map('trim', explode(',', $proyecto['tecnologias']))) as $t): ?><span><?= ee($t) ?></span><?php endforeach; ?>
            </div>
            <?php if ($proyecto['enlace'] !== ''): ?><a class="project-card__enlace" href="<?= ee($proyecto['enlace']) ?>" target="_blank" rel="noopener">Ver proyecto &rarr;</a><?php endif; ?>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
