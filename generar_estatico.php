<?php
$root = __DIR__;
$dbPath = $root . '/storage/portafolio.sqlite';

if (!is_file($dbPath)) {
    die("No existe storage/portafolio.sqlite\n");
}

@mkdir($root . '/docs', 0775, true);
@mkdir($root . '/docs/css', 0775, true);
@mkdir($root . '/docs/js', 0775, true);
@mkdir($root . '/docs/img', 0775, true);

// Copiar assets tal como están
@copy($root . '/css/styles.css', $root . '/docs/css/styles.css');
@copy($root . '/js/script.js', $root . '/docs/js/script.js');
@copy($root . '/img/foto.jpg', $root . '/docs/img/foto.jpg');

$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Config
$config = [];
foreach ($db->query("SELECT clave, valor FROM configuraciones") as $row) {
    $config[$row['clave']] = $row['valor'];
}

// Datos
$habilidades  = $db->query("SELECT * FROM habilidades ORDER BY orden ASC, id ASC")->fetchAll();
$educaciones  = $db->query("SELECT * FROM educaciones ORDER BY orden ASC, id ASC")->fetchAll();
$experiencias = $db->query("SELECT * FROM experiencias ORDER BY orden ASC, id ASC")->fetchAll();
$proyectos    = $db->query("SELECT * FROM proyectos ORDER BY orden ASC, id ASC")->fetchAll();

function e($v){
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function c($config, $k, $d = ''){
    return $config[$k] ?? $d;
}

$roles = array_filter(array_map('trim', explode('|', c($config, 'hero_roles'))));
$whatsapp = preg_replace('/\D/', '', c($config, 'whatsapp'));

$html = '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="' . e(c($config,'meta_descripcion')) . '">
  <meta name="author" content="' . e(c($config,'perfil_nombre')) . '">
  <meta property="og:title" content="' . e(c($config,'sitio_titulo')) . '">
  <meta property="og:description" content="' . e(c($config,'meta_descripcion')) . '">
  <meta property="og:type" content="website">
  <meta name="theme-color" content="#0b1120">
  <title>' . e(c($config,'sitio_titulo')) . '</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <header class="navbar">
    <div class="navbar__container">
      <a href="#inicio" class="navbar__logo">' . e(c($config,'logo_primero')) . '<span>' . e(c($config,'logo_segundo')) . '</span></a>
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
          <p class="hero__greeting">' . e(c($config,'hero_saludo')) . '</p>
          <h1 class="hero__name"><span class="grad">' . e(c($config,'hero_nombre_grad')) . '</span><br>' . e(c($config,'hero_nombre_resto')) . '</h1>
          <h2 class="hero__rol" id="typed" data-roles="' . e(implode('|', $roles)) . '"></h2>
          <p class="hero__desc">' . e(c($config,'hero_descripcion')) . '</p>
          <div class="hero__buttons">
            <a href="#proyectos" class="btn btn--primary">Ver proyectos</a>
            <a href="#contacto" class="btn btn--outline">Contáctame</a>
          </div>
        </div>
        <div class="hero__foto"><img src="img/foto.jpg" alt="Foto de ' . e(c($config,'perfil_nombre')) . '" width="260" height="260"></div>
      </div>
    </div>
    <div class="hero__grid" aria-hidden="true"></div>
  </section>

  <section id="sobre-mi" class="section">
    <div class="container">
      <h2 class="section__title">Sobre mí</h2>
      <div class="sobre-mi">
        <div class="sobre-mi__avatar"><img src="img/foto.jpg" alt="Foto de ' . e(c($config,'perfil_nombre')) . '" loading="lazy"></div>
        <div class="sobre-mi__texto">
          <p>' . c($config,'sobre_parrafo_1') . '</p>
          <p>' . c($config,'sobre_parrafo_2') . '</p>
          <div class="sobre-mi__datos">
            <div><span>Nombre:</span> ' . e(c($config,'perfil_nombre')) . '</div>
            <div><span>Perfil:</span> ' . e(c($config,'perfil_perfil')) . '</div>
            <div><span>Idiomas:</span> ' . e(c($config,'perfil_idiomas')) . '</div>
            <div><span>Email:</span> ' . e(c($config,'perfil_email')) . '</div>
          </div>
        </div>
      </div>
    </div>
  </section>';

if ($habilidades) {
    $html .= '
  <section id="habilidades" class="section section--alt">
    <div class="container">
      <h2 class="section__title">Habilidades</h2>
      <div class="skills">';
    foreach ($habilidades as $h) {
        $html .= '
        <div class="skill-card">
          <div class="skill-card__icon">' . e($h['icono']) . '</div>
          <h3>' . e($h['nombre']) . '</h3>
          <div class="progress" role="progressbar" aria-valuenow="' . (int)$h['porcentaje'] . '" aria-valuemin="0" aria-valuemax="100"><div class="progress__bar" data-value="' . (int)$h['porcentaje'] . '"></div></div>
        </div>';
    }
    $html .= '
      </div>
    </div>
  </section>';
}

if ($educaciones) {
    $html .= '
  <section id="educacion" class="section">
    <div class="container">
      <h2 class="section__title">Educación</h2>
      <div class="timeline">';
    foreach ($educaciones as $item) {
        $html .= '
        <div class="timeline__item">
          <div class="timeline__punto"></div>
          <div class="timeline__card">
            <span class="timeline__fecha">' . e($item['fecha']) . '</span>
            <h3>' . e($item['titulo']) . '</h3>
            <p>' . e($item['institucion']) . '</p>';
        if ($item['descripcion'] !== '') {
            $html .= '            <small>' . e($item['descripcion']) . '</small>';
        }
        $html .= '
          </div>
        </div>';
    }
    $html .= '
      </div>
    </div>
  </section>';
}

if ($experiencias) {
    $html .= '
  <section id="experiencia" class="section section--alt">
    <div class="container">
      <h2 class="section__title">Experiencia Laboral</h2>
      <div class="timeline">';
    foreach ($experiencias as $item) {
        $html .= '
        <div class="timeline__item">
          <div class="timeline__punto"></div>
          <div class="timeline__card">
            <span class="timeline__fecha">' . e($item['fecha']) . '</span>
            <h3>' . e($item['titulo']) . '</h3>
            <p>' . e($item['institucion']) . '</p>';
        if ($item['descripcion'] !== '') {
            $html .= '            <small>' . e($item['descripcion']) . '</small>';
        }
        $html .= '
          </div>
        </div>';
    }
    $html .= '
      </div>
    </div>
  </section>';
}

if ($proyectos) {
    $html .= '
  <section id="proyectos" class="section">
    <div class="container">
      <h2 class="section__title">Proyectos</h2>
      <div class="proyectos">';
    foreach ($proyectos as $proyecto) {
        $html .= '
        <article class="project-card" data-categoria="' . e($proyecto['categoria']) . '">
          <div class="project-card__img">' . e($proyecto['icono']) . '</div>
          <div class="project-card__body">
            <h3>' . e($proyecto['titulo']) . '</h3>
            <p>' . e($proyecto['descripcion']) . '</p>
            <div class="project-card__tags">';
        $tags = array_filter(array_map('trim', explode(',', $proyecto['tecnologias'])));
        foreach ($tags as $t) {
            $html .= '<span>' . e($t) . '</span>';
        }
        $html .= '
            </div>';
        if ($proyecto['enlace'] !== '') {
            $html .= '<a class="project-card__enlace" href="' . e($proyecto['enlace']) . '" target="_blank" rel="noopener">Ver proyecto &rarr;</a>';
        }
        $html .= '
          </div>
        </article>';
    }
    $html .= '
      </div>
    </div>
  </section>';
}

$html .= '
  <section id="contacto" class="section section--alt">
    <div class="container container--narrow">
      <h2 class="section__title">Contacto</h2>
      <p class="section__sub">' . e(c($config,'contacto_titulo')) . '</p>
      <div class="form" style="text-align:center;padding:2rem;">
        <p>El formulario requiere el servidor PHP para enviar mensajes.</p>
        <p><strong>Email:</strong> ' . e(c($config,'perfil_email')) . '</p>';
if ($whatsapp !== '') {
    $html .= '<p><a class="btn btn--primary" href="https://wa.me/' . e($whatsapp) . '" target="_blank" rel="noopener">Escríbeme por WhatsApp</a></p>';
}
$html .= '
      </div>
    </div>
  </section>

  <footer class="footer">
    <p>&copy; <span id="anio"></span> ' . e(c($config,'footer_texto')) . '</p>
    <p class="footer__admin">Versión estática para GitHub Pages · App PHP completa en el repositorio</p>
  </footer>
  <button class="scroll-top" id="scrollTop" aria-label="Volver arriba">&#8593;</button>';

if ($whatsapp !== '') {
    $html .= '
  <a class="wa-btn" href="https://wa.me/' . e($whatsapp) . '" target="_blank" rel="noopener" aria-label="Escríbeme por WhatsApp">
    <svg viewBox="0 0 32 32" width="26" height="26"><path fill="currentColor" d="M16.04 4C9.06 4 3.38 9.68 3.38 16.66c0 2.23.58 4.4 1.69 6.32L3.24 29l6.16-1.61a12.6 12.6 0 0 0 6.63 1.83h.01c6.98 0 12.66-5.68 12.66-12.66C28.7 9.68 23.02 4 16.04 4Zm0 23.12h-.01a10.5 10.5 0 0 1-5.36-1.47l-.38-.23-3.66.96.97-3.57-.25-.38a10.44 10.44 0 0 1-1.61-5.58c0-5.79 4.71-10.5 10.5-10.5 2.8 0 5.43 1.09 7.42 3.08a10.43 10.43 0 0 1 3.07 7.42c0 5.79-4.71 10.5-10.5 10.5Zm5.76-7.86c-.32-.16-1.87-.92-2.16-1.03-.29-.11-.5-.16-.71.16-.21.32-.81 1.03-1 1.24-.18.21-.37.24-.69.08a8.66 8.66 0 0 1-2.54-1.56 9.51 9.51 0 0 1-1.76-2.19c-.18-.32-.02-.49.14-.65.14-.14.32-.37.47-.55.16-.21.21-.37.32-.61.11-.24.05-.45-.03-.61-.08-.16-.71-1.71-.97-2.34-.26-.61-.52-.53-.71-.54h-.61c-.21 0-.55.08-.84.37-.29.29-1.1 1.08-1.1 2.64 0 1.55 1.13 3.05 1.29 3.26.16.21 2.22 3.39 5.38 4.75.75.32 1.34.52 1.8.66.76.24 1.44.21 1.99.13.61-.09 1.87-.76 2.13-1.5.26-.73.26-1.37.18-1.5-.08-.13-.29-.21-.61-.37Z"/></svg>
  </a>';
}

$html .= '
  <script src="js/script.js" defer></script>
</body>
</html>';

file_put_contents($root . '/docs/index.html', $html);
echo "GENERADO: docs/index.html\n";
