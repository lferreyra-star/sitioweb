<?php
/** CRUD de habilidades del portafolio. */

declare(strict_types=1);

namespace App\Repos;

final class HabilidadRepo extends Repositorio
{
    protected string $tabla = 'habilidades';

    public function crear(array $d): void
    {
        $this->ejecutar(
            'INSERT INTO habilidades (nombre, icono, porcentaje, orden)
             VALUES (:nombre, :icono, :porcentaje, :orden)',
            [
                ':nombre'     => $d['nombre'],
                ':icono'      => $d['icono'] !== '' ? $d['icono'] : '💼',
                ':porcentaje' => max(0, min(100, (int) $d['porcentaje'])),
                ':orden'      => (int) ($d['orden'] ?? 0),
            ]
        );
    }

    public function actualizar(int $id, array $d): bool
    {
        $stmt = $this->ejecutar(
            'UPDATE habilidades
                SET nombre = :nombre, icono = :icono, porcentaje = :porcentaje, orden = :orden
              WHERE id = :id',
            [
                ':id'         => $id,
                ':nombre'     => $d['nombre'],
                ':icono'      => $d['icono'] !== '' ? $d['icono'] : '💼',
                ':porcentaje' => max(0, min(100, (int) $d['porcentaje'])),
                ':orden'      => (int) ($d['orden'] ?? 0),
            ]
        );

        return $stmt->rowCount() > 0;
    }
}
