<?php
/** CRUD de proyectos del portafolio. */

declare(strict_types=1);

namespace App\Repos;

final class ProyectoRepo extends Repositorio
{
    protected string $tabla = 'proyectos';

    public function crear(array $d): void
    {
        $this->ejecutar(
            'INSERT INTO proyectos (titulo, descripcion, icono, enlace, categoria, tecnologias, orden)
             VALUES (:titulo, :descripcion, :icono, :enlace, :categoria, :tecnologias, :orden)',
            [
                ':titulo'      => $d['titulo'],
                ':descripcion' => $d['descripcion'],
                ':icono'       => $d['icono'] !== '' ? $d['icono'] : '🔗',
                ':enlace'      => $d['enlace'] ?? '',
                ':categoria'   => in_array($d['categoria'] ?? 'web', ['web', 'app', 'otro'], true)
                    ? $d['categoria'] : 'web',
                ':tecnologias' => $d['tecnologias'] ?? '',
                ':orden'       => (int) ($d['orden'] ?? 0),
            ]
        );
    }

    public function actualizar(int $id, array $d): bool
    {
        $stmt = $this->ejecutar(
            'UPDATE proyectos
                SET titulo = :titulo, descripcion = :descripcion, icono = :icono,
                    enlace = :enlace, categoria = :categoria, tecnologias = :tecnologias,
                    orden = :orden
              WHERE id = :id',
            [
                ':id'          => $id,
                ':titulo'      => $d['titulo'],
                ':descripcion' => $d['descripcion'],
                ':icono'       => $d['icono'] !== '' ? $d['icono'] : '🔗',
                ':enlace'      => $d['enlace'] ?? '',
                ':categoria'   => in_array($d['categoria'] ?? 'web', ['web', 'app', 'otro'], true)
                    ? $d['categoria'] : 'web',
                ':tecnologias' => $d['tecnologias'] ?? '',
                ':orden'       => (int) ($d['orden'] ?? 0),
            ]
        );

        return $stmt->rowCount() > 0;
    }
}
