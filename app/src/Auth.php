<?php
/**
 * Autenticación del administrador.
 *
 * Incluye hash de contraseñas con bcrypt, regeneración de sesión,
 * bloqueo temporal por intentos fallidos (anti fuerza bruta).
 */

declare(strict_types=1);

namespace App;

final class Auth
{
    /** Intentos fallidos permitidos antes de bloquear temporalmente. */
    public const MAX_INTENTOS = 5;

    /** Segundos que dura el bloqueo tras agotar los intentos. */
    public const BLOQUEO_SEGUNDOS = 900; // 15 minutos

    /** Días que dura la cookie persistente de "Recordarme". */
    public const RECORDAR_DIAS = 30;

    /** Nombre de la cookie persistente. */
    private const COOKIE = 'lf_recuerdo';

    /**
     * Intenta iniciar sesión. Devuelve true solo si las credenciales son válidas.
     */
    public static function intentar(string $usuario, string $clave): bool
    {
        if (self::bloqueado()) {
            return false;
        }

        $fila = self::buscarUsuario($usuario);

        // password_verify() se ejecuta siempre para no revelar por tiempos
        // de respuesta si el usuario existe o no.
        $claveHash = $fila['password_hash'] ?? '';
        $esValido  = $claveHash !== '' && password_verify($clave, $claveHash);

        if (!$esValido) {
            $_SESSION['intentos_fallidos'] = (int) ($_SESSION['intentos_fallidos'] ?? 0) + 1;
            $_SESSION['ultimo_intento']    = time();
            return false;
        }

        // Recodificar la contraseña si los parámetros de hash cambiaron.
        if (password_needs_rehash($claveHash, PASSWORD_DEFAULT)) {
            $actualizar = Database::conexion()->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?');
            $actualizar->execute([password_hash($clave, PASSWORD_DEFAULT), $fila['id']]);
        }

        unset($_SESSION['intentos_fallidos'], $_SESSION['ultimo_intento']);

        session_regenerate_id(true);          // evita fijación de sesión
        $_SESSION['usuario_id']     = (int) $fila['id'];
        $_SESSION['usuario_nombre'] = $fila['nombre'];
        $_SESSION['ultima_regen']   = time();

        return true;
    }

    /** ¿Hay una sesión de administrador activa? */
    public static function check(): bool
    {
        return !empty($_SESSION['usuario_id']);
    }

    /** Devuelve los datos del administrador autenticado o null. */
    public static function usuario(): ?array
    {
        if (!self::check()) {
            return null;
        }

        static $cache = null;
        if ($cache === null) {
            $stmt = Database::conexion()->prepare('SELECT id, usuario, nombre, email FROM usuarios WHERE id = ?');
            $stmt->execute([(int) $_SESSION['usuario_id']]);
            $cache = $stmt->fetch() ?: null;
        }

        return $cache;
    }

    /** Redirige al login si no hay sesión activa (para páginas protegidas). */
    public static function exigirLogin(): void
    {
        if (!self::check()) {
            flash('info', 'Inicia sesión para acceder al panel de administración.');
            ir_a('login.php');
        }
    }

    /* ===============================================================
     *  "RECORDARME": cookie persistente con selector + validador.
     *  El validador se guarda hasheado y se rota en cada uso.
     * =============================================================== */

    /** Emite una cookie persistente para no volver a pedir la contraseña. */
    public static function recordar(int $usuarioId): void
    {
        self::limpiarCaducados();

        $selector  = bin2hex(random_bytes(9));
        $validador = bin2hex(random_bytes(32));
        $expira    = time() + self::RECORDAR_DIAS * 86400;

        $stmt = Database::conexion()->prepare(
            'INSERT INTO recuerdos (usuario_id, selector, token_hash, expira_en) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$usuarioId, $selector, hash('sha256', $validador), date('Y-m-d H:i:s', $expira)]);

        self::ponerCookie($selector . ':' . $validador, $expira);
    }

    /** Restaura la sesión desde la cookie persistente. Devuelve true si lo logra. */
    public static function restaurarSesion(): bool
    {
        if (self::check()) {
            return true;
        }

        $cookie = (string) ($_COOKIE[self::COOKIE] ?? '');
        if (!str_contains($cookie, ':')) {
            return false;
        }

        [$selector, $validador] = explode(':', $cookie, 2);
        if ($selector === '' || $validador === '') {
            self::borrarCookie();
            return false;
        }

        $stmt = Database::conexion()->prepare('SELECT * FROM recuerdos WHERE selector = ? LIMIT 1');
        $stmt->execute([$selector]);
        $fila = $stmt->fetch();

        if (!$fila) {
            self::borrarCookie();
            return false;
        }

        if (strtotime((string) $fila['expira_en']) < time()) {
            self::eliminarRecuerdo((int) $fila['id']);
            self::borrarCookie();
            return false;
        }

        // El validador debe coincidir; si no, la cookie pudo ser robada.
        if (!hash_equals((string) $fila['token_hash'], hash('sha256', $validador))) {
            self::olvidarRecuerdo();
            return false;
        }

        $usuarioId = (int) $fila['usuario_id'];
        $usuario   = self::buscarPorId($usuarioId);
        if (!$usuario) {
            self::olvidarRecuerdo();
            return false;
        }

        // Rotar el token (se borra el anterior y se emite uno nuevo).
        self::eliminarRecuerdo((int) $fila['id']);
        self::recordar($usuarioId);

        session_regenerate_id(true);
        $_SESSION['usuario_id']     = $usuarioId;
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['ultima_regen']   = time();

        return true;
    }

    /** Elimina la cookie persistente y su registro en la base de datos. */
    public static function olvidarRecuerdo(): void
    {
        $cookie = (string) ($_COOKIE[self::COOKIE] ?? '');
        if ($cookie !== '') {
            $selector = explode(':', $cookie, 2)[0];
            if ($selector !== '') {
                $stmt = Database::conexion()->prepare('DELETE FROM recuerdos WHERE selector = ?');
                $stmt->execute([$selector]);
            }
        }

        self::borrarCookie();
    }

    /** Invalida TODAS las cookies persistentes de un usuario (cambio de clave, etc.). */
    public static function revocarRecuerdos(int $usuarioId): void
    {
        $stmt = Database::conexion()->prepare('DELETE FROM recuerdos WHERE usuario_id = ?');
        $stmt->execute([$usuarioId]);
    }

    private static function limpiarCaducados(): void
    {
        Database::conexion()
            ->prepare('DELETE FROM recuerdos WHERE expira_en < ?')
            ->execute([date('Y-m-d H:i:s')]);
    }

    private static function eliminarRecuerdo(int $id): void
    {
        Database::conexion()->prepare('DELETE FROM recuerdos WHERE id = ?')->execute([$id]);
    }

    private static function buscarPorId(int $id): ?array
    {
        $stmt = Database::conexion()->prepare('SELECT * FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $fila = $stmt->fetch();

        return $fila ?: null;
    }

    private static function ponerCookie(string $valor, int $expira): void
    {
        $esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        setcookie(self::COOKIE, $valor, [
            'expires'  => $expira,
            'path'     => (BASE_URL ?: '') . '/',
            'secure'   => $esHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[self::COOKIE] = $valor;
    }

    private static function borrarCookie(): void
    {
        setcookie(self::COOKIE, '', [
            'expires'  => time() - 42000,
            'path'     => (BASE_URL ?: '') . '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        unset($_COOKIE[self::COOKIE]);
    }

    /** Cierra la sesión de forma segura. */
    public static function cerrar(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool) $params['secure'],
                (bool) $params['httponly']
            );
        }

        session_destroy();
    }

    /** Devuelve los segundos restantes de bloqueo (0 si no hay bloqueo). */
    public static function segundosDeBloqueo(): int
    {
        $intentos = (int) ($_SESSION['intentos_fallidos'] ?? 0);
        if ($intentos < self::MAX_INTENTOS) {
            return 0;
        }

        $ultimo   = (int) ($_SESSION['ultimo_intento'] ?? 0);
        $restante = self::BLOQUEO_SEGUNDOS - (time() - $ultimo);

        if ($restante <= 0) {
            unset($_SESSION['intentos_fallidos'], $_SESSION['ultimo_intento']);
            return 0;
        }

        return $restante;
    }

    private static function bloqueado(): bool
    {
        return self::segundosDeBloqueo() > 0;
    }

    private static function buscarUsuario(string $usuario): ?array
    {
        $stmt = Database::conexion()->prepare('SELECT * FROM usuarios WHERE usuario = ? LIMIT 1');
        $stmt->execute([$usuario]);
        $fila = $stmt->fetch();

        return $fila ?: null;
    }
}
