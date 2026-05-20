<?php

class ProductoEntity extends BaseEntity
{
    // ─────────────────────────────────────────────
    // PROPIEDADES — idénticas a columnas de la BD
    // ─────────────────────────────────────────────
    public ?int    $id               = null;
    public ?int    $categoria_id     = null;
    public ?string $categoria_nombre = null;
    public ?string $nombre           = null;
    public ?string $descripcion      = null;
    public ?float  $precio_base      = null;
    public ?int    $tiene_variantes  = 0;
    public ?int    $stock            = 0;
    public ?int    $visible_tienda   = 1;
    public ?string $image_url        = null;
    public ?int    $activo           = 1;
    public ?string $created_at       = null;
    public ?string $updated_at       = null;

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    // Retorna true si el producto está activo
    public function isActivo(): bool
    {
        return (int) $this->activo === 1;
    }
    
    public function isVisibleTienda(): bool
    {
        return (int) $this->visible_tienda === 1;
    }
    // Retorna true si el producto maneja variantes
    public function tieneVariantes(): bool
    {
        return (int) $this->tiene_variantes === 1;
    }

    // Retorna el precio formateado en lempiras
    // Ej: L. 150.00
    public function getPrecioFormateado(): string
    {
        if ($this->precio_base === null) {
            return 'Ver variantes';
        }
        return 'L. ' . number_format((float) $this->precio_base, 2);
    }

    // Retorna la URL de la imagen o una imagen por defecto
    public function getImageUrl(): string
    {
        if (!empty($this->image_url)) {
            return APP_URL . 'Content/Demo/img/Productos/' . $this->image_url;
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
            $this->addError('El nombre del producto es obligatorio.');
        }

        if (empty($this->categoria_id)) {
            $this->addError('La categoría es obligatoria.');
        }

        // Si no tiene variantes debe tener precio base y stock
        if (!$this->tieneVariantes()) {
            if ($this->precio_base === null || $this->precio_base <= 0) {
                $this->addError('El precio base es obligatorio para productos sin variantes.');
            }
            if ($this->stock === null || $this->stock < 0) {
                $this->addError('El stock no puede ser negativo.');
            }
        }

        return !$this->hasErrors();
    }
}