<?php

/**
 * ServicioCatalogoModel — Acceso a datos del catálogo de servicios técnicos.
 *
 * NOTA DE ARQUITECTURA: el módulo Servicio Técnico no cuenta con stored
 * procedures (a diferencia del resto del sistema). Este Model accede a la
 * BD con CONSULTAS PREPARADAS PDO directamente. Sigue siendo MVC estricto:
 * el SQL vive únicamente aquí, jamás en un Controller. Hereda de BaseModel
 * solo para reutilizar la conexión PDO compartida ($this->pdo).
 *
 * Todos los métodos atrapan PDOException, la registran y devuelven un valor
 * seguro — el Controller nunca recibe una excepción cruda.
 */
class ServicioCatalogoModel extends BaseModel
{
    protected string $table = 'servicios_catalogo';

    // ─────────────────────────────────────────────
    // LECTURA
    // ─────────────────────────────────────────────

    /** Todos los servicios — activos primero, luego por nombre. */
    public function findAll(): array
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT * FROM servicios_catalogo ORDER BY activo DESC, nombre ASC"
            );
            return array_map(
                fn($row) => ServicioCatalogoEntity::fromArray($row),
                $stmt->fetchAll()
            );
        } catch (\PDOException $e) {
            error_log('[ServicioCatalogoModel::findAll] ' . $e->getMessage());
            return [];
        }
    }

    /** Solo servicios activos — usado al armar el presupuesto de una orden. */
    public function findActivos(): array
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT * FROM servicios_catalogo WHERE activo = 1 ORDER BY nombre ASC"
            );
            return array_map(
                fn($row) => ServicioCatalogoEntity::fromArray($row),
                $stmt->fetchAll()
            );
        } catch (\PDOException $e) {
            error_log('[ServicioCatalogoModel::findActivos] ' . $e->getMessage());
            return [];
        }
    }

    /** Un servicio por id. Devuelve entidad vacía (Found = false) si no existe. */
    public function findById(int $id): ServicioCatalogoEntity
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM servicios_catalogo WHERE id = ? LIMIT 1"
            );
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            return $row
                ? ServicioCatalogoEntity::fromArray($row)
                : new ServicioCatalogoEntity();
        } catch (\PDOException $e) {
            error_log('[ServicioCatalogoModel::findById] ' . $e->getMessage());
            return new ServicioCatalogoEntity();
        }
    }

    // ─────────────────────────────────────────────
    // ESCRITURA
    // ─────────────────────────────────────────────

    /** Inserta un servicio. Devuelve el id nuevo, o 0 si falló. */
    public function insert(array $data): int
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO servicios_catalogo (nombre, descripcion, precio, categoria, activo)
                 VALUES (?, ?, ?, ?, 1)"
            );
            $stmt->execute([
                $data['nombre'],
                $data['descripcion'] ?: null,
                (float) $data['precio'],
                $data['categoria'],
            ]);
            return (int) $this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log('[ServicioCatalogoModel::insert] ' . $e->getMessage());
            return 0;
        }
    }

    /** Actualiza un servicio. true si la sentencia se ejecutó. */
    public function update(array $data): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE servicios_catalogo
                    SET nombre = ?, descripcion = ?, precio = ?, categoria = ?
                  WHERE id = ?"
            );
            return $stmt->execute([
                $data['nombre'],
                $data['descripcion'] ?: null,
                (float) $data['precio'],
                $data['categoria'],
                (int) $data['id'],
            ]);
        } catch (\PDOException $e) {
            error_log('[ServicioCatalogoModel::update] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Activa/desactiva un servicio (soft-delete). No hay borrado físico:
     * orden_servicio_items referencia este catálogo y se perdería historial.
     */
    public function toggleActivo(int $id, int $activo): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE servicios_catalogo SET activo = ? WHERE id = ?"
            );
            return $stmt->execute([$activo, $id]);
        } catch (\PDOException $e) {
            error_log('[ServicioCatalogoModel::toggleActivo] ' . $e->getMessage());
            return false;
        }
    }
}
