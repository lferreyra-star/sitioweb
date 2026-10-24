<?php
/**
 * Cabecera del panel de administración.
 *
 * Requiere que se haya cargado bootstrap.php y llamado a Auth::exigirLogin().
 * Variables esperadas:
 *   $titulo (string) – título de la página.
 *   $pagina (string) – clave del ítem activo del menú.
 */

use App\Auth;
use App\Repos\MensajeRepo;

$usuarioActual = Auth::usuario();
$noLeidos      = (new MensajeRepo())->contarNoLeidos();
$activo        = $pagina ?? '';

$menu = [
    'dashboard'   => ['admin/',                    '📊', 'Dashboard',   0],
    'proyectos'   => ['admin/proyectos.php',       '🚀', 'Proyectos',   0],
    'habilidades' => ['admin/habilidades.php',     '💡', 'Habilidades', 0],
    'educacion'   => ['admin/educacion.php',       '🎓', 'Educación',   0],
    'experiencia' => ['admin/experiencia.php',     '💼', 'Experiencia', 0],
    'mensajes'    => ['admin/mensajes.php',        '✉️', 'Mensajes',    $noLeidos],
    'sitio'       => ['admin/sitio.php',           '⚙️', 'Sitio',       0],
    'seguridad'   => ['admin/seguridad.php',       '🔒', 'Seguridad',   0],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow, noarchive">
  <meta name="theme-color" content="#0b1120">
  <title><?= e($titulo ?? 'Panel') ?> | Admin</title>
  <link rel="stylesheet" href="<?= e(url('css/admin.css')) ?>">
</head>
<body class="admin">
  <a class="admin__salto" href="#contenido">Saltar al contenido</a>

  <div class="admin__app">

    <!-- Navegación lateral -->
    <aside class="admin__sidebar" id="adminSidebar">
      <div class="admin__marca">
        <span class="admin__marca-icono" aria-hidden="true">&#128081;</span>
        <div>
          <strong>Luis Fabiano</strong>
          <small>Panel de administración</small>
        </div>
      </div>

      <nav class="admin__nav" aria-label="Menú principal">
        <ul>
          <?php foreach ($menu as $clave => [$href, $icono, $etiqueta, $badge]): ?>
            <li>
              <a href="<?= e(url($href)) ?>" class="<?= $clave === $activo ? 'activo' : '' ?>">
                <span class="admin__nav-icono" aria-hidden="true"><?= $icono ?></span>
                <?= e($etiqueta) ?>
                <?php if ($badge > 0): ?>
                  <span class="admin__badge"><?= (int) $badge ?></span>
                <?php endif; ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <div class="admin__pie">
        <a href="<?= e(url('index.php')) ?>" target="_blank" rel="noopener">
          <span class="admin__nav-icono" aria-hidden="true">&#128065;</span> Ver sitio
        </a>
        <form method="post" action="<?= e(url('logout.php')) ?>">
          <?= csrf_campo() ?>
          <button type="submit">
            <span class="admin__nav-icono" aria-hidden="true">&#9211;</span> Cerrar sesión
          </button>
        </form>
      </div>
    </aside>

    <!-- Contenido -->
    <div class="admin__panel">
      <header class="admin__topbar">
        <button class="admin__toggle" id="adminToggle" aria-label="Abrir o cerrar menú" aria-expanded="false">&#9776;</button>
        <h1 class="admin__titulo"><?= e($titulo ?? 'Panel') ?></h1>
        <div class="admin__usuario">
          <span class="admin__avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($usuarioActual['nombre'] ?? 'A', 0, 1))) ?></span>
          <span class="admin__usuario-nombre"><?= e($usuarioActual['nombre'] ?? '') ?></span>
        </div>
      </header>

      <main class="admin__contenido" id="contenido">
        <div class="alertas" aria-live="polite"><?= flash_html() ?></div>
