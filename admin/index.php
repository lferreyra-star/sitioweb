<?php
/**
 * Dashboard del panel de administración.
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Auth;
use App\Repos\EducacionRepo;
use App\Repos\ExperienciaRepo;
use App\Repos\HabilidadRepo;
use App\Repos\MensajeRepo;
use App\Repos\ProyectoRepo;

Auth::exigirLogin();

$mensajesRepo    = new MensajeRepo();
$ultimosMensajes = $mensajesRepo->listar();

function iconoDash(string $tipo): string
{
    switch ($tipo) {
        case 'proyectos':
            return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>';
        case 'habilidades':
            return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>';
        case 'mensajes':
            return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>';
        case 'experiencia':
            return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>';
        case 'educacion':
            return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>';
        case 'recientes':
            return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>';
        case 'plus':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>';
        case 'edit':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>';
        case 'lock':
            return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>';
        case 'check':
            return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
        case 'inbox':
            return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg>';
        default:
            return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle></svg>';
    }
}

$estadisticas = [
    ['titulo' => 'Proyectos',     'valor' => (new ProyectoRepo())->contar(),      'href' => 'admin/proyectos.php',       'icono' => 'proyectos',   'color' => 'azul'],
    ['titulo' => 'Habilidades',   'valor' => (new HabilidadRepo())->contar(),     'href' => 'admin/habilidades.php',     'icono' => 'habilidades', 'color' => 'verde'],
    ['titulo' => 'Mensajes',      'valor' => $mensajesRepo->contar(),             'href' => 'admin/mensajes.php',        'icono' => 'mensajes',    'color' => 'cian', 'destacado' => $mensajesRepo->contarNoLeidos(), 'nota' => 'sin leer'],
    ['titulo' => 'Experiencia',   'valor' => (new ExperienciaRepo())->contar(),   'href' => 'admin/experiencia.php',     'icono' => 'experiencia', 'color' => 'ambar'],
    ['titulo' => 'Educación',     'valor' => (new EducacionRepo())->contar(),     'href' => 'admin/educacion.php',       'icono' => 'educacion',   'color' => 'morado'],
    ['titulo' => 'Últimos 7 días','valor' => $mensajesRepo->contarRecientes(7), 'href' => 'admin/mensajes.php',        'icono' => 'recientes',   'color' => 'rosa'],
];

$titulo = 'Dashboard';
$pagina = 'dashboard';
require __DIR__ . '/partials/inicio.php';
?>

<section class="admin__hero">
  <div class="admin__hero-texto">
    <h2>Bienvenido de nuevo</h2>
    <p>Revisa el estado de tu portafolio y gestiona el contenido desde un solo lugar.</p>
  </div>
  <div class="admin__hero-acciones">
    <a class="boton boton--primario" href="<?= e(url('admin/proyectos.php?nuevo=1')) ?>"><?= iconoDash('plus') ?> Nuevo proyecto</a>
    <a class="boton boton--fantasma" href="<?= e(url('admin/sitio.php')) ?>"><?= iconoDash('edit') ?> Editar sitio</a>
  </div>
</section>

<section class="admin__tarjetas">
  <?php foreach ($estadisticas as $dato): ?>
    <a class="tarjeta-estadistica tarjeta-estadistica--<?= e($dato['color']) ?>" href="<?= e(url($dato['href'])) ?>">
      <div class="tarjeta-estadistica__header">
        <div class="tarjeta-estadistica__icono"><?= iconoDash($dato['icono']) ?></div>
        <?php if (!empty($dato['destacado'])): ?>
          <span class="tarjeta-estadistica__badge"><?= (int) $dato['destacado'] ?> <?= e($dato['nota'] ?? 'sin leer') ?></span>
        <?php endif; ?>
      </div>
      <div class="tarjeta-estadistica__body">
        <strong class="tarjeta-estadistica__valor"><?= (int) $dato['valor'] ?></strong>
        <span class="tarjeta-estadistica__titulo"><?= e($dato['titulo']) ?></span>
      </div>
      <span class="tarjeta-estadistica__flecha">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
      </span>
    </a>
  <?php endforeach; ?>
</section>

<div class="admin__grid-2">
  <!-- Últimos mensajes -->
  <section class="caja caja--destacada">
    <header class="caja__cabecera">
      <div class="caja__cabecera-titulo">
        <div class="caja__icono"><?= iconoDash('inbox') ?></div>
        <div>
          <h2>Últimos mensajes</h2>
          <p>Buzón de contacto</p>
        </div>
      </div>
      <a class="boton boton--texto" href="<?= e(url('admin/mensajes.php')) ?>">Ver todos</a>
    </header>

    <?php if ($ultimosMensajes === []): ?>
      <div class="caja__vacia">
        <div class="caja__vacia-icono"><?= iconoDash('inbox') ?></div>
        <strong>Sin mensajes aún</strong>
        <p>Cuando recibas mensajes desde el formulario, aparecerán aquí.</p>
      </div>
    <?php else: ?>
      <ul class="lista-mensajes lista-mensajes--moderna">
        <?php foreach (array_slice($ultimosMensajes, 0, 5) as $m): ?>
          <li class="lista-mensajes__item <?= $m['leido'] ? '' : 'lista-mensajes__item--no-leido' ?>">
            <a href="<?= e(url('admin/mensajes.php?ver=' . (int) $m['id'])) ?>" class="lista-mensajes__enlace">
              <div class="lista-mensajes__avatar"><?= strtoupper(substr($m['nombre'], 0, 1)) ?></div>
              <div class="lista-mensajes__contenido">
                <div class="lista-mensajes__cabecera">
                  <strong><?= e($m['nombre']) ?></strong>
                  <time><?= e($m['creado_en']) ?></time>
                </div>
                <p><?= e(mb_substr(strip_tags($m['mensaje']), 0, 90)) ?><?= mb_strlen(strip_tags($m['mensaje'])) > 90 ? '…' : '' ?></p>
              </div>
              <?php if (!$m['leido']): ?>
                <span class="lista-mensajes__punto" aria-label="No leído"></span>
              <?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>

  <!-- Accesos rápidos -->
  <section class="caja">
    <header class="caja__cabecera">
      <div class="caja__cabecera-titulo">
        <div class="caja__icono"><?= iconoDash('edit') ?></div>
        <div>
          <h2>Acciones rápidas</h2>
          <p>Atajos más utilizados</p>
        </div>
      </div>
    </header>
    <div class="accesos-rapidos accesos-rapidos--moderno">
      <a class="acceso-rapido" href="<?= e(url('admin/proyectos.php?nuevo=1')) ?>">
        <div class="acceso-rapido__icono"><?= iconoDash('plus') ?></div>
        <div class="acceso-rapido__texto">
          <strong>Nuevo proyecto</strong>
          <span>Añadir al portafolio</span>
        </div>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
      </a>
      <a class="acceso-rapido" href="<?= e(url('admin/habilidades.php?nuevo=1')) ?>">
        <div class="acceso-rapido__icono"><?= iconoDash('plus') ?></div>
        <div class="acceso-rapido__texto">
          <strong>Nueva habilidad</strong>
          <span>Actualizar stack</span>
        </div>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
      </a>
      <a class="acceso-rapido" href="<?= e(url('admin/sitio.php')) ?>">
        <div class="acceso-rapido__icono"><?= iconoDash('edit') ?></div>
        <div class="acceso-rapido__texto">
          <strong>Editar textos</strong>
          <span>Hero, Sobre mí, SEO</span>
        </div>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
      </a>
      <a class="acceso-rapido" href="<?= e(url('admin/seguridad.php')) ?>">
        <div class="acceso-rapido__icono"><?= iconoDash('lock') ?></div>
        <div class="acceso-rapido__texto">
          <strong>Cambiar contraseña</strong>
          <span>Seguridad de cuenta</span>
        </div>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
      </a>
    </div>

    <div class="sistema-estado">
      <header class="sistema-estado__cabecera">
        <h3>Estado del sistema</h3>
      </header>
      <ul class="sistema-estado__lista">
        <li>
          <span>Base de datos</span>
          <span class="estado estado--ok"><?= iconoDash('check') ?> SQLite · Conectada</span>
        </li>
        <li>
          <span>Versión PHP</span>
          <span class="estado"><?= e(PHP_VERSION) ?></span>
        </li>
        <li>
          <span>Sesión activa</span>
          <span class="estado estado--ok"><?= iconoDash('check') ?> Verificada</span>
        </li>
      </ul>
    </div>
  </section>
</div>

<?php require __DIR__ . '/partials/fin.php'; ?>
