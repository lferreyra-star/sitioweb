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

$mensajesRepo  = new MensajeRepo();
$ultimosMensajes = $mensajesRepo->listar();

$estadisticas = [
    ['titulo' => 'Proyectos',     'valor' => (new ProyectoRepo())->contar(),      'href' => 'admin/proyectos.php',       'icono' => '🚀'],
    ['titulo' => 'Habilidades',   'valor' => (new HabilidadRepo())->contar(),     'href' => 'admin/habilidades.php',     'icono' => '💡'],
    ['titulo' => 'Mensajes',      'valor' => $mensajesRepo->contar(),             'href' => 'admin/mensajes.php',        'icono' => '✉️', 'destacado' => $mensajesRepo->contarNoLeidos(), 'nota' => 'sin leer'],
    ['titulo' => 'Experiencias',  'valor' => (new ExperienciaRepo())->contar(),   'href' => 'admin/experiencia.php',     'icono' => '💼'],
    ['titulo' => 'Educación',     'valor' => (new EducacionRepo())->contar(),     'href' => 'admin/educacion.php',       'icono' => '🎓'],
    ['titulo' => 'Mensajes 7 días', 'valor' => $mensajesRepo->contarRecientes(7), 'href' => 'admin/mensajes.php',        'icono' => '📈'],
];

$titulo = 'Dashboard';
$pagina = 'dashboard';
require __DIR__ . '/partials/inicio.php';
?>

<section class="admin__tarjetas">
  <?php foreach ($estadisticas as $dato): ?>
    <a class="tarjeta-estadistica" href="<?= e(url($dato['href'])) ?>">
      <span class="tarjeta-estadistica__icono" aria-hidden="true"><?= $dato['icono'] ?></span>
      <div>
        <strong><?= (int) $dato['valor'] ?></strong>
        <span><?= e($dato['titulo']) ?></span>
        <?php if (!empty($dato['destacado'])): ?>
          <em><?= (int) $dato['destacado'] ?> <?= e($dato['nota'] ?? '') ?></em>
        <?php endif; ?>
      </div>
    </a>
  <?php endforeach; ?>
</section>

<div class="admin__grid-2">
  <!-- Últimos mensajes -->
  <section class="caja">
    <header class="caja__cabecera">
      <h2>Últimos mensajes</h2>
      <a class="boton boton--texto" href="<?= e(url('admin/mensajes.php')) ?>">Ver todos</a>
    </header>

    <?php if ($ultimosMensajes === []): ?>
      <p class="caja__vacia">Todavía no has recibido mensajes.</p>
    <?php else: ?>
      <ul class="lista-mensajes">
        <?php foreach (array_slice($ultimosMensajes, 0, 5) as $m): ?>
          <li class="<?= $m['leido'] ? '' : 'no-leido' ?>">
            <a href="<?= e(url('admin/mensajes.php?ver=' . (int) $m['id'])) ?>">
              <strong><?= e($m['nombre']) ?></strong>
              <span><?= e(mb_substr($m['mensaje'], 0, 80)) ?><?= mb_strlen($m['mensaje']) > 80 ? '…' : '' ?></span>
              <small><?= e($m['creado_en']) ?></small>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>

  <!-- Accesos rápidos -->
  <section class="caja">
    <header class="caja__cabecera">
      <h2>Acciones rápidas</h2>
    </header>
    <div class="accesos-rapidos">
      <a class="boton boton--primario" href="<?= e(url('admin/proyectos.php?nuevo=1')) ?>">+ Nuevo proyecto</a>
      <a class="boton boton--secundario" href="<?= e(url('admin/habilidades.php?nuevo=1')) ?>">+ Nueva habilidad</a>
      <a class="boton boton--secundario" href="<?= e(url('admin/sitio.php')) ?>">Editar textos del sitio</a>
      <a class="boton boton--secundario" href="<?= e(url('admin/seguridad.php')) ?>">Cambiar contraseña</a>
    </div>

    <div class="atajos-teclado">
      <h3>Estado del sistema</h3>
      <ul>
        <li><span>Base de datos</span> <em class="ok">SQLite · conectada</em></li>
        <li><span>PHP</span> <em><?= e(PHP_VERSION) ?></em></li>
        <li><span>Último acceso</span> <em><?= e(date('d/m/Y H:i')) ?></em></li>
      </ul>
    </div>
  </section>
</div>

<?php require __DIR__ . '/partials/fin.php'; ?>
