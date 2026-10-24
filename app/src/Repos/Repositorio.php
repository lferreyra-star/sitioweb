<?php
/**
 * Repositorio base: operaciones comunes sobre cualquier tabla.
 *
 * Todas las consultas usan sentencias preparadas (previene SQL injection).
 */

declare(strict_types=1);

namespace App\Repos;

use App\Database;
use PDO;

abstract class Repositorio
{
    protected PDO $db;

    /** Nombre de la tabla que gestiona cada repositorio concreto. */
    protected string $tabla = '';

    public function __construct()
    {
        $this->db = Database::conexion();
    }

    /** Listado completo de filas. */
    public function listar(string $orden = 'orden ASC, id ASC'): array
    {
        return $this->db
            ->query("SELECT * FROM {$this->tabla} ORDER BY {$orden}")
            ->fetchAll();
    }

    /** Una fila por ID o null si no existe. */
    public function obtener(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->tabla} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $fila = $stmt->fetch();

        return $fila ?: null;
    }

    /** Elimina una fila. Devuelve true si se borró algo. */
    public function eliminar(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->tabla} WHERE id = :id");
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /** Número total de filas. */
    public function contar(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM {$this->tabla}")->fetchColumn();
    }

    /** Ejecuta una sentencia preparada y devuelve su objeto PDOStatement. */
    protected function ejecutar(string $sql, array $parametros): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt;
    }
}
