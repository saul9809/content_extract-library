<?php

namespace ContentProcessor\Structurers;

use ContentProcessor\Contracts\StructurerInterface;
use ContentProcessor\Contracts\SemanticStructurerInterface;
use ContentProcessor\Contracts\SchemaInterface;
use ContentProcessor\Models\DocumentContext;
use ContentProcessor\Models\StructuredDocumentResult;

/**
 * Estructurador basado en reglas deterministas.
 * 
 * Implementa un sistema de reglas simples para transformar texto crudo
 * en JSON estructurado, sin usar IA, OCR ni heurísticas complejas.
 * 
 * **Enfoque:**
 * - Lee el texto crudo línea por línea
 * - Busca patrones simples: "field_name: value"
 * - Detecta campos ambiguos (múltiples coincidencias) como warnings
 * - Detecta campos ausentes como warnings
 * - Convierte tipos básicos (string → int, float, bool, etc.)
 * 
 * **Ejemplo de entrada:**
 * ```
 * name: Juan Pérez
 * age: 30
 * email: juan@example.com
 * ```
 * 
 * **Schema esperado:**
 * ```php
 * [
 *     'name' => ['type' => 'string', 'required' => true],
 *     'age' => ['type' => 'integer', 'required' => true],
 *     'email' => ['type' => 'string', 'required' => false],
 * ]
 * ```
 * 
 * **Warnings generados:**
 * - 'name': "Campo encontrado múltiples veces (ambiguo)"
 * - 'age': "Campo requerido no encontrado"
 * - etc.
 * 
 * Bloque 3: Estructuración Semántica
 * @package ContentProcessor\Structurers
 * @since 1.2.0
 */
class RuleBasedStructurer implements SemanticStructurerInterface
{
    /**
     * Delimitador de campo estándar.
     * @var string
     */
    private string $fieldDelimiter = ':';

    /**
     * Si usar case-insensitive en la búsqueda de campos.
     * @var bool
     */
    private bool $caseInsensitive = true;

    /**
     * Si limpiar valores (trim, espacios múltiples).
     * @var bool
     */
    private bool $cleanValues = true;

    /**
     * Constructor.
     * 
     * @param string $fieldDelimiter El delimitador usado en el texto
     * @param bool $caseInsensitive Búsqueda case-insensitive
     * @param bool $cleanValues Limpiar valores
     */
    public function __construct(
        string $fieldDelimiter = ':',
        bool $caseInsensitive = true,
        bool $cleanValues = true
    ) {
        $this->fieldDelimiter = $fieldDelimiter;
        $this->caseInsensitive = $caseInsensitive;
        $this->cleanValues = $cleanValues;
    }

    /**
     * {@inheritDoc}
     * 
     * Método heredado de StructurerInterface para compatibilidad.
     * Usa el método nuevo structureWithContext() internamente.
     */
    public function structure(array $content, SchemaInterface $schema): array
    {
        try {
            $context = new DocumentContext('[array]', 'array-content', $content);
            $result = $this->structureWithContext($context, $schema);
            return $result->getData();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * {@inheritDoc}
     * 
     * Método nuevo que soporta contexto y retorna warnings.
     */
    public function structureWithContext(
        DocumentContext $context,
        SchemaInterface $schema
    ): StructuredDocumentResult {
        $text = $context->getRawTextCombined();
        $definition = $schema->getDefinition();
        $structured = [];
        $warnings = [];

        // Procesa cada field en el schema
        foreach ($definition as $fieldName => $rules) {
            [$value, $warning] = $this->extractField($fieldName, $text, $rules);

            $structured[$fieldName] = $value;

            if ($warning) {
                $warnings[$fieldName] = $warning;
            }
        }

        return new StructuredDocumentResult(
            $context->getDocumentName(),
            $structured,
            $warnings
        );
    }

    /**
     * Extrae un campo del texto según las reglas.
     * 
     * Retorna un array: [valor_extraído, warning_o_null]
     * 
     * @param string $fieldName Nombre del field
     * @param string $text Texto crudo combind
     * @param array $rules Reglas del field del schema
     * @return array [$value, $warning]
     */
    private function extractField(string $fieldName, string $text, array $rules): array
    {
        $type = $rules['type'] ?? 'string';
        $required = $rules['required'] ?? false;

        // Busca el patrón "field_name: value"
        $pattern = $this->buildPattern($fieldName);
        $matches = [];
        preg_match_all($pattern, $text, $matches);

        // Si no hay coincidencias
        if (empty($matches[1])) {
            $warning = $required
                ? "Campo requerido no encontrado"
                : "Campo opcional no encontrado";
            return [null, $warning];
        }

        // Si hay múltiples coincidencias (ambiguo)
        if (count($matches[1]) > 1) {
            $value = $this->castValue($matches[1][0], $type);
            $warning = "Campo encontrado múltiples veces (ambiguo). Se usó el primero.";
            return [$value, $warning];
        }

        // Coincidencia única (caso ideal)
        $value = $this->castValue($matches[1][0], $type);
        return [$value, null];
    }

    /**
     * Construye el patrón regex para buscar un field.
     * 
     * @param string $fieldName Nombre del field
     * @return string Patrón regex
     */
    private function buildPattern(string $fieldName): string
    {
        // Escapa caracteres especiales en el nombre del field
        $escaped = preg_quote($fieldName, '/');
        $delimiter = preg_quote($this->fieldDelimiter, '/');

        $flags = $this->caseInsensitive ? 'ims' : 'ms';

        // Patrón: "^fieldName: (.*)$" (multiline)
        return "/{$escaped}{$delimiter}\s*(.*)$/{$flags}";
    }

    /**
     * Convierte un valor string al tipo especificado.
     * 
     * @param string $value Valor a convertir
     * @param string $type Tipo destino (string, integer, float, boolean, array)
     * @return mixed
     */
    private function castValue(string $value, string $type)
    {
        if ($this->cleanValues) {
            $value = trim($value);
            $value = preg_replace('/\s+/', ' ', $value);
        }

        if (empty($value)) {
            return match ($type) {
                'integer', 'int' => 0,
                'float' => 0.0,
                'boolean', 'bool' => false,
                'array' => [],
                default => '',
            };
        }

        return match ($type) {
            'integer', 'int' => (int)$value,
            'float' => (float)$value,
            'boolean', 'bool' => $this->parseBoolean($value),
            'array' => $this->parseArray($value),
            default => (string)$value,
        };
    }

    /**
     * Parsea un boolean desde string.
     * 
     * @param string $value
     * @return bool
     */
    private function parseBoolean(string $value): bool
    {
        $truthy = ['true', 'yes', 'on', '1', 'verdadero', 'sí'];
        return in_array(strtolower($value), $truthy, true);
    }

    /**
     * Parsea un array desde string.
     * 
     * Soporta:
     * - CSV: "item1, item2, item3"
     * - JSON: "[\"item1\", \"item2\"]"
     * 
     * @param string $value
     * @return array
     */
    private function parseArray(string $value): array
    {
        // Intenta JSON primero
        $json = @json_decode($value, true);
        if (is_array($json)) {
            return $json;
        }

        // Fallback: CSV
        return array_map('trim', explode(',', $value));
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'RuleBasedStructurer';
    }
}
