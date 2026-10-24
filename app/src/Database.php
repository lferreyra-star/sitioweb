<?php
/**
 * Conexión y esquema de la base de datos SQLite.
 *
 * La base se crea y se siembra automáticamente la primera vez que se
 * visita el sitio: no hay que configurar nada.
 */

declare(strict_types=1);

namespace App;

use PDO;

final class Database
{
    private static ?PDO $conexion = null;

    /** Devuelve la conexión PDO (singleton). */
    public static function conexion(): PDO
    {
        if (self::$conexion === null) {
            if (!is_dir(STORAGE_PATH)) {
                mkdir(STORAGE_PATH, 0775, true);
            }

            self::$conexion = new PDO('sqlite:' . DB_PATH, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            self::$conexion->exec('PRAGMA journal_mode = WAL');
            self::$conexion->exec('PRAGMA foreign_keys = ON');
        }

        return self::$conexion;
    }

    /** Crea el esquema (si no existe) y siembra los datos iniciales. */
    public static function iniciar(): void
    {
        $db = self::conexion();

        self::crearEsquema($db);

        // Garantiza que existan todas las claves de configuración (también
        // en bases de datos ya creadas con versiones anteriores).
        self::asegurarConfiguracion($db);

        $hayAdmin = (int) $db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
        if ($hayAdmin === 0) {
            self::sembrar($db);
        }
    }

    /** Insertas las claves de configuración que falten (no sobrescribe). */
    private static function asegurarConfiguracion(\PDO $db): void
    {
        $stmt = $db->prepare('INSERT OR IGNORE INTO configuraciones (clave, valor) VALUES (?, ?)');
        foreach (self::configuracionInicial() as $clave => $valor) {
            $stmt->execute([$clave, $valor]);
        }
    }

    /** Crea todas las tablas de forma idempotente. */
    private static function crearEsquema(PDO $db): void
    {
        $db->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS usuarios (
                id            INTEGER PRIMARY KEY AUTOINCREMENT,
                usuario       TEXT    NOT NULL UNIQUE,
                password_hash TEXT    NOT NULL,
                nombre        TEXT    NOT NULL,
                email         TEXT    NOT NULL DEFAULT '',
                creado_en     TEXT    NOT NULL DEFAULT (datetime('now', 'localtime'))
            );

            CREATE TABLE IF NOT EXISTS configuraciones (
                clave TEXT PRIMARY KEY,
                valor TEXT NOT NULL DEFAULT ''
            );

            CREATE TABLE IF NOT EXISTS habilidades (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre     TEXT    NOT NULL,
                icono      TEXT    NOT NULL DEFAULT '💼',
                porcentaje INTEGER NOT NULL DEFAULT 50 CHECK (porcentaje BETWEEN 0 AND 100),
                orden      INTEGER NOT NULL DEFAULT 0
            );

            CREATE TABLE IF NOT EXISTS educaciones (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                titulo       TEXT NOT NULL,
                institucion  TEXT NOT NULL,
                fecha        TEXT NOT NULL,
                descripcion  TEXT NOT NULL DEFAULT '',
                orden        INTEGER NOT NULL DEFAULT 0
            );

            CREATE TABLE IF NOT EXISTS experiencias (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                titulo       TEXT NOT NULL,
                institucion  TEXT NOT NULL,
                fecha        TEXT NOT NULL,
                descripcion  TEXT NOT NULL DEFAULT '',
                orden        INTEGER NOT NULL DEFAULT 0
            );

            CREATE TABLE IF NOT EXISTS proyectos (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                titulo       TEXT NOT NULL,
                descripcion  TEXT NOT NULL,
                icono        TEXT NOT NULL DEFAULT '🔗',
                enlace       TEXT NOT NULL DEFAULT '',
                categoria    TEXT NOT NULL DEFAULT 'web',
                tecnologias  TEXT NOT NULL DEFAULT '',
                orden        INTEGER NOT NULL DEFAULT 0,
                creado_en    TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
            );

            CREATE TABLE IF NOT EXISTS mensajes (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre     TEXT NOT NULL,
                email      TEXT NOT NULL,
                mensaje    TEXT NOT NULL,
                ip         TEXT NOT NULL DEFAULT '',
                leido      INTEGER NOT NULL DEFAULT 0,
                creado_en  TEXT NOT NULL DEFAULT (datetime('now', 'localtime'))
            );

            -- Cookies persistentes "Recordarme" (selector + validador hasheado)
            CREATE TABLE IF NOT EXISTS recuerdos (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                usuario_id INTEGER NOT NULL,
                selector   TEXT    NOT NULL UNIQUE,
                token_hash TEXT    NOT NULL,
                expira_en  TEXT    NOT NULL,
                creado_en  TEXT    NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
            );

            -- Solicitudes de restablecimiento de contraseña (token hasheado)
            CREATE TABLE IF NOT EXISTS resets (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                usuario_id INTEGER NOT NULL,
                token_hash TEXT    NOT NULL,
                expira_en  TEXT    NOT NULL,
                usado      INTEGER NOT NULL DEFAULT 0,
                creado_en  TEXT    NOT NULL DEFAULT (datetime('now', 'localtime')),
                FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
            );
        SQL);
    }

    /** Claves de configuración por defecto (se crean si faltan). */
    private static function configuracionInicial(): array
    {
        return [
            'sitio_titulo'        => 'Luis Fabiano Ferreyra Alanya | Portafolio',
            'meta_descripcion'    => 'Portafolio profesional de Luis Fabiano Ferreyra Alanya - Desarrollador Web',
            'logo_primero'        => 'Luis',
            'logo_segundo'        => 'Fabiano',
            'hero_saludo'         => 'Hola, soy',
            'hero_nombre_grad'    => 'Luis Fabiano',
            'hero_nombre_resto'   => 'Ferreyra Alanya',
            'hero_roles'          => 'Desarrollador Web|Diseñador Front-End|Creador de Experiencias Digitales',
            'hero_descripcion'    => 'Desarrollador web apasionado por crear experiencias digitales modernas, funcionales y atractivas.',
            'sobre_parrafo_1'     => 'Soy <strong>Luis Fabiano Ferreyra Alanya</strong>, un desarrollador apasionado por la tecnología y el diseño web. Me especializo en crear soluciones digitales que combinan estética, rendimiento y una excelente experiencia de usuario.',
            'sobre_parrafo_2'     => 'Me gusta aprender constantemente nuevas tecnologías y enfrentar desafíos que me permitan crecer profesionalmente.',
            'perfil_nombre'       => 'Luis Fabiano Ferreyra Alanya',
            'perfil_perfil'       => 'Desarrollador Web',
            'perfil_idiomas'      => 'Español',
            'perfil_email'        => 'lfabiano@email.com',
            'contacto_titulo'     => '¿Tienes un proyecto en mente? Hablemos.',
            'whatsapp'            => '51960403655',
            'foto'                => 'img/foto.jpg',
            'footer_texto'        => 'Luis Fabiano Ferreyra Alanya. Todos los derechos reservados.',

            // ---- Correo saliente (SMTP) para "Recuperar contraseña" ----
            'smtp_host'      => '',
            'smtp_puerto'    => '587',
            'smtp_usuario'   => '',
            'smtp_clave'     => '',
            'smtp_seguridad' => 'tls',
            'smtp_remitente' => '',
            'smtp_nombre'    => 'Portafolio',
        ];
    }

    /** ¿El correo saliente está configurado? */
    public static function correoConfigurado(): bool
    {
        return trim((string) cfg('smtp_host')) !== '';
    }

    /** Inserta el administrador por defecto y el contenido inicial del sitio. */
    private static function sembrar(PDO $db): void
    {
        // ---- Administrador por defecto: admin / admin123 (cámbialo ya) ----
        $stmt = $db->prepare(
            'INSERT INTO usuarios (usuario, password_hash, nombre, email) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            'admin',
            password_hash('admin123', PASSWORD_DEFAULT),
            'Luis Fabiano Ferreyra Alanya',
            'lfabiano@email.com',
        ]);

        // ---- Configuración del sitio ----
        self::asegurarConfiguracion($db);

        // ---- Habilidades ----
        $habilidades = [
            ['HTML5', '💻', 90],
            ['CSS3', '🎨', 85],
            ['JavaScript', '🔌', 80],
            ['Responsive Design', '🛠️', 85],
        ];
        $stmt = $db->prepare(
            'INSERT INTO habilidades (nombre, icono, porcentaje, orden) VALUES (?, ?, ?, ?)'
        );
        foreach ($habilidades as $i => [$nombre, $icono, $porcentaje]) {
            $stmt->execute([$nombre, $icono, $porcentaje, $i]);
        }

        // ---- Educación ----
        $educaciones = [
            ['Ingeniería de Sistemas', 'Universidad Autónoma del Perú', 'En curso · 5.º ciclo', ''],
            ['Certificación Full Stack Developer', 'Platzi - Bootcamp intensivo en desarrollo web', '2023', ''],
            ['Curso de Diseño UI/UX', 'Coursera - Fundamentos de experiencia de usuario', '2022', ''],
        ];
        $stmt = $db->prepare(
            'INSERT INTO educaciones (titulo, institucion, fecha, descripcion, orden) VALUES (?, ?, ?, ?, ?)'
        );
        foreach ($educaciones as $i => [$titulo, $institucion, $fecha, $descripcion]) {
            $stmt->execute([$titulo, $institucion, $fecha, $descripcion, $i]);
        }

        // ---- Experiencia laboral ----
        $experiencias = [
            ['Desarrollador Front-End', 'TechNova Solutions', '2024 - Actualidad', 'Maquetación de interfaces, animaciones y optimización de rendimiento web.'],
            ['Desarrollador Web Jr.', 'Estudio Digital Creativo', '2023 - 2024', 'Creación de landing pages y tiendas online con HTML, CSS y JavaScript.'],
            ['Freelancer Web', 'Proyectos independientes', '2022 - 2023', 'Diseño y desarrollo de sitios web para pequeños negocios.'],
        ];
        $stmt = $db->prepare(
            'INSERT INTO experiencias (titulo, institucion, fecha, descripcion, orden) VALUES (?, ?, ?, ?, ?)'
        );
        foreach ($experiencias as $i => [$titulo, $institucion, $fecha, $descripcion]) {
            $stmt->execute([$titulo, $institucion, $fecha, $descripcion, $i]);
        }

        // ---- Proyectos ----
        $proyectos = [
            ['Landing Page Moderna', 'Sitio web responsivo con animaciones y navegación fluida.', '🚀', '', 'web', 'HTML5, CSS3, JS'],
            ['App de Tareas', 'Aplicación para gestionar tareas con almacenamiento local.', '📱', '', 'app', 'JavaScript, LocalStorage'],
            ['Portafolio Personal', 'Diseño minimalista y elegante para mostrar trabajos.', '🌐', '', 'web', 'HTML5, CSS3'],
        ];
        $stmt = $db->prepare(
            'INSERT INTO proyectos (titulo, descripcion, icono, enlace, categoria, tecnologias, orden)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($proyectos as $i => [$titulo, $descripcion, $icono, $enlace, $categoria, $tecnologias]) {
            $stmt->execute([$titulo, $descripcion, $icono, $enlace, $categoria, $tecnologias, $i]);
        }
    }
}
