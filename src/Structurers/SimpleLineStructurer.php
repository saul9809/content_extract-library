<?php

namespace ContentProcessor\Structurers;

use ContentProcessor\Contracts\StructurerInterface;
use ContentProcessor\Contracts\SchemaInterface;

/**
 * Simple structurer with line-by-line parsing.
 * 
 * Parses text content into simple key:value pairs.
 * Expected format:
 *     field_name: value
 *     other_field: another value
 * 
 * Designed for testing and as a base for more sophisticated structurers.
 * In the future, will be extended with regex, AI, OCR, etc.
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

        // Combine all content (multiple "pages")
        $text = implode("\n", $content);

        // Simple parsing based on schema
        $structured = [];
        $definition = $schema->getDefinition();

        foreach ($definition as $fieldName => $rules) {
            // Search for lines that begin with the field name
            $pattern = '/^' . preg_quote($fieldName) . ':\s*(.*)$/mi';
            if (preg_match($pattern, $text, $matches)) {
                $value = trim($matches[1]);

                // Convert to expected type
                $type = $rules['type'] ?? 'string';
                $structured[$fieldName] = $this->castValue($value, $type);
            } else {
                // Field not found, use null
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
     * Converts a value to its corresponding type.
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
