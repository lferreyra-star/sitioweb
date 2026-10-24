<?php
/**
 * Buzón de mensajes del formulario de contacto.
 *
 *  - lista todos los mensajes
 *  - ?ver=ID muestra el detalle y lo marca como leído
 *  - permite marcar como no leído y eliminar
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Auth;
use App\Repos\MensajeRepo;

Auth::exigirLogin();
verificar_csrf();

$repo = new MensajeRepo();

/* ------------------------- Procesar POST (PRG) ------------------------- */
if (es_post()) {
    $accion = post('accion');
    $id     = (int) post('id');

    if ($accion === 'eliminar') {
        $repo->eliminar($id);
        flash('exito', 'Mensaje eliminado.');
        ir_a('admin/mensajes.php');
    }

    if ($accion === 'leido') {
        $repo->marcarLeido($id);
        flash('exito', 'Mensaje marcado como leído.');
        ir_a('admin/mensajes.php?ver=' . $id);
    }

    if ($accion === 'no-leido') {
        $repo->marcarNoLeido($id);
        flash('exito', 'Mensaje marcado como no leído.');
        ir_a('admin/mensajes.php');
    }
}

/* --------------------------- Preparar datos ---------------------------- */
$ver = null;
if (isset($_GET['ver'])) {
    $ver = $repo->obtener((int) $_GET['ver']);
    if ($ver === null) {
        flash('error', 'El mensaje indicado no existe.');
        ir_a('admin/mensajes.php');
    }
    if (!$ver['leido']) {
        $repo->marcarLeido((int) $ver['id']);
        $ver['leido'] = 1;
    }
}
$filas = $repo->listar();

$titulo = 'Mensajes';
$pagina = 'mensajes';
require __DIR__ . '/partials/inicio.php';
?>

<?php if ($ver !== null): ?>
  <!-- Detalle del mensaje -->
  <section class="caja">
    <header class="caja__cabecera">
      <h2>Mensaje de <?= e($ver['nombre']) ?></h2>
      <a class="boton boton--texto" href="<?= e(url('admin/mensajes.php')) ?>">&larr; Volver al buzón</a>
    </header>

    <article class="mensaje-detalle">
      <header class="mensaje-detalle__cabecera">
        <div>
          <strong><?= e($ver['nombre']) ?></strong>
          <a href="mailto:<?= e($ver['email']) ?>"><?= e($ver['email']) ?></a>
        </div>
        <time><?= e($ver['creado_en']) ?></time>
      </header>
      <div class="mensaje-detalle__cuerpo">
        <p><?= nl2br(e($ver['mensaje'])) ?></p>
      </div>
      <footer class="mensaje-detalle__pie">
        <span class="etiqueta">IP: <?= e($ver['ip'] !== '' ? $ver['ip'] : '—') ?></span>
        <div class="form-admin__acciones">
          <a class="boton boton--primario" href="mailto:<?= e($ver['email']) ?>?subject=Re: tu mensaje">Responder por correo</a>
          <form method="post" action="<?= e(url('admin/mensajes.php')) ?>" class="form-inline">
            <?= csrf_campo() ?>
            <input type="hidden" name="accion" value="no-leido">
            <input type="hidden" name="id" value="<?= (int) $ver['id'] ?>">
            <button type="submit" class="boton boton--secundario">Marcar como no leído</button>
          </form>
          <form method="post" action="<?= e(url('admin/mensajes.php')) ?>" class="form-inline"
                data-confirm="¿Eliminar este mensaje de forma permanente?">
            <?= csrf_campo() ?>
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="id" value="<?= (int) $ver['id'] ?>">
            <button type="submit" class="boton boton--eliminar">Eliminar</button>
          </form>
        </div>
      </footer>
    </article>
  </section>
<?php else: ?>
  <!-- Buzón -->
  <section class="caja">
    <header class="caja__cabecera">
      <h2>Buzón (<?= count($filas) ?>)</h2>
      <?php if ($filas !== []): ?>
        <span class="etiqueta etiqueta--ok"><?= $repo->contarNoLeidos() ?> sin leer</span>
      <?php endif; ?>
    </header>

    <?php if ($filas === []): ?>
      <p class="caja__vacia">No hay mensajes todavía. Cuando alguien use el formulario de contacto aparecerán aquí.</p>
    <?php else: ?>
      <div class="tabla-contenedor">
        <table class="tabla">
          <thead>
            <tr>
              <th>Estado</th><th>Remitente</th><th>Mensaje</th><th>Fecha</th><th class="tabla__acciones">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($filas as $fila): ?>
              <tr class="<?= $fila['leido'] ? '' : 'fila-no-leida' ?>">
                <td>
                  <span class="punto-estado <?= $fila['leido'] ? '' : 'punto-estado--nuevo' ?>"
                        title="<?= $fila['leido'] ? 'Leído' : 'Sin leer' ?>"></span>
                </td>
                <td>
                  <strong><?= e($fila['nombre']) ?></strong>
                  <small class="tabla__detalle"><?= e($fila['email']) ?></small>
                </td>
                <td class="tabla__detalle"><?= e(mb_substr($fila['mensaje'], 0, 100)) ?><?= mb_strlen($fila['mensaje']) > 100 ? '…' : '' ?></td>
                <td><span class="etiqueta"><?= e($fila['creado_en']) ?></span></td>
                <td class="tabla__acciones">
                  <a class="boton boton--mini boton--editar"
                     href="<?= e(url('admin/mensajes.php?ver=' . (int) $fila['id'])) ?>">Leer</a>
                  <form method="post" action="<?= e(url('admin/mensajes.php')) ?>" class="form-inline"
                        data-confirm="¿Eliminar el mensaje de <?= e($fila['nombre']) ?>?">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id" value="<?= (int) $fila['id'] ?>">
                    <button type="submit" class="boton boton--mini boton--eliminar">Eliminar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
<?php endif; ?>

<?php require __DIR__ . '/partials/fin.php'; ?>
