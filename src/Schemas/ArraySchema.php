<?php

namespace ContentProcessor\Schemas;

use ContentProcessor\Contracts\SchemaInterface;

/**
 * Esquema basado en array de definiciones de campos.
 * 
 * Permite definir esquemas flexibles con validación básica.
 * 
 * @example
 * $schema = new ArraySchema([
 *     'nombre' => ['type' => 'string', 'required' => true],
 *     'carnet_identidad' => ['type' => 'string', 'required' => false],
 *     'anos_experiencia' => ['type' => 'int', 'required' => false],
 * ]);
 */
class ArraySchema implements SchemaInterface
{
    private array $definition = [];
    private string $name = 'ArraySchema';

    /**
     * @param array $definition Definición del esquema
     * @param string $name Nombre opcional del esquema
     */
    public function __construct(array $definition, string $name = 'ArraySchema')
    {
        $this->definition = $definition;
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinition(): array
    {
        return $this->definition;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(array $data): array
    {
        $errors = [];

        foreach ($this->definition as $fieldName => $rules) {
            $required = $rules['required'] ?? false;
            $type = $rules['type'] ?? 'string';
            $value = $data[$fieldName] ?? null;

            // Validación de requerido
            if ($required && ($value === null || $value === '')) {
                $errors[] = "Campo '$fieldName' es requerido.";
                continue;
            }

            // Validación de tipo
            if ($value !== null && $value !== '') {
                if (!$this->validateType($value, $type)) {
                    $errors[] = "Campo '$fieldName' debe ser de tipo '$type', pero es " . gettype($value) . ".";
                }
            }
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Valida el tipo de un valor.
     * 
     * @param mixed $value
     * @param string $type
     * @return bool
     */
    private function validateType(mixed $value, string $type): bool
    {
        return match ($type) {
            'string' => is_string($value),
            'int' => is_int($value) || (is_string($value) && ctype_digit($value)),
            'float' => is_float($value) || is_numeric($value),
            'bool' => is_bool($value),
            'array' => is_array($value),
            default => true,
        };
    }
}
