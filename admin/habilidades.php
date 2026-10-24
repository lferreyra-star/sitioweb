<?php
/**
 * Gestión de habilidades: crear, editar y eliminar.
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Auth;
use App\Repos\HabilidadRepo;

Auth::exigirLogin();
verificar_csrf();

$repo = new HabilidadRepo();

/* ------------------------- Procesar POST (PRG) ------------------------- */
if (es_post()) {
    $accion = post('accion');
    $id     = (int) post('id');

    if ($accion === 'eliminar') {
        $repo->eliminar($id);
        flash('exito', 'Habilidad eliminada.');
        ir_a('admin/habilidades.php');
    }

    if ($accion === 'crear' || $accion === 'editar') {
        $nombre     = post('nombre');
        $icono      = post('icono');
        $porcentaje = filter_var(post('porcentaje'), FILTER_VALIDATE_INT);
        $orden      = filter_var(post('orden'), FILTER_VALIDATE_INT);
        $destino    = $accion === 'editar' ? '?editar=' . $id : '?nuevo=1';

        if ($nombre === '' || mb_strlen($nombre) > 60) {
            flash('error', 'Indica un nombre de hasta 60 caracteres.');
            ir_a('admin/habilidades.php' . $destino);
        }
        if ($porcentaje === false || $porcentaje < 0 || $porcentaje > 100) {
            flash('error', 'El porcentaje debe estar entre 0 y 100.');
            ir_a('admin/habilidades.php' . $destino);
        }

        $datos = [
            'nombre'     => $nombre,
            'icono'      => $icono,
            'porcentaje' => $porcentaje,
            'orden'      => $orden === false ? 0 : $orden,
        ];

        if ($accion === 'crear') {
            $repo->crear($datos);
            flash('exito', 'Habilidad creada.');
        } else {
            $repo->actualizar($id, $datos);
            flash('exito', 'Habilidad actualizada.');
        }
        ir_a('admin/habilidades.php');
    }
}

/* --------------------------- Preparar datos ---------------------------- */
$editar = null;
if (isset($_GET['editar'])) {
    $editar = $repo->obtener((int) $_GET['editar']);
    if ($editar === null) {
        flash('error', 'La habilidad indicada no existe.');
        ir_a('admin/habilidades.php');
    }
}
$mostrarFormulario = $editar !== null || isset($_GET['nuevo']);
$filas = $repo->listar();

$titulo = 'Habilidades';
$pagina = 'habilidades';
require __DIR__ . '/partials/inicio.php';
?>

<?php if ($mostrarFormulario): ?>
  <section class="caja">
    <header class="caja__cabecera">
      <h2><?= $editar ? 'Editar habilidad' : 'Nueva habilidad' ?></h2>
      <a class="boton boton--texto" href="<?= e(url('admin/habilidades.php')) ?>">Cancelar</a>
    </header>

    <form class="form-admin" method="post" action="<?= e(url('admin/habilidades.php')) ?>">
      <?= csrf_campo() ?>
      <input type="hidden" name="accion" value="<?= $editar ? 'editar' : 'crear' ?>">
      <input type="hidden" name="id" value="<?= (int) ($editar['id'] ?? 0) ?>">

      <div class="form-admin__grid">
        <label>
          <span>Nombre *</span>
          <input type="text" name="nombre" required maxlength="60"
                 placeholder="p. ej. JavaScript" value="<?= e($editar['nombre'] ?? '') ?>">
        </label>
        <label>
          <span>Icono (emoji)</span>
          <input type="text" name="icono" maxlength="8" placeholder="💻" value="<?= e($editar['icono'] ?? '') ?>">
        </label>
        <label>
          <span>Porcentaje (0-100) *</span>
          <input type="number" name="porcentaje" min="0" max="100" required
                 value="<?= e($editar['porcentaje'] ?? '80') ?>">
        </label>
        <label>
          <span>Orden</span>
          <input type="number" name="orden" min="0" max="999" value="<?= e($editar['orden'] ?? '0') ?>">
        </label>
      </div>

      <div class="form-admin__acciones">
        <button type="submit" class="boton boton--primario"><?= $editar ? 'Guardar cambios' : 'Crear habilidad' ?></button>
        <a class="boton boton--texto" href="<?= e(url('admin/habilidades.php')) ?>">Cancelar</a>
      </div>
    </form>
  </section>
<?php endif; ?>

<section class="caja">
  <header class="caja__cabecera">
    <h2>Listado (<?= count($filas) ?>)</h2>
    <?php if (!$mostrarFormulario): ?>
      <a class="boton boton--primario" href="<?= e(url('admin/habilidades.php?nuevo=1')) ?>">+ Nueva</a>
    <?php endif; ?>
  </header>

  <?php if ($filas === []): ?>
    <p class="caja__vacia">No hay habilidades registradas todavía.</p>
  <?php else: ?>
    <div class="tabla-contenedor">
      <table class="tabla">
        <thead>
          <tr>
            <th>Orden</th><th>Icono</th><th>Nombre</th><th>Porcentaje</th><th class="tabla__acciones">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($filas as $fila): ?>
            <tr>
              <td><?= (int) $fila['orden'] ?></td>
              <td class="tabla__icono" aria-hidden="true"><?= e($fila['icono']) ?></td>
              <td><?= e($fila['nombre']) ?></td>
              <td>
                <div class="barra-mini">
                  <div class="barra-mini__relleno" style="width: <?= (int) $fila['porcentaje'] ?>%"></div>
                  <span><?= (int) $fila['porcentaje'] ?>%</span>
                </div>
              </td>
              <td class="tabla__acciones">
                <a class="boton boton--mini boton--editar"
                   href="<?= e(url('admin/habilidades.php?editar=' . (int) $fila['id'])) ?>">Editar</a>
                <form method="post" action="<?= e(url('admin/habilidades.php')) ?>" class="form-inline"
                      data-confirm="¿Eliminar la habilidad «<?= e($fila['nombre']) ?>»?">
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

<?php require __DIR__ . '/partials/fin.php'; ?>
