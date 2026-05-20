<?php

class VarianteEntity extends BaseEntity
{
    // ─────────────────────────────────────────────
    // PROPIEDADES — idénticas a columnas de la BD
    // ─────────────────────────────────────────────
    public ?int    $id            = null;
    public ?int    $producto_id   = null;
    public ?string $nombre        = null;
    public ?float  $precio        = null;
    public ?int    $stock         = 0;
    public ?string $codigo_barras = null;
    public ?string $image_url     = null;
    public ?int    $activo        = 1;
    public ?int    $orden         = 0;

    // Precio base heredado del producto — se llena desde el controlador
    public ?float $precio_base_producto = null;

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    // Retorna el precio efectivo de la variante
    // Si no tiene precio propio hereda el precio base del producto
    public function getPrecioEfectivo(): float
    {
        if ($this->precio !== null) {
            return (float) $this->precio;
        }
        return (float) ($this->precio_base_producto ?? 0);
    }

    // Retorna el precio efectivo formateado en lempiras
    public function getPrecioFormateado(): string
    {
        return 'L. ' . number_format($this->getPrecioEfectivo(), 2);
    }

    // Retorna true si la variante tiene stock disponible
    public function tieneStock(): bool
    {
        return (int) $this->stock > 0;
    }

    // Retorna true si la variante está activa
    public function isActivo(): bool
    {
        return (int) $this->activo === 1;
    }

    // Retorna la URL de la imagen o imagen por defecto
    public function getImageUrl(): string
    {
        if (!empty($this->image_url)) {
            return APP_URL . 'Content/Demo/img/Variantes/' . $this->image_url;
        }
        return APP_URL . 'Content/Demo/img/default/producto_default.svg';
    }

    // ─────────────────────────────────────────────
    // VALIDACIÓN
    // ─────────────────────────────────────────────
    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->nombre)) {
            $this->addError('El nombre de la variante es obligatorio.');
        }

        if ($this->stock < 0) {
            $this->addError('El stock no puede ser negativo.');
        }

        return !$this->hasErrors();
    }
}