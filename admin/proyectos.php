<?php
/**
 * Gestión de proyectos: crear, editar y eliminar.
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Auth;
use App\Repos\ProyectoRepo;

Auth::exigirLogin();
verificar_csrf();

$repo = new ProyectoRepo();

/* ------------------------- Procesar POST (PRG) ------------------------- */
if (es_post()) {
    $accion = post('accion');
    $id     = (int) post('id');

    if ($accion === 'eliminar') {
        $repo->eliminar($id);
        flash('exito', 'Proyecto eliminado.');
        ir_a('admin/proyectos.php');
    }

    if ($accion === 'crear' || $accion === 'editar') {
        $tituloP    = post('titulo');
        $descripcion = post('descripcion');
        $enlace     = post('enlace');
        $orden      = filter_var(post('orden'), FILTER_VALIDATE_INT);
        $destino    = $accion === 'editar' ? '?editar=' . $id : '?nuevo=1';

        if ($tituloP === '' || mb_strlen($tituloP) > 120) {
            flash('error', 'El título es obligatorio (máx. 120 caracteres).');
            ir_a('admin/proyectos.php' . $destino);
        }
        if ($descripcion === '' || mb_strlen($descripcion) > 500) {
            flash('error', 'La descripción es obligatoria (máx. 500 caracteres).');
            ir_a('admin/proyectos.php' . $destino);
        }
        if ($enlace !== '' && !filter_var($enlace, FILTER_VALIDATE_URL)) {
            flash('error', 'El enlace debe ser una URL válida (https://...).');
            ir_a('admin/proyectos.php' . $destino);
        }

        $datos = [
            'titulo'      => $tituloP,
            'descripcion' => $descripcion,
            'icono'       => post('icono'),
            'enlace'      => $enlace,
            'categoria'   => post('categoria', 'web'),
            'tecnologias' => post('tecnologias'),
            'orden'       => $orden === false ? 0 : $orden,
        ];

        if ($accion === 'crear') {
            $repo->crear($datos);
            flash('exito', 'Proyecto creado.');
        } else {
            $repo->actualizar($id, $datos);
            flash('exito', 'Proyecto actualizado.');
        }
        ir_a('admin/proyectos.php');
    }
}

/* --------------------------- Preparar datos ---------------------------- */
$editar = null;
if (isset($_GET['editar'])) {
    $editar = $repo->obtener((int) $_GET['editar']);
    if ($editar === null) {
        flash('error', 'El proyecto indicado no existe.');
        ir_a('admin/proyectos.php');
    }
}
$mostrarFormulario = $editar !== null || isset($_GET['nuevo']);
$filas = $repo->listar();
$categorias = ['web' => 'Sitio web', 'app' => 'Aplicación', 'otro' => 'Otro'];

$titulo = 'Proyectos';
$pagina = 'proyectos';
require __DIR__ . '/partials/inicio.php';
?>

<?php if ($mostrarFormulario): ?>
  <section class="caja">
    <header class="caja__cabecera">
      <h2><?= $editar ? 'Editar proyecto' : 'Nuevo proyecto' ?></h2>
      <a class="boton boton--texto" href="<?= e(url('admin/proyectos.php')) ?>">Cancelar</a>
    </header>

    <form class="form-admin" method="post" action="<?= e(url('admin/proyectos.php')) ?>">
      <?= csrf_campo() ?>
      <input type="hidden" name="accion" value="<?= $editar ? 'editar' : 'crear' ?>">
      <input type="hidden" name="id" value="<?= (int) ($editar['id'] ?? 0) ?>">

      <div class="form-admin__grid">
        <label>
          <span>Título *</span>
          <input type="text" name="titulo" required maxlength="120"
                 placeholder="p. ej. Landing Page Moderna" value="<?= e($editar['titulo'] ?? '') ?>">
        </label>
        <label>
          <span>Icono (emoji)</span>
          <input type="text" name="icono" maxlength="8" placeholder="🚀" value="<?= e($editar['icono'] ?? '') ?>">
        </label>
        <label>
          <span>Categoría</span>
          <select name="categoria">
            <?php foreach ($categorias as $clave => $etiqueta): ?>
              <option value="<?= e($clave) ?>" <?= ($editar['categoria'] ?? 'web') === $clave ? 'selected' : '' ?>>
                <?= e($etiqueta) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          <span>Enlace (URL, opcional)</span>
          <input type="url" name="enlace" maxlength="300" placeholder="https://..."
                 value="<?= e($editar['enlace'] ?? '') ?>">
        </label>
        <label class="form-admin__ancho">
          <span>Descripción *</span>
          <textarea name="descripcion" rows="3" required maxlength="500"
                    placeholder="Describe el proyecto"><?= e($editar['descripcion'] ?? '') ?></textarea>
        </label>
        <label class="form-admin__ancho">
          <span>Tecnologías (separadas por coma)</span>
          <input type="text" name="tecnologias" maxlength="200" placeholder="HTML5, CSS3, JS"
                 value="<?= e($editar['tecnologias'] ?? '') ?>">
        </label>
        <label>
          <span>Orden</span>
          <input type="number" name="orden" min="0" max="999" value="<?= e($editar['orden'] ?? '0') ?>">
        </label>
      </div>

      <div class="form-admin__acciones">
        <button type="submit" class="boton boton--primario"><?= $editar ? 'Guardar cambios' : 'Crear proyecto' ?></button>
        <a class="boton boton--texto" href="<?= e(url('admin/proyectos.php')) ?>">Cancelar</a>
      </div>
    </form>
  </section>
<?php endif; ?>

<section class="caja">
  <header class="caja__cabecera">
    <h2>Listado (<?= count($filas) ?>)</h2>
    <?php if (!$mostrarFormulario): ?>
      <a class="boton boton--primario" href="<?= e(url('admin/proyectos.php?nuevo=1')) ?>">+ Nuevo</a>
    <?php endif; ?>
  </header>

  <?php if ($filas === []): ?>
    <p class="caja__vacia">No hay proyectos todavía. ¡Crea el primero!</p>
  <?php else: ?>
    <div class="tabla-contenedor">
      <table class="tabla">
        <thead>
          <tr>
            <th>#</th><th>Proyecto</th><th>Categoría</th><th>Tecnologías</th><th class="tabla__acciones">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($filas as $i => $fila): ?>
            <tr>
              <td><?= (int) $fila['id'] ?></td>
              <td>
                <strong><?= e($fila['icono']) ?> <?= e($fila['titulo']) ?></strong>
                <small class="tabla__detalle"><?= e(mb_substr($fila['descripcion'], 0, 90)) ?><?= mb_strlen($fila['descripcion']) > 90 ? '…' : '' ?></small>
              </td>
              <td><span class="etiqueta etiqueta--<?= e($fila['categoria']) ?>"><?= e($fila['categoria']) ?></span></td>
              <td class="tabla__detalle"><?= e($fila['tecnologias']) ?></td>
              <td class="tabla__acciones">
                <a class="boton boton--mini boton--editar"
                   href="<?= e(url('admin/proyectos.php?editar=' . (int) $fila['id'])) ?>">Editar</a>
                <form method="post" action="<?= e(url('admin/proyectos.php')) ?>" class="form-inline"
                      data-confirm="¿Eliminar el proyecto «<?= e($fila['titulo']) ?>»?">
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
