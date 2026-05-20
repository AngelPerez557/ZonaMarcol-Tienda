<?php

abstract class BaseModel
{
    // Instancia PDO compartida — viene del Singleton de Conexion.php
    protected PDO $pdo;

    // Nombre de la tabla — cada modelo hijo lo define
    // Ej: protected string $table = 'users';
    protected string $table;

    // Prefijo estándar para todos los procedimientos almacenados
    // Ej: 'sp_users_findAll' → 'sp_' + 'users' + '_findAll'
    protected string $spPrefix;

    // Columna primaria — por defecto 'id', sobreescribible en cada modelo
    protected string $primaryKey = 'id';

    // ─────────────────────────────────────────────
    // CONSTRUCTOR
    // Obtiene la conexión PDO del Singleton y
    // construye automáticamente el prefijo de SPs
    // basándose en el nombre de la tabla
    // ─────────────────────────────────────────────
    public function __construct()
    {
        $this->pdo      = Conexion::getInstance();
        // Construye el prefijo automáticamente
        // Ej: tabla 'users' → prefijo 'sp_users'
        $this->spPrefix = 'sp_' . $this->table;
    }

    // ─────────────────────────────────────────────
    // EJECUTOR CENTRAL DE PROCEDIMIENTOS ALMACENADOS
    // Todos los métodos CRUD pasan por aquí
    // Centraliza el manejo de errores en un solo punto
    // ─────────────────────────────────────────────

    // Ejecuta un SP y retorna múltiples filas
    // Ej: $this->callSP('sp_users_findAll') → array de filas
    protected function callSP(string $spName, array $params = []): array
    {
        try {
            // Construye CALL sp_nombre(?, ?, ?) con tantos ? como parámetros
            $placeholders = count($params) > 0
                ? '(' . implode(', ', array_fill(0, count($params), '?')) . ')'
                : '()';

            $stmt = $this->pdo->prepare("CALL {$spName}{$placeholders}");
            $stmt->execute($params);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            $this->handleError($spName, $e);
            return [];
        }
    }

    // Ejecuta un SP y retorna una única fila
    // Retorna null si no hay resultados
    // Ej: $this->callSPSingle('sp_users_findById', [5]) → array | null
    protected function callSPSingle(string $spName, array $params = []): ?array
    {
        try {
            $placeholders = count($params) > 0
                ? '(' . implode(', ', array_fill(0, count($params), '?')) . ')'
                : '()';

            $stmt = $this->pdo->prepare("CALL {$spName}{$placeholders}");
            $stmt->execute($params);
            $row = $stmt->fetch();
            return $row ?: null;

        } catch (PDOException $e) {
            $this->handleError($spName, $e);
            return null;
        }
    }

    // Ejecuta un SP que no retorna filas (INSERT, UPDATE, DELETE)
    // Retorna el número de filas afectadas
    // Ej: $this->callSPExecute('sp_users_delete', [5]) → 1
    protected function callSPExecute(string $spName, array $params = []): int
    {
        try {
            $placeholders = count($params) > 0
                ? '(' . implode(', ', array_fill(0, count($params), '?')) . ')'
                : '()';

            $stmt = $this->pdo->prepare("CALL {$spName}{$placeholders}");
            $stmt->execute($params);
            return $stmt->rowCount();

        } catch (PDOException $e) {
            $this->handleError($spName, $e);
            return 0;
        }
    }

    // Ejecuta un SP de INSERT y retorna el último ID insertado
    // Ej: $this->callSPInsert('sp_users_insert', ['Juan', 'juan@mail.com']) → 15
    protected function callSPInsert(string $spName, array $params = []): int
    {
        try {
            $placeholders = count($params) > 0
                ? '(' . implode(', ', array_fill(0, count($params), '?')) . ')'
                : '()';

            $stmt = $this->pdo->prepare("CALL {$spName}{$placeholders}");
            $stmt->execute($params);

            // Algunos motores retornan el ID como resultado del SP
            // Si no, usa lastInsertId()
            $row = $stmt->fetch();
            if ($row && isset($row['id'])) {
                return (int) $row['id'];
            }

            return (int) $this->pdo->lastInsertId();

        } catch (PDOException $e) {
            $this->handleError($spName, $e);
            return 0;
        }
    }

    // ─────────────────────────────────────────────
    // CRUD GENÉRICO — Usa la convención de nombres de SPs
    // Cada modelo puede sobreescribir estos métodos
    // si necesita lógica adicional
    // ─────────────────────────────────────────────

    // Retorna todos los registros llamando a sp_tabla_findAll
    public function findAll(): mixed
    {
        return $this->callSP("{$this->spPrefix}_findAll");
    }

    /// Retorna un registro por ID llamando a sp_tabla_findById
    // Tipo mixed permite que los modelos hijos retornen entidades tipadas
    public function findById(int $id): mixed
    {
        return $this->callSPSingle("{$this->spPrefix}_findById", [$id]);
    }

    // Retorna registros por columna llamando a sp_tabla_findBy
    public function findBy(string $column, mixed $value): mixed
    {
        return $this->callSP("{$this->spPrefix}_findBy", [$column, $value]);
    }

    // Elimina un registro llamando a sp_tabla_delete
    public function delete(int $id): bool
    {
        return $this->callSPExecute("{$this->spPrefix}_delete", [$id]) > 0;
    }

    // Retorna el total de registros llamando a sp_tabla_count
    public function count(): int
    {
        $row = $this->callSPSingle("{$this->spPrefix}_count");
        return $row ? (int) $row['total'] : 0;
    }

    // Verifica si existe un registro llamando a sp_tabla_exists
    public function exists(int $id): bool
    {
        $row = $this->callSPSingle("{$this->spPrefix}_exists", [$id]);
        return $row ? (int) $row['existe'] === 1 : false;
    }

    // ─────────────────────────────────────────────
    // TRANSACCIONES
    // Para operaciones que requieren múltiples SPs
    // en una sola unidad atómica
    // ─────────────────────────────────────────────

    // Inicia una transacción — útil cuando un proceso
    // llama a varios SPs que deben ejecutarse todos o ninguno
    protected function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    // Confirma todos los cambios de la transacción
    protected function commit(): void
    {
        $this->pdo->commit();
    }

    // Revierte todos los cambios si algo falla
    protected function rollback(): void
    {
        $this->pdo->rollBack();
    }

    // ─────────────────────────────────────────────
    // MANEJO DE ERRORES
    // ─────────────────────────────────────────────

    // Centraliza el manejo de errores de PDO
    // En desarrollo muestra el error completo
    // En producción lo registra en log y lanza excepción genérica
    private function handleError(string $spName, PDOException $e): void
    {
        if (APP_ENV === 'development') {
            // En desarrollo muestra el error completo para depuración
            throw new RuntimeException(
                "Error ejecutando '{$spName}': " . $e->getMessage()
            );
        }

        // En producción registra el error en el log de PHP
        // sin exponer detalles al usuario
        error_log("[BaseModel] Error en '{$spName}': " . $e->getMessage());

        throw new RuntimeException(
            "Error en la operación. Contactá al administrador."
        );
    }
}