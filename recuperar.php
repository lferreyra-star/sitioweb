<?php
/**
 * Solicitar enlace para restablecer la contraseña.
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Auth;
use App\Correo;
use App\Database;

if (Auth::check()) {
    ir_a('admin/');
}

$enviado = false;
$mensaje = '';
$oldEmail = '';

if (es_post()) {
    verificar_csrf();

    $oldEmail = strtolower(post('email'));
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Throttling ligero (1 solicitud cada 20 segundos por sesión/IP).
    $ts = $_SESSION['reset_ts'] ?? 0;
    if (time() - $ts < 20) {
        $mensaje = 'Has enviado demasiadas solicitudes. Espera unos segundos e inténtalo de nuevo.';
    } else {
        $_SESSION['reset_ts'] = time();

        $stmt = Database::conexion()->prepare('SELECT id, email FROM usuarios LIMIT 1');
        $stmt->execute();
        $usuario = $stmt->fetch();

        if ($usuario && !empty($usuario['email']) && $usuario['email'] !== '') {
            $emailUsuario = (string) $usuario['email'];
            if (filter_var($emailUsuario, FILTER_VALIDATE_EMAIL)) {
                Database::conexion()->prepare('UPDATE resets SET usado = 1 WHERE usuario_id = ?')->execute([$usuario['id']]);

                $token = bin2hex(random_bytes(32));
                $hash  = hash('sha256', $token);
                $expira = date('Y-m-d H:i:s', time() + 3600);

                $ins = Database::conexion()->prepare(
                    'INSERT INTO resets (usuario_id, token_hash, expira_en) VALUES (?, ?, ?)'
                );
                $ins->execute([$usuario['id'], $hash, $expira]);

                $esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $enlace  = $esquema . '://' . $host . url('restablecer.php?token=' . $token);

                $html = '<p>Hola,</p>'
                    . '<p>Has solicitado restablecer la contraseña de tu portafolio.</p>'
                    . '<p><a href="' . e($enlace) . '">Haz clic aquí para crear una nueva contraseña</a></p>'
                    . '<p>Este enlace caduca en 1 hora y solo puede usarse una vez.</p>'
                    . '<p>Si no fuiste tú, puedes ignorar este mensaje.</p>';

                $r = Correo::enviar($emailUsuario, 'Restablecer tu contraseña', $html);

                if (!$r['ok']) {
                    @file_put_contents(STORAGE_PATH . '/correos.log', "\n--- ENLACE DE RESTABLECIMIENTO (NO ENVIADO) ---\n" . $enlace . "\n---\n", FILE_APPEND);
                }
            }
        }

        $enviado = true;
        $mensaje = 'Si ese correo está registrado, recibirás un enlace de restablecimiento en unos minutos.';
    }

    old_guardar(['email' => $oldEmail]);
    flash('info', $mensaje);
    ir_a('recuperar.php');
}

$old = old_obtener();
$emailOld = $old['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Recuperar contraseña | <?= e(cfg('sitio_titulo')) ?></title>
  <link rel="stylesheet" href="<?= e(url('css/styles.css')) ?>">
</head>
<body class="pagina-login">
  <main class="login">
    <div class="login__tarjeta">
      <div class="login__marca" aria-hidden="true">&#128274;</div>
      <h1 class="login__titulo">Recuperar contraseña</h1>
      <p class="login__sub">Introduce tu correo electrónico y te enviaremos un enlace para restablecerla.</p>
      <?= flash_html() ?>
      <form class="form form--login" method="post" action="<?= e(url('recuperar.php')) ?>">
        <?= csrf_campo() ?>
        <label class="sr-only" for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" placeholder="tu@correo.com" required maxlength="190" value="<?= e($emailOld) ?>">
        <button type="submit" class="btn btn--primary btn--full">Enviar enlace</button>
      </form>
      <a class="login__volver" href="<?= e(url('login.php')) ?>">&larr; Volver al acceso</a>
    </div>
  </main>
  <script src="<?= e(url('js/script.js')) ?>" defer></script>
</body>
</html>