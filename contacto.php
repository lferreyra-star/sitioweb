<?php
/**
 * Procesamiento del formulario de contacto.
 *
 * Valida en el servidor, protege contra CSRF y bots, guarda el mensaje
 * en SQLite y redirige de vuelta al portafolio.
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Repos\MensajeRepo;

// Solo se acepta POST y con token CSRF válido.
if (!es_post()) {
    ir_a('#contacto');
}
verificar_csrf();

$nombre     = post('nombre');
$email      = post('email');
$mensaje    = post('mensaje');
$honeypot   = post('sitio');   // campo trampa: debe quedar vacío
$errores    = [];

// --- Anti-bots -------------------------------------------------------
$honeypotRelleno = $honeypot !== '';
$envioRelampago  = (time() - (int) ($_SESSION['formulario_ts'] ?? 0)) < 3;

if ($honeypotRelleno || $envioRelampago) {
    // Se finge un envío exitoso para no delatar la trampa.
    flash('exito', '¡Gracias por tu mensaje! Te responderé pronto.');
    ir_a('#contacto');
}

// --- Validación ------------------------------------------------------
if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 120) {
    $errores[] = 'El nombre debe tener entre 2 y 120 caracteres.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
    $errores[] = 'Introduce un correo electrónico válido.';
}
if (mb_strlen($mensaje) < 10 || mb_strlen($mensaje) > 3000) {
    $errores[] = 'El mensaje debe tener entre 10 y 3000 caracteres.';
}

if ($errores !== []) {
    old_guardar(['nombre' => $nombre, 'email' => $email, 'mensaje' => $mensaje]);
    flash('error', implode(' ', $errores));
    ir_a('#contacto');
}

// --- Guardado --------------------------------------------------------
(new MensajeRepo())->crear([
    'nombre'  => $nombre,
    'email'   => $email,
    'mensaje' => $mensaje,
    'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
]);

flash('exito', '¡Gracias por tu mensaje! Te responderé pronto.');
ir_a('#contacto');
