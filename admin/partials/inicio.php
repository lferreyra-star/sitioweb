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
    'dashboard'   => ['admin/',                    'dashboard',   'Dashboard',   0],
    'proyectos'   => ['admin/proyectos.php',       'folder',      'Proyectos',   0],
    'habilidades' => ['admin/habilidades.php',     'code',        'Habilidades', 0],
    'educacion'   => ['admin/educacion.php',       'graduation',  'Educación',   0],
    'experiencia' => ['admin/experiencia.php',     'briefcase',   'Experiencia', 0],
    'mensajes'    => ['admin/mensajes.php',        'mail',        'Mensajes',    $noLeidos],
    'sitio'       => ['admin/sitio.php',           'settings',    'Sitio',       0],
    'seguridad'   => ['admin/seguridad.php',       'shield',      'Seguridad',   0],
];

function iconoAdmin(string $tipo): string
{
    switch ($tipo) {
        case 'dashboard':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>';
        case 'folder':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>';
        case 'code':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>';
        case 'graduation':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>';
        case 'briefcase':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>';
        case 'mail':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>';
        case 'settings':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>';
        case 'shield':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>';
        case 'eye':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
        case 'logout':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>';
        case 'crown':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4l3 12h14l3-12-6 7-4-7-4 7-6-7zm3 16h14"></path></svg>';
        default:
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle></svg>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow, noarchive">
  <meta name="theme-color" content="#0b1120">
  <title><?= e($titulo ?? 'Panel') ?> | Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('css/admin.css')) ?>">
</head>
<body class="admin">
  <a class="admin__salto" href="#contenido">Saltar al contenido</a>

  <div class="admin__app">

    <!-- Navegación lateral -->
    <aside class="admin__sidebar" id="adminSidebar">
      <div class="admin__marca">
        <div class="admin__marca-icono" aria-hidden="true"><?= iconoAdmin('crown') ?></div>
        <div>
          <strong>Portafolio Admin</strong>
          <small>Luis Fabiano Ferreyra</small>
        </div>
      </div>

      <nav class="admin__nav" aria-label="Menú principal">
        <div class="admin__nav-titulo">Navegación</div>
        <ul>
          <?php foreach ($menu as $clave => [$href, $icono, $etiqueta, $badge]): ?>
            <li>
              <a href="<?= e(url($href)) ?>" class="<?= $clave === $activo ? 'activo' : '' ?>">
                <span class="admin__nav-icono" aria-hidden="true"><?= iconoAdmin($icono) ?></span>
                <span class="admin__nav-texto"><?= e($etiqueta) ?></span>
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
          <span class="admin__nav-icono" aria-hidden="true"><?= iconoAdmin('eye') ?></span>
          <span class="admin__nav-texto">Ver sitio web</span>
        </a>
        <form method="post" action="<?= e(url('logout.php')) ?>">
          <?= csrf_campo() ?>
          <button type="submit">
            <span class="admin__nav-icono" aria-hidden="true"><?= iconoAdmin('logout') ?></span>
            <span class="admin__nav-texto">Cerrar sesión</span>
          </button>
        </form>
        <div class="admin__usuario">
          <div class="admin__usuario-avatar">
            <?= strtoupper(substr($usuarioActual['nombre'] ?? 'A', 0, 1)) ?>
          </div>
          <div class="admin__usuario-info">
            <strong><?= e($usuarioActual['nombre'] ?? 'Administrador') ?></strong>
            <small><?= e($usuarioActual['usuario'] ?? 'admin') ?></small>
          </div>
        </div>
      </div>
    </aside>

    <!-- Panel principal -->
    <div class="admin__panel">
      <header class="admin__topbar">
        <button class="admin__toggle" id="adminToggle" aria-label="Abrir menú" aria-expanded="false">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div class="admin__topbar-info">
          <h1 class="admin__titulo"><?= e($titulo ?? 'Panel de administración') ?></h1>
          <p class="admin__subtitulo">Gestiona el contenido de tu portafolio</p>
        </div>
        <div class="admin__topbar-acciones">
          <span class="admin__hora" id="adminHora"></span>
        </div>
      </header>

      <main class="admin__contenido" id="contenido">
        <?php
        $flash = flash_html();
        if ($flash !== ''):
        ?>
          <div class="alertas"><?= $flash ?></div>
        <?php endif; ?>
