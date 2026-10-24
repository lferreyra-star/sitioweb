<?php
/** CRUD de experiencia laboral. */

declare(strict_types=1);

namespace App\Repos;

final class ExperienciaRepo extends Repositorio
{
    protected string $tabla = 'experiencias';

    public function crear(array $d): void
    {
        $this->ejecutar(
            'INSERT INTO experiencias (titulo, institucion, fecha, descripcion, orden)
             VALUES (:titulo, :institucion, :fecha, :descripcion, :orden)',
            [
                ':titulo'      => $d['titulo'],
                ':institucion' => $d['institucion'],
                ':fecha'       => $d['fecha'],
                ':descripcion' => $d['descripcion'] ?? '',
                ':orden'       => (int) ($d['orden'] ?? 0),
            ]
        );
    }

    public function actualizar(int $id, array $d): bool
    {
        $stmt = $this->ejecutar(
            'UPDATE experiencias
                SET titulo = :titulo, institucion = :institucion, fecha = :fecha,
                    descripcion = :descripcion, orden = :orden
              WHERE id = :id',
            [
                ':id'          => $id,
                ':titulo'      => $d['titulo'],
                ':institucion' => $d['institucion'],
                ':fecha'       => $d['fecha'],
                ':descripcion' => $d['descripcion'] ?? '',
                ':orden'       => (int) ($d['orden'] ?? 0),
            ]
        );

        return $stmt->rowCount() > 0;
    }
}
