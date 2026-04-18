<?php

namespace ContentProcessor\Structurers;

use ContentProcessor\Contracts\StructurerInterface;
use ContentProcessor\Contracts\SchemaInterface;

/**
 * Estructurador simple con parsing línea-por-línea.
 * 
 * Parse contenido de texto en pares clave:valor simples.
 * Formato esperado:
 *     field_name: value
 *     other_field: another value
 * 
 * Diseñado para pruebas y como base para estructuradores más sofisticados.
 * En el futuro, se extenderá con regex, IA, OCR, etc.
 */
class SimpleLineStructurer implements StructurerInterface
{
    /**
     * {@inheritDoc}
     */
    public function structure(array $content, SchemaInterface $schema): array
    {
        if (empty($content)) {
            return [];
        }

        // Combina todo el contenido (múltiples "páginas")
        $text = implode("\n", $content);

        // Parse simple basado en el esquema
        $structured = [];
        $definition = $schema->getDefinition();

        foreach ($definition as $fieldName => $rules) {
            // Busca líneas que comiencen con el nombre del campo
            $pattern = '/^' . preg_quote($fieldName) . ':\s*(.*)$/mi';
            if (preg_match($pattern, $text, $matches)) {
                $value = trim($matches[1]);

                // Convierte al tipo esperado
                $type = $rules['type'] ?? 'string';
                $structured[$fieldName] = $this->castValue($value, $type);
            } else {
                // Campo no encontrado, usa null
                $structured[$fieldName] = null;
            }
        }

        return $structured;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'SimpleLineStructurer';
    }

    /**
     * Convierte un valor a su tipo correspondiente.
     * 
     * @param string $value
     * @param string $type
     * @return mixed
     */
    private function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'string' => $value,
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'array' => explode(',', $value),
            default => $value,
        };
    }
}
