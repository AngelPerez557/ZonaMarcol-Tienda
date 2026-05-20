<?php

class FavoritoModel extends BaseModel
{
    protected string $table      = 'favoritos';
    protected string $primaryKey = 'id';

    public function toggle(int $clienteId, int $productoId): int
    {
        $existe = $this->callSPSingle('sp_favoritos_isFavorito', [$clienteId, $productoId]);
        
        if ($existe && (int)$existe['es_favorito'] > 0) {
            // Ya es favorito — quitar
            $db = Conexion::getInstance();
            $stmt = $db->prepare("DELETE FROM favoritos WHERE cliente_id = ? AND producto_id = ?");
            $stmt->execute([$clienteId, $productoId]);
            return 0;
        } else {
            // No es favorito — agregar
            $db = Conexion::getInstance();
            $stmt = $db->prepare("INSERT IGNORE INTO favoritos (cliente_id, producto_id) VALUES (?, ?)");
            $stmt->execute([$clienteId, $productoId]);
            return 1;
        }
    }

    public function isFavorito(int $clienteId, int $productoId): bool
    {
        $row = $this->callSPSingle('sp_favoritos_isFavorito', [$clienteId, $productoId]);
        return $row && (int)$row['es_favorito'] > 0;
    }

    public function findByCliente(int $clienteId): array
    {
        return $this->callSP('sp_favoritos_findByCliente', [$clienteId]);
    }
}