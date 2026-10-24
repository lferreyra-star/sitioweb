<?php
/**
 * Login del panel de administración.
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Auth;

// Si ya hay sesión, directo al panel (JSON si lo pide el modal del index).
if (Auth::check()) {
    if (es_ajax()) {
        responder_json(['ok' => true, 'redirect' => url('admin/')]);
    }
    ir_a('admin/');
}

$errores      = [];
$usuario      = '';
$bloqueo      = Auth::segundosDeBloqueo();
$mensajeError = null;

if (es_post()) {
    verificar_csrf();

    $usuario = post('usuario');
    $clave   = post('clave');
    $bloqueo = Auth::segundosDeBloqueo();

    if ($bloqueo > 0) {
        $mensajeError = 'Demasiados intentos fallidos. Espera ' . ceil($bloqueo / 60) . ' minuto(s).';
    } elseif ($usuario === '' || $clave === '') {
        $mensajeError = 'Introduce tu usuario y contraseña.';
    } elseif (Auth::intentar($usuario, $clave)) {
        if (post('recordar') === '1') {
            Auth::recordar((int) $_SESSION['usuario_id']);
        }
        if (es_ajax()) {
            responder_json(['ok' => true, 'redirect' => url('admin/')]);
        }
        flash('exito', 'Bienvenido, ' . $_SESSION['usuario_nombre'] . '.');
        ir_a('admin/');
    } else {
        $restantes    = Auth::MAX_INTENTOS - (int) ($_SESSION['intentos_fallidos'] ?? 0);
        $mensajeError = $restantes > 0
            ? 'Credenciales incorrectas. Te quedan ' . $restantes . ' intento(s).'
            : 'Cuenta bloqueada temporalmente por seguridad.';
        $bloqueo = Auth::segundosDeBloqueo();
    }

    // Respuesta para el modal del index (sin recargar la página).
    if (es_ajax()) {
        responder_json(['ok' => false, 'error' => $mensajeError, 'bloqueo' => $bloqueo]);
    }

    if ($mensajeError !== null) {
        $errores[] = $mensajeError;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Acceso administrativo | <?= e(cfg('sitio_titulo')) ?></title>
  <link rel="stylesheet" href="<?= e(url('css/styles.css')) ?>">
</head>
<body class="pagina-login">

  <main class="login">
    <div class="login__tarjeta">
      <div class="login__marca" aria-hidden="true">&#128274;</div>
      <h1 class="login__titulo">Panel de administración</h1>
      <p class="login__sub">Identifícate para gestionar el contenido del sitio.</p>

      <?= flash_html() ?>

      <?php foreach ($errores as $error): ?>
        <p class="alerta alerta--error"><?= e($error) ?></p>
      <?php endforeach; ?>

      <?php if ($bloqueo > 0 && $errores === []): ?>
        <p class="alerta alerta--info">Acceso bloqueado durante <?= (int) ceil($bloqueo / 60) ?> minuto(s) por intentos fallidos.</p>
      <?php endif; ?>

      <form class="form form--login" method="post" action="<?= e(url('login.php')) ?>" autocomplete="off">
        <?= csrf_campo() ?>
        <label class="sr-only" for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" placeholder="Usuario" required
               maxlength="60" value="<?= e($usuario) ?>" autofocus>
        <label class="sr-only" for="clave">Contraseña</label>
        <input type="password" id="clave" name="clave" placeholder="Contraseña" required maxlength="200">

        <div class="login__opciones">
          <label class="check">
            <input type="checkbox" name="recordar" value="1">
            <span>Recordarme</span>
          </label>
          <a class="login__recuperar" href="<?= e(url('recuperar.php')) ?>">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn btn--primary btn--full" <?= $bloqueo > 0 ? 'disabled' : '' ?>>
          Iniciar sesión
        </button>
      </form>

      <a class="login__volver" href="<?= e(url('index.php')) ?>">&larr; Volver al portafolio</a>
    </div>
  </main>

  <script src="<?= e(url('js/script.js')) ?>" defer></script>
</body>
</html>
