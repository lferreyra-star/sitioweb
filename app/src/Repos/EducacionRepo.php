<?php
/** CRUD de formación académica. */

declare(strict_types=1);

namespace App\Repos;

final class EducacionRepo extends Repositorio
{
    protected string $tabla = 'educaciones';

    public function crear(array $d): void
    {
        $this->ejecutar(
            'INSERT INTO educaciones (titulo, institucion, fecha, descripcion, orden)
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
            'UPDATE educaciones
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
