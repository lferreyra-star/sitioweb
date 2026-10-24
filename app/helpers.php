<?php
/**
 * Funciones utilitarias globales de la aplicación.
 *
 * Todas las salidas a HTML deben pasar por e() para evitar XSS,
 * y todos los formularios POST deben incluir csrf_campo().
 */

declare(strict_types=1);

/* ===============================================================
 *  SEGURIDAD
 * =============================================================== */

/** Escapa un valor para imprimirlo de forma segura en HTML (XSS). */
function e(mixed $valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Devuelve (y crea si no existe) el token CSRF de la sesión actual. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Campo oculto <input> con el token CSRF. Úsalo dentro de cada <form>. */
function csrf_campo(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/** Compara el token enviado contra el de la sesión de forma segura. */
function csrf_valido(mixed $token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Llamar al inicio de toda página que procese formularios POST.
 * Si el token CSRF es incorrecto, detiene la petición con error 419.
 */
function verificar_csrf(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }
    if (!csrf_valido($_POST['_csrf'] ?? null)) {
        http_response_code(419);
        exit('Token de seguridad inválido o sesión expirada. Vuelve atrás y reintenta.');
    }
}

/* ===============================================================
 *  PETICIONES Y ENTRADAS
 * =============================================================== */

/** ¿La petición actual es un POST? */
function es_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** Lee un campo del POST, eliminando espacios sobrantes. */
function post(string $clave, string $porDefecto = ''): string
{
    $valor = $_POST[$clave] ?? $porDefecto;
    return is_string($valor) ? trim($valor) : $porDefecto;
}

/** ¿Se envió un campo concreto en el POST? */
function tiene_post(string $clave): bool
{
    return isset($_POST[$clave]);
}

/* ===============================================================
 *  NAVEGACIÓN
 * =============================================================== */

/** Construye una URL absoluta dentro del sitio (compatible con subcarpetas). */
function url(string $ruta = ''): string
{
    return BASE_URL . '/' . ltrim($ruta, '/');
}

/** Redirige a una ruta relativa (o URL absoluta) y detiene la ejecución. */
function ir_a(string $ruta): void
{
    $destino = (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://'))
        ? $ruta
        : url($ruta);
    header('Location: ' . $destino, true, 302);
    exit;
}

/* ===============================================================
 *  MENSAJES FLASH (un solo uso entre peticiones)
 * =============================================================== */

/** Guarda un mensaje para mostrarlo tras la redirección. */
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

/** Recupera y limpia todos los mensajes pendientes. */
function flash_listar(): array
{
    $mensajes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensajes;
}

/** Renderiza los mensajes pendientes como bloques <p class="alerta ...">. */
function flash_html(): string
{
    $salida = '';
    foreach (flash_listar() as $item) {
        $tipo = in_array($item['tipo'], ['exito', 'error', 'info'], true) ? $item['tipo'] : 'info';
        $salida .= '<p class="alerta alerta--' . $tipo . '">' . e($item['mensaje']) . '</p>';
    }
    return $salida;
}

/** Guarda valores del formulario para repoblarlo tras un error. */
function old_guardar(array $datos): void
{
    $_SESSION['old'] = $datos;
}

/** Recupera y limpia los valores repuestos del último envío fallido. */
function old_obtener(): array
{
    $datos = $_SESSION['old'] ?? [];
    unset($_SESSION['old']);
    return $datos;
}

/** Envía una respuesta JSON y detiene la ejecución (peticiones AJAX). */
function responder_json(array $datos): never
{
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** ¿La petición actual espera una respuesta JSON (modal/fetch)? */
function es_ajax(): bool
{
    return post('ajax') === '1'
        || (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');
}

/* ===============================================================
 *  CONFIGURACIÓN DEL SITIO (clave → valor desde SQLite)
 * =============================================================== */

/**
 * Obtiene una configuración del sitio.
 *
 * @param string $clave      Clave a buscar (p. ej. 'perfil_email').
 * @param string $porDefecto Valor si la clave no existe.
 */
function cfg(string $clave, string $porDefecto = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = (new App\Repos\ConfigRepo())->comoArreglo();
    }
    return $cache[$clave] ?? $porDefecto;
}
