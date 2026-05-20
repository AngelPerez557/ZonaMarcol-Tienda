<?php

class TemporadaModel extends BaseModel
{
    protected string $table      = 'temporadas';
    protected string $primaryKey = 'id';

    public function findAll(): array
    {
        $rows = $this->callSP('sp_temporadas_findAll');
        return array_map(fn($r) => TemporadaEntity::fromArray($r), $rows);
    }

    public function findActivas(): array
    {
        $rows = $this->callSP('sp_temporadas_findActivas');
        return array_map(fn($r) => TemporadaEntity::fromArray($r), $rows);
    }

    public function findAbiertas(): array
    {
        $rows = $this->callSP('sp_temporadas_findAbiertas');
        return array_map(fn($r) => TemporadaEntity::fromArray($r), $rows);
    }

    public function findById(int $id): TemporadaEntity
    {
        $row = $this->callSPSingle('sp_temporadas_findAll');
        // No hay SP single; usar findAll y filtrar (mejorar después con SP dedicado)
        foreach ($this->findAll() as $t) {
            if ((int) $t->id === $id) return $t;
        }
        return new TemporadaEntity();
    }

    public function cerrarVencidas(): void
    {
        $this->callSPExecute('sp_temporadas_cerrarVencidas');
    }
}
