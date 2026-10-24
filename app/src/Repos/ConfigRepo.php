<?php
/** Configuración general del sitio (pares clave/valor). */

declare(strict_types=1);

namespace App\Repos;

final class ConfigRepo extends Repositorio
{
    protected string $tabla = 'configuraciones';

    /** Devuelve todas las configuraciones como [clave => valor]. */
    public function comoArreglo(): array
    {
        $filas = $this->db->query('SELECT clave, valor FROM configuraciones ORDER BY clave')->fetchAll();
        $salida = [];
        foreach ($filas as $fila) {
            $salida[$fila['clave']] = $fila['valor'];
        }
        return $salida;
    }

    /** Guarda (o sobrescribe) una configuración concreta. */
    public function guardar(string $clave, string $valor): void
    {
        $this->ejecutar(
            'INSERT INTO configuraciones (clave, valor) VALUES (:clave, :valor)
             ON CONFLICT(clave) DO UPDATE SET valor = excluded.valor',
            [':clave' => $clave, ':valor' => $valor]
        );
    }

    /** Guarda varias configuraciones en una sola transacción. */
    public function guardarVarios(array $valores): void
    {
        $this->db->beginTransaction();
        try {
            foreach ($valores as $clave => $valor) {
                $this->guardar((string) $clave, (string) $valor);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
