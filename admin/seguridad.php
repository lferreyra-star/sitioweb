<?php
/**
 * Seguridad: cambio de contraseña y datos de la cuenta de administrador.
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Auth;
use App\Database;

Auth::exigirLogin();
verificar_csrf();

$db = Database::conexion();

/* ------------------------- Procesar POST (PRG) ------------------------- */
if (es_post() && post('accion') === 'actualizar') {
    $nombre = post('nombre');
    $email  = post('email');

    if ($nombre === '' || mb_strlen($nombre) > 120) {
        flash('error', 'El nombre es obligatorio (máx. 120 caracteres).');
        ir_a('admin/seguridad.php');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Introduce un correo electrónico válido.');
        ir_a('admin/seguridad.php');
    }

    $stmt = $db->prepare('UPDATE usuarios SET nombre = :nombre, email = :email WHERE id = :id');
    $stmt->execute([':nombre' => $nombre, ':email' => $email, ':id' => (int) $_SESSION['usuario_id']]);
    $_SESSION['usuario_nombre'] = $nombre;

    flash('exito', 'Datos de la cuenta actualizados.');
    ir_a('admin/seguridad.php');
}

if (es_post() && post('accion') === 'cambiar-clave') {
    $actual  = post('actual');
    $nueva   = post('nueva');
    $repetir = post('repetir');

    $fila = $db->prepare('SELECT password_hash FROM usuarios WHERE id = :id');
    $fila->execute([':id' => (int) $_SESSION['usuario_id']]);
    $hashActual = (string) $fila->fetchColumn();

    if ($actual === '' || !password_verify($actual, $hashActual)) {
        flash('error', 'Tu contraseña actual no es correcta.');
        ir_a('admin/seguridad.php');
    }
    if (mb_strlen($nueva) < 8) {
        flash('error', 'La nueva contraseña debe tener al menos 8 caracteres.');
        ir_a('admin/seguridad.php');
    }
    if ($nueva !== $repetir) {
        flash('error', 'La confirmación no coincide con la nueva contraseña.');
        ir_a('admin/seguridad.php');
    }
    if ($nueva === $actual) {
        flash('error', 'La nueva contraseña debe ser distinto de la actual.');
        ir_a('admin/seguridad.php');
    }

    $stmt = $db->prepare('UPDATE usuarios SET password_hash = :hash WHERE id = :id');
    $stmt->execute([
        ':hash' => password_hash($nueva, PASSWORD_DEFAULT),
        ':id'   => (int) $_SESSION['usuario_id'],
    ]);

    // Invalida todas las cookies persistentes tras cambiar la contraseña.
    \App\Auth::revocarRecuerdos((int) $_SESSION['usuario_id']);
    \App\Auth::olvidarRecuerdo();

    session_regenerate_id(true);
    flash('exito', 'Contraseña cambiada correctamente.');
    ir_a('admin/seguridad.php');
}

/* --------------------------- Preparar datos ---------------------------- */
$usuarioActual = Auth::usuario();

$titulo = 'Seguridad de la cuenta';
$pagina = 'seguridad';
require __DIR__ . '/partials/inicio.php';
?>

<div class="admin__grid-2">

  <!-- Datos de la cuenta -->
  <section class="caja">
    <header class="caja__cabecera">
      <h2>Datos de la cuenta</h2>
    </header>

    <form class="form-admin" method="post" action="<?= e(url('admin/seguridad.php')) ?>">
      <?= csrf_campo() ?>
      <input type="hidden" name="accion" value="actualizar">

      <div class="form-admin__grid">
        <label>
          <span>Usuario (no editable)</span>
          <input type="text" value="<?= e($usuarioActual['usuario'] ?? '') ?>" disabled>
        </label>
        <label>
          <span>Nombre *</span>
          <input type="text" name="nombre" required maxlength="120"
                 value="<?= e($usuarioActual['nombre'] ?? '') ?>">
        </label>
        <label class="form-admin__ancho">
          <span>Correo electrónico *</span>
          <input type="email" name="email" required maxlength="190"
                 value="<?= e($usuarioActual['email'] ?? '') ?>">
        </label>
      </div>

      <div class="form-admin__acciones">
        <button type="submit" class="boton boton--primario">Guardar datos</button>
      </div>
    </form>
  </section>

  <!-- Cambio de contraseña -->
  <section class="caja">
    <header class="caja__cabecera">
      <h2>Cambiar contraseña</h2>
    </header>

    <form class="form-admin" method="post" action="<?= e(url('admin/seguridad.php')) ?>">
      <?= csrf_campo() ?>
      <input type="hidden" name="accion" value="cambiar-clave">

      <div class="form-admin__grid">
        <label class="form-admin__ancho">
          <span>Contraseña actual *</span>
          <input type="password" name="actual" required maxlength="200" autocomplete="current-password">
        </label>
        <label>
          <span>Nueva contraseña * (mín. 8)</span>
          <input type="password" name="nueva" required minlength="8" maxlength="200" autocomplete="new-password">
        </label>
        <label>
          <span>Repetir nueva contraseña *</span>
          <input type="password" name="repetir" required minlength="8" maxlength="200" autocomplete="new-password">
        </label>
      </div>

      <div class="form-admin__acciones">
        <button type="submit" class="boton boton--primario">Cambiar contraseña</button>
      </div>
    </form>

    <p class="caja__nota">
      Usa una contraseña larga con letras, números y símbolos. Si olvidas la contraseña
      actual, vuelve a ejecutar el archivo <code>instalar.php</code> de emergencia o edita
      la base <code>storage/portafolio.sqlite</code> (ver README).
    </p>
  </section>
</div>

<?php require __DIR__ . '/partials/fin.php'; ?>
