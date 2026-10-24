<?php
/** Gestión de los mensajes enviados desde el formulario de contacto. */

declare(strict_types=1);

namespace App\Repos;

final class MensajeRepo extends Repositorio
{
    protected string $tabla = 'mensajes';

    /** Últimos mensajes primero (para el panel y el dashboard). */
    public function listar(string $orden = 'datetime(creado_en) DESC, id DESC'): array
    {
        return parent::listar($orden);
    }

    public function crear(array $d): void
    {
        $this->ejecutar(
            'INSERT INTO mensajes (nombre, email, mensaje, ip)
             VALUES (:nombre, :email, :mensaje, :ip)',
            [
                ':nombre'  => $d['nombre'],
                ':email'   => $d['email'],
                ':mensaje' => $d['mensaje'],
                ':ip'      => $d['ip'] ?? '',
            ]
        );
    }

    /** Marca un mensaje como leído. */
    public function marcarLeido(int $id): bool
    {
        $stmt = $this->ejecutar('UPDATE mensajes SET leido = 1 WHERE id = :id', [':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /** Marca un mensaje como no leído. */
    public function marcarNoLeido(int $id): bool
    {
        $stmt = $this->ejecutar('UPDATE mensajes SET leido = 0 WHERE id = :id', [':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /** Número de mensajes sin leer (badge del menú). */
    public function contarNoLeidos(): int
    {
        return (int) $this->db
            ->query('SELECT COUNT(*) FROM mensajes WHERE leido = 0')
            ->fetchColumn();
    }

    /** Cuenta mensajes recibidos en los últimos N días. */
    public function contarRecientes(int $dias = 7): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM mensajes
              WHERE datetime(creado_en) >= datetime('now', 'localtime', ?)"
        );
        $stmt->execute(['-' . (int) $dias . ' days']);

        return (int) $stmt->fetchColumn();
    }
}
