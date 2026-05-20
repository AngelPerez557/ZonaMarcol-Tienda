<?php

abstract class BaseEntity
{
    // Indica si el registro fue encontrado en la BD
    // false por defecto — se pone true cuando el SP retorna datos
    public bool $Found = false;

    // ─────────────────────────────────────────────
    // CONSTRUCTOR
    // Recibe el array de PDO y mapea las propiedades
    // ─────────────────────────────────────────────
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
            $this->Found = true;
        }
    }

    // ─────────────────────────────────────────────
    // FACTORY METHOD
    // Crea una instancia de la entidad hija desde
    // un array asociativo retornado por PDO
    // Ej: UserEntity::fromArray($row)
    // ─────────────────────────────────────────────
    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    // ─────────────────────────────────────────────
    // FILL — Mapeo directo de propiedades públicas
    // Asigna cada clave del array a la propiedad
    // correspondiente si existe en la entidad hija
    // ─────────────────────────────────────────────
    protected function fill(array $data): void
    {
        foreach ($data as $key => $value) {
            // Solo asigna si la propiedad existe en la entidad hija
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    // ─────────────────────────────────────────────
    // TO ARRAY
    // Convierte la entidad a array asociativo
    // Útil para pasar datos a los SPs o a la API
    // ─────────────────────────────────────────────
    public function toArray(): array
    {
        $data = [];
        // Obtiene todas las propiedades públicas de la entidad hija
        $reflect = new ReflectionClass($this);

        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();

            // Excluye la propiedad Found — es de control interno
            if ($name === 'Found') continue;

            $data[$name] = $this->$name;
        }

        return $data;
    }

    // ─────────────────────────────────────────────
    // TO JSON
    // Serializa la entidad a JSON para la API
    // ─────────────────────────────────────────────
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    // ─────────────────────────────────────────────
    // VALIDACIÓN
    // Cada entidad hija define sus propias reglas
    // ─────────────────────────────────────────────
    abstract public function isValid(): bool;

    // Array de errores de validación
    protected array $errors = [];

    // Retorna todos los errores de validación
    public function getErrors(): array
    {
        return $this->errors;
    }

    // Retorna el primer error de validación
    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    // Verifica si hay errores de validación
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    // Agrega un error de validación
    protected function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    // Limpia todos los errores de validación
    protected function clearErrors(): void
    {
        $this->errors = [];
    }
}