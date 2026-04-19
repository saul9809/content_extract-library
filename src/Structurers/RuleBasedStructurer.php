<?php

namespace ContentProcessor\Structurers;

use ContentProcessor\Contracts\StructurerInterface;
use ContentProcessor\Contracts\SemanticStructurerInterface;
use ContentProcessor\Contracts\SchemaInterface;
use ContentProcessor\Models\DocumentContext;
use ContentProcessor\Models\StructuredDocumentResult;

/**
 * Rule-based structurer with deterministic rules.
 * 
 * Implements a simple rule system to transform raw text
 * into structured JSON, without using AI, OCR or complex heuristics.
 * 
 * **Approach:**
 * - Reads raw text line by line
 * - Searches for simple patterns: "field_name: value"
 * - Detects ambiguousus fields (multiple matches) as warnings
 * - Detects missing fields as warnings
 * - Converts basic types (string → int, float, bool, etc.)
 * 
 * **Input example:**
 * ```
 * name: Juan Perez
 * age: 30
 * email: juan@example.com
 * ```
 * 
 * **Expected schema:**
 * ```php
 * [
 *     'name' => ['type' => 'string', 'required' => true],
 *     'age' => ['type' => 'integer', 'required' => true],
 *     'email' => ['type' => 'string', 'required' => false],
 * ]
 * ```
 * 
 * **Generated warnings:**
 * - 'name': "Field found multiple times (ambiguousus)"
 * - 'age': "Required field not found"
 * - etc.
 * 
 * Block 3: Semantic Structuring
 * @package ContentProcessor\Structurers
 * @since 1.2.0
 */
class RuleBasedStructurer implements SemanticStructurerInterface
{
    /**
     * Field standard delimiter.
     * @var string
     */
    private string $fieldDelimiter = ':';

    /**
     * Whether to use case-insensitive field search.
     * @var bool
     */
    private bool $caseInsensitive = true;

    /**
     * Whether to clean values (trim, multiple spaces).
     * @var bool
     */
    private bool $cleanValues = true;

    /**
     * Constructor.
     * 
     * @param string $fieldDelimiter The delimiter used in the text
     * @param bool $caseInsensitive Case-insensitive search
     * @param bool $cleanValues Clean values
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
     * Inherited method from StructurerInterface for compatibility.
     * Uses the new structureWithContext() method internally.
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
     * New method that supports context and returns warnings.
     */
    public function structureWithContext(
        DocumentContext $context,
        SchemaInterface $schema
    ): StructuredDocumentResult {
        $text = $context->getRawTextCombined();
        $definition = $schema->getDefinition();
        $structured = [];
        $warnings = [];

        // Process each field in the schema
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
     * Extracts a field from the text according to the rules.
     * 
     * Returns an array: [extracted_value, warning_or_null]
     * 
     * @param string $fieldName Field name
     * @param string $text Combined raw text
     * @param array $rules Field rules from schema
     * @return array [$value, $warning]
     */
    private function extractField(string $fieldName, string $text, array $rules): array
    {
        $type = $rules['type'] ?? 'string';
        $required = $rules['required'] ?? false;

        // Search for the pattern "field_name: value"
        $pattern = $this->buildPattern($fieldName);
        $matches = [];
        preg_match_all($pattern, $text, $matches);

        // If there are no matches
        if (empty($matches[1])) {
            $warning = $required
                ? "Required field not found"
                : "Optional field not found";
            return [null, $warning];
        }

        // If there are multiple matches (us)
        if (count($matches[1]) > 1) {
            $value = $this->castValue($matches[1][0], $type);
            $warning = "Field found multiple times (ambiguousus). Using the first one.";
            return [$value, $warning];
        }

        // Single match (ideal case)
        $value = $this->castValue($matches[1][0], $type);
        return [$value, null];
    }

    /**
     * Builds the regex pattern to search for a field.
     * 
     * @param string $fieldName Field name
     * @return string Regex pattern
     */
    private function buildPattern(string $fieldName): string
    {
        // Escape special characters in the field name
        $escaped = preg_quote($fieldName, '/');
        $delimiter = preg_quote($this->fieldDelimiter, '/');

        $flags = $this->caseInsensitive ? 'ims' : 'ms';

        // Pattern: "^fieldName: (.*)$" (multiline)
        return "/{$escaped}{$delimiter}\s*(.*)$/{$flags}";
    }

    /**
     * Converts a string value to the specified type.
     * 
     * @param string $value Value to convert
     * @param string $type Target type (string, integer, float, boolean, array)
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
