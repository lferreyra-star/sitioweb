<?php
/**
 * Restablecer contraseña mediante token.
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Auth;
use App\Database;

if (Auth::check()) {
    ir_a('admin/');
}

$token = (string) ($_GET['token'] ?? '');
$tokenHash = hash('sha256', $token);
$valido = false;
$usuarioId = 0;
$errores = [];

if ($token === '') {
    flash('error', 'El enlace de restablecimiento no es válido.');
    ir_a('login.php');
}

$stmt = Database::conexion()->prepare('SELECT * FROM resets WHERE token_hash = ? LIMIT 1');
$stmt->execute([$tokenHash]);
$fila = $stmt->fetch();

if ($fila) {
    if ((int) $fila['usado'] === 0 && strtotime((string) $fila['expira_en']) >= time()) {
        $valido = true;
        $usuarioId = (int) $fila['usuario_id'];
    }
}

if (!$valido) {
    flash('error', 'El enlace ha caducado, ya fue usado o no es válido.');
    ir_a('login.php');
}

if (es_post()) {
    verificar_csrf();
    $clave1 = post('clave1');
    $clave2 = post('clave2');

    if ($clave1 === '' || $clave2 === '') {
        $errores[] = 'Introduce y confirma la nueva contraseña.';
    } elseif (mb_strlen($clave1) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($clave1 !== $clave2) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if ($errores === []) {
        $db = Database::conexion();
        $db->beginTransaction();
        try {
            $act = $db->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?');
            $act->execute([password_hash($clave1, PASSWORD_DEFAULT), $usuarioId]);

            $db->prepare('UPDATE resets SET usado = 1 WHERE token_hash = ?')->execute([$tokenHash]);
            $db->prepare('DELETE FROM resets WHERE usuario_id = ?')->execute([$usuarioId]);
            Auth::revocarRecuerdos($usuarioId);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $errores[] = 'No se pudo actualizar la contraseña. Inténtalo de nuevo.';
        }
    }

    if ($errores === []) {
        flash('exito', 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.');
        ir_a('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Restablecer contraseña | <?= e(cfg('sitio_titulo')) ?></title>
  <link rel="stylesheet" href="<?= e(url('css/styles.css')) ?>">
</head>
<body class="pagina-login">
  <main class="login">
    <div class="login__tarjeta">
      <div class="login__marca" aria-hidden="true">&#128274;</div>
      <h1 class="login__titulo">Nueva contraseña</h1>
      <p class="login__sub">Establece una nueva contraseña para tu cuenta.</p>
      <?= flash_html() ?>
      <?php foreach ($errores as $err): ?>
        <p class="alerta alerta--error"><?= e($err) ?></p>
      <?php endforeach; ?>
      <form class="form form--login" method="post" action="<?= e(url('restablecer.php?token=' . rawurlencode($token))) ?>">
        <?= csrf_campo() ?>
        <label class="sr-only" for="clave1">Nueva contraseña</label>
        <input type="password" id="clave1" name="clave1" placeholder="Nueva contraseña (mín. 8 caracteres)" required minlength="8" maxlength="200">
        <label class="sr-only" for="clave2">Confirmar contraseña</label>
        <input type="password" id="clave2" name="clave2" placeholder="Confirmar contraseña" required minlength="8" maxlength="200">
        <button type="submit" class="btn btn--primary btn--full">Guardar contraseña</button>
      </form>
      <a class="login__volver" href="<?= e(url('login.php')) ?>">&larr; Volver al acceso</a>
    </div>
  </main>
  <script src="<?= e(url('js/script.js')) ?>" defer></script>
</body>
</html>