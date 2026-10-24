<?php
/**
 * Bootstrap de la aplicación.
 *
 * Carga configuración, inicializa la sesión con parámetros seguros,
 * registra el autocompletado de clases (PSR-4 ligero) y arranca la
 * base de datos SQLite.
 *
 * @author  Luis Fabiano Ferreyra Alanya
 * @version 2.0
 */

declare(strict_types=1);

/* ---------------------------------------------------------------
 * 1. Modo debug
 *     Ponlo en false cuando el sitio esté en producción (ver README).
 * ------------------------------------------------------------- */
define('APP_DEBUG', true);

/* ---------------------------------------------------------------
 * 2. Rutas y URL base (funciona en la raíz y en subcarpetas)
 * ------------------------------------------------------------- */
define('APP_ROOT', dirname(__DIR__));
define('STORAGE_PATH', APP_ROOT . '/storage');
define('DB_PATH', STORAGE_PATH . '/portafolio.sqlite');

$dirScript  = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$dirScript  = preg_replace('#/admin$#', '', $dirScript);   // las páginas admin están en /admin
$dirScript  = ($dirScript === '/' || $dirScript === '.' || $dirScript === '') ? '' : rtrim($dirScript, '/');
define('BASE_URL', $dirScript);

/* ---------------------------------------------------------------
 * 3. Manejo de errores
 * ------------------------------------------------------------- */
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
if (!APP_DEBUG) {
    ini_set('error_log', STORAGE_PATH . '/errores.log');
}
error_reporting(E_ALL);

/* ---------------------------------------------------------------
 * 4. Zona horaria y sesión endurecida
 * ------------------------------------------------------------- */
date_default_timezone_set('America/Lima');

if (session_status() === PHP_SESSION_NONE) {
    $esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_name('lf_portafolio');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => (BASE_URL ?: '') . '/',
        'secure'   => $esHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    // Regenerar el ID de sesión periódicamente evita el secuestro de sesión.
    $ultimo = $_SESSION['ultima_regen'] ?? 0;
    if (time() - $ultimo > 1800) {           // cada 30 minutos
        session_regenerate_id(true);
        $_SESSION['ultima_regen'] = time();
    }
}

/* ---------------------------------------------------------------
 * 5. Autocompletado de clases:  App\Xxx  →  app/src/Xxx.php
 * ------------------------------------------------------------- */
spl_autoload_register(static function (string $clase): void {
    if (strncmp($clase, 'App\\', 4) !== 0) {
        return;
    }
    $ruta = APP_ROOT . '/app/src/' . str_replace('\\', '/', substr($clase, 4)) . '.php';
    if (is_file($ruta)) {
        require $ruta;
    }
});

/* ---------------------------------------------------------------
 * 6. Utilidades globales y arranque de la base de datos
 * ------------------------------------------------------------- */
require_once APP_ROOT . '/app/helpers.php';

App\Database::iniciar();

// Si no hay sesión activa, intenta restaurarla desde la cookie "Recordarme".
if (empty($_SESSION['usuario_id'])) {
    App\Auth::restaurarSesion();
}
