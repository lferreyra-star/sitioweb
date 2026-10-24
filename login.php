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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(url('css/styles.css')) ?>">
</head>
<body class="pagina-login">

  <div class="login-fondo" aria-hidden="true">
    <span class="login-fondo__orb login-fondo__orb--uno"></span>
    <span class="login-fondo__orb login-fondo__orb--dos"></span>
    <span class="login-fondo__grid"></span>
  </div>

  <main class="login">
    <div class="login__contenedor">
      <div class="login__cabecera">
        <a class="login__marca" href="<?= e(url('index.php')) ?>">
          <span class="login__marca-icono">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
          </span>
          <span class="login__marca-texto">
            <strong>Panel de Administración</strong>
            <small>Acceso seguro</small>
          </span>
        </a>
        <a class="login__volver" href="<?= e(url('index.php')) ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
          Volver al portafolio
        </a>
      </div>

      <div class="login__tarjeta">
        <div class="login__tarjeta-header">
          <h1 class="login__titulo">Iniciar sesión</h1>
          <p class="login__sub">Introduce tus credenciales para acceder al panel</p>
        </div>

        <?= flash_html() ?>

        <?php foreach ($errores as $error): ?>
          <div class="login-alerta login-alerta--error">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span><?= e($error) ?></span>
          </div>
        <?php endforeach; ?>

        <?php if ($bloqueo > 0 && $errores === []): ?>
          <div class="login-alerta login-alerta--info">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <span>Acceso bloqueado durante <?= (int) ceil($bloqueo / 60) ?> minuto(s) por intentos fallidos.</span>
          </div>
        <?php endif; ?>

        <form class="form-login" method="post" action="<?= e(url('login.php')) ?>" autocomplete="off">
          <?= csrf_campo() ?>
          <div class="form-login__campo">
            <label for="usuario">Usuario</label>
            <div class="form-login__input">
              <span class="form-login__icono">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              </span>
              <input type="text" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required
                     maxlength="60" value="<?= e($usuario) ?>" autofocus>
            </div>
          </div>

          <div class="form-login__campo">
            <label for="clave">Contraseña</label>
            <div class="form-login__input">
              <span class="form-login__icono">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
              </span>
              <input type="password" id="clave" name="clave" placeholder="Ingresa tu contraseña" required maxlength="200">
              <button type="button" class="form-login__ojo" id="toggleClave" aria-label="Mostrar u ocultar contraseña">
                <svg class="ojo-ver" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <svg class="ojo-ocultar" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
              </button>
            </div>
          </div>

          <div class="form-login__opciones">
            <label class="check check--moderno">
              <input type="checkbox" name="recordar" value="1">
              <span class="check__box"></span>
              <span>Recordarme</span>
            </label>
            <a class="form-login__recuperar" href="<?= e(url('recuperar.php')) ?>">¿Olvidaste tu contraseña?</a>
          </div>

          <button type="submit" class="btn-login" <?= $bloqueo > 0 ? 'disabled' : '' ?>>
            <span>Iniciar sesión</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
          </button>
        </form>
      </div>

      <p class="login__pie">Protegido con autenticación segura</p>
    </div>
  </main>

  <script>
    (() => {
      const btn = document.getElementById('toggleClave');
      const input = document.getElementById('clave');
      if (!btn || !input) return;
      const ver = btn.querySelector('.ojo-ver');
      const ocultar = btn.querySelector('.ojo-ocultar');
      btn.addEventListener('click', () => {
        const mostrar = input.type === 'password';
        input.type = mostrar ? 'text' : 'password';
        if (ver) ver.style.display = mostrar ? 'none' : 'block';
        if (ocultar) ocultar.style.display = mostrar ? 'block' : 'none';
        btn.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
      });
    })();
  </script>
</body>
</html>
