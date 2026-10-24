<?php
/**
 * Gestión de experiencia laboral: crear, editar y eliminar.
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Auth;
use App\Repos\ExperienciaRepo;

Auth::exigirLogin();
verificar_csrf();

$repo = new ExperienciaRepo();

/* ------------------------- Procesar POST (PRG) ------------------------- */
if (es_post()) {
    $accion = post('accion');
    $id     = (int) post('id');

    if ($accion === 'eliminar') {
        $repo->eliminar($id);
        flash('exito', 'Experiencia eliminada.');
        ir_a('admin/experiencia.php');
    }

    if ($accion === 'crear' || $accion === 'editar') {
        $destino = $accion === 'editar' ? '?editar=' . $id : '?nuevo=1';

        if (post('titulo') === '' || mb_strlen(post('titulo')) > 140) {
            flash('error', 'El cargo es obligatorio (máx. 140 caracteres).');
            ir_a('admin/experiencia.php' . $destino);
        }
        if (post('institucion') === '' || mb_strlen(post('institucion')) > 140) {
            flash('error', 'La empresa es obligatoria (máx. 140 caracteres).');
            ir_a('admin/experiencia.php' . $destino);
        }
        if (post('fecha') === '' || mb_strlen(post('fecha')) > 60) {
            flash('error', 'Indica un periodo (máx. 60 caracteres).');
            ir_a('admin/experiencia.php' . $destino);
        }

        $orden = filter_var(post('orden'), FILTER_VALIDATE_INT);
        $datos = [
            'titulo'      => post('titulo'),
            'institucion' => post('institucion'),
            'fecha'       => post('fecha'),
            'descripcion' => post('descripcion'),
            'orden'       => $orden === false ? 0 : $orden,
        ];

        if ($accion === 'crear') {
            $repo->crear($datos);
            flash('exito', 'Experiencia añadida.');
        } else {
            $repo->actualizar($id, $datos);
            flash('exito', 'Experiencia actualizada.');
        }
        ir_a('admin/experiencia.php');
    }
}

/* --------------------------- Preparar datos ---------------------------- */
$editar = null;
if (isset($_GET['editar'])) {
    $editar = $repo->obtener((int) $_GET['editar']);
    if ($editar === null) {
        flash('error', 'La experiencia indicada no existe.');
        ir_a('admin/experiencia.php');
    }
}
$mostrarFormulario = $editar !== null || isset($_GET['nuevo']);
$filas = $repo->listar();

$titulo = 'Experiencia laboral';
$pagina = 'experiencia';
require __DIR__ . '/partials/inicio.php';
?>

<?php if ($mostrarFormulario): ?>
  <section class="caja">
    <header class="caja__cabecera">
      <h2><?= $editar ? 'Editar experiencia' : 'Nueva experiencia' ?></h2>
      <a class="boton boton--texto" href="<?= e(url('admin/experiencia.php')) ?>">Cancelar</a>
    </header>

    <form class="form-admin" method="post" action="<?= e(url('admin/experiencia.php')) ?>">
      <?= csrf_campo() ?>
      <input type="hidden" name="accion" value="<?= $editar ? 'editar' : 'crear' ?>">
      <input type="hidden" name="id" value="<?= (int) ($editar['id'] ?? 0) ?>">

      <div class="form-admin__grid">
        <label>
          <span>Cargo *</span>
          <input type="text" name="titulo" required maxlength="140"
                 placeholder="p. ej. Desarrollador Front-End" value="<?= e($editar['titulo'] ?? '') ?>">
        </label>
        <label>
          <span>Empresa *</span>
          <input type="text" name="institucion" required maxlength="140"
                 placeholder="p. ej. TechNova Solutions" value="<?= e($editar['institucion'] ?? '') ?>">
        </label>
        <label>
          <span>Periodo *</span>
          <input type="text" name="fecha" required maxlength="60"
                 placeholder="p. ej. 2024 - Actualidad" value="<?= e($editar['fecha'] ?? '') ?>">
        </label>
        <label>
          <span>Orden</span>
          <input type="number" name="orden" min="0" max="999" value="<?= e($editar['orden'] ?? '0') ?>">
        </label>
        <label class="form-admin__ancho">
          <span>Descripción (opcional)</span>
          <textarea name="descripcion" rows="3" maxlength="400"
                    placeholder="Logros y responsabilidades"><?= e($editar['descripcion'] ?? '') ?></textarea>
        </label>
      </div>

      <div class="form-admin__acciones">
        <button type="submit" class="boton boton--primario"><?= $editar ? 'Guardar cambios' : 'Añadir experiencia' ?></button>
        <a class="boton boton--texto" href="<?= e(url('admin/experiencia.php')) ?>">Cancelar</a>
      </div>
    </form>
  </section>
<?php endif; ?>

<section class="caja">
  <header class="caja__cabecera">
    <h2>Listado (<?= count($filas) ?>)</h2>
    <?php if (!$mostrarFormulario): ?>
      <a class="boton boton--primario" href="<?= e(url('admin/experiencia.php?nuevo=1')) ?>">+ Nueva</a>
    <?php endif; ?>
  </header>

  <?php if ($filas === []): ?>
    <p class="caja__vacia">Todavía no hay experiencia registrada.</p>
  <?php else: ?>
    <div class="tabla-contenedor">
      <table class="tabla">
        <thead>
          <tr>
            <th>Orden</th><th>Periodo</th><th>Cargo</th><th>Empresa</th><th class="tabla__acciones">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($filas as $fila): ?>
            <tr>
              <td><?= (int) $fila['orden'] ?></td>
              <td><span class="etiqueta"><?= e($fila['fecha']) ?></span></td>
              <td><?= e($fila['titulo']) ?></td>
              <td><?= e($fila['institucion']) ?></td>
              <td class="tabla__acciones">
                <a class="boton boton--mini boton--editar"
                   href="<?= e(url('admin/experiencia.php?editar=' . (int) $fila['id'])) ?>">Editar</a>
                <form method="post" action="<?= e(url('admin/experiencia.php')) ?>" class="form-inline"
                      data-confirm="¿Eliminar «<?= e($fila['titulo']) ?>» de <?= e($fila['institucion']) ?>?">
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
