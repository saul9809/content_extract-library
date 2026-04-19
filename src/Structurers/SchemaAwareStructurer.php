<?php

namespace ContentProcessor\Structurers;

use ContentProcessor\Contracts\StructurerInterface;
use ContentProcessor\Contracts\SchemaInterface;
use ContentProcessor\Models\Warning;
use ContentProcessor\Utils\TextNormalizer;
use ContentProcessor\Utils\TextSegmenter;

/**
 * Schema-Aware Structurer
 * 
 * Implements semantic alignment of text fragments to schema fields.
 * Uses optional field aliases to guide extraction and improve accuracy.
 * 
 * **Pipeline:**
 * 1. Normalize text (TextNormalizer)
 * 2. Segment into fragments (TextSegmenter)
 * 3. For each schema field:
 *    a. Read aliases (if defined)
 *    b. Match fragments against aliases
 *    c. Extract best match as field value
 * 4. Validate types
 * 5. Generate warnings for missing/ambiguous matches
 * 
 * **Schema Format (Backward Compatible):**
 * ```php
 * [
 *     'name' => [
 *         'type' => 'string',
 *         'required' => true,
 *         'aliases' => [  // OPTIONAL - new semantic feature
 *             'name',
 *             'full name',
 *             'client name',
 *         ],
 *     ],
 *     'age' => [
 *         'type' => 'integer',
 *         'required' => false,
 *         // No aliases - will use field name only
 *     ],
 * ]
 * ```
 * 
 * **Matching Strategy:**
 * - Exact match (normalized)
 * - Prefix match (normalized alias is prefix of fragment)
 * - Proximity match (alias followed by colon or similar pattern)
 * - Scoring system for ambiguity detection
 * 
 * **Warnings Generated:**
 * - 'missing': Required field not found in text
 * - 'ambiguous': Multiple matches with similar scores
 * - 'type_mismatch': Value doesn't match expected type
 * - 'incomplete': Optional field missing
 * 
 * This structurer is domain-agnostic and deterministic.
 * No semantic inference or domain assumptions are made.
 * 
 * @package ContentProcessor\Structurers
 * @since 1.4.0 (Semantic Structuring Phase)
 */
class SchemaAwareStructurer implements StructurerInterface
{
    /**
     * Text normalizer instance
     * @var TextNormalizer
     */
    private TextNormalizer $normalizer;

    /**
     * Text segmenter instance
     * @var TextSegmenter
     */
    private TextSegmenter $segmenter;

    /**
     * Collected warnings during structuring
     * @var array<Warning>
     */
    private array $warnings = [];

    /**
     * Matching threshold (0.0 to 1.0)
     * Fragments with score >= threshold are considered matches
     * @var float
     */
    private float $matchThreshold = 0.6;

    /**
     * Constructor
     * 
     * @param float $matchThreshold Matching similarity threshold (default: 0.6)
     * @param array $normalizerConfig Configuration for TextNormalizer
     * @param int $maxWordsPerFragment Maximum words per fragment (TextSegmenter)
     */
    public function __construct(
        float $matchThreshold = 0.6,
        array $normalizerConfig = [],
        int $maxWordsPerFragment = 12
    ) {
        $this->matchThreshold = max(0.0, min(1.0, $matchThreshold));
        $this->normalizer = new TextNormalizer($normalizerConfig);
        $this->segmenter = new TextSegmenter($maxWordsPerFragment);
    }

    /**
     * Structure content according to schema using alias-driven matching
     * 
     * @param array $content Extracted content (typically array with 'text' key)
     * @param SchemaInterface $schema Target schema with optional aliases
     * @return array Structured result with 'data' and 'warnings'
     */
    public function structure(array $content, SchemaInterface $schema): array
    {
        $this->warnings = [];
        $rawText = '';

        // Extract raw text from content
        if (is_string($content)) {
            $rawText = $content;
        } elseif (isset($content['text'])) {
            $rawText = $content['text'];
        } elseif (isset($content[0])) {
            $rawText = implode("\n", (array)$content);
        }

        // Normalize and segment text
        $normalizedText = $this->normalizer->normalize($rawText);
        $fragments = $this->segmenter->segment($normalizedText);

        // Extract data from fragments using schema
        $structuredData = $this->extractFromFragments($fragments, $schema);

        // Perform type conversion and validation
        $validatedData = $this->validateAndConvertTypes($structuredData, $schema);

        return [
            'data' => $validatedData,
            'warnings' => array_map(fn($w) => $w->toArray(), $this->warnings),
        ];
    }

    /**
     * Extract values from fragments using schema and aliases
     * 
     * @param array<string> $fragments Text fragments
     * @param SchemaInterface $schema Schema definition
     * @return array Extracted data
     */
    private function extractFromFragments(array $fragments, SchemaInterface $schema): array
    {
        $definition = $schema->getDefinition();
        $data = [];

        foreach ($definition as $fieldName => $fieldDef) {
            $aliases = $this->getFieldAliases($fieldName, $fieldDef);

            // Find best matching fragment for this field
            $match = $this->findBestMatch($fragments, $aliases);

            if ($match !== null) {
                $data[$fieldName] = $match['value'];

                // Warn about ambiguity if multiple close matches
                if ($match['ambiguous']) {
                    $this->warnings[] = Warning::create([
                        'field' => $fieldName,
                        'category' => 'ambiguous',
                        'message' => sprintf(
                            'Field "%s" found multiple times with similar match scores. ' .
                                'Chose the first match. Consider adding more specific aliases.',
                            $fieldName
                        ),
                    ]);
                }
            } else {
                // No match found
                if ($fieldDef['required'] ?? false) {
                    $this->warnings[] = Warning::create([
                        'field' => $fieldName,
                        'category' => 'missing',
                        'message' => sprintf(
                            'Required field "%s" not found in text. ' .
                                'Aliases checked: %s',
                            $fieldName,
                            implode(', ', array_map(fn($a) => "\"$a\"", $aliases))
                        ),
                    ]);
                } else {
                    $this->warnings[] = Warning::create([
                        'field' => $fieldName,
                        'category' => 'incomplete',
                        'message' => sprintf(
                            'Optional field "%s" not found in text.',
                            $fieldName
                        ),
                    ]);
                }
            }
        }

        return $data;
    }

    /**
     * Get aliases for a field from schema definition
     * 
     * Fallback strategy:
     * 1. Use explicit aliases if defined
     * 2. Use field name if no aliases
     * 
     * @param string $fieldName Field name from schema
     * @param array $fieldDef Field definition from schema
     * @return array<string> Normalized aliases
     */
    private function getFieldAliases(string $fieldName, array $fieldDef): array
    {
        $aliases = [];

        // Use explicit aliases if defined
        if (!empty($fieldDef['aliases']) && is_array($fieldDef['aliases'])) {
            $aliases = array_merge($aliases, $fieldDef['aliases']);
        }

        // Always include normalized field name as fallback alias
        $aliases[] = $fieldName;

        // Normalize all aliases
        $normalized = array_map(
            fn($alias) => $this->normalizer->normalize($alias),
            $aliases
        );

        return array_unique($normalized);
    }

    /**
     * Find best matching fragment for given aliases
     * 
     * Matching algorithm:
     * 1. Score each fragment against each alias
     * 2. Take the highest score
     * 3. Check if multiple fragments have similar scores (ambiguity)
     * 4. Return match or null if no match >= threshold
     * 
     * @param array<string> $fragments Available text fragments
     * @param array<string> $aliases Field aliases to match
     * @return array|null Match result with 'value', 'score', 'ambiguous' or null
     */
    private function findBestMatch(array $fragments, array $aliases): ?array
    {
        $scores = [];

        foreach ($fragments as $fragmentIdx => $fragment) {
            $bestAliasScore = 0.0;
            $bestAlias = '';

            foreach ($aliases as $alias) {
                $score = $this->calculateMatchScore($fragment, $alias);

                if ($score > $bestAliasScore) {
                    $bestAliasScore = $score;
                    $bestAlias = $alias;
                }
            }

            if ($bestAliasScore > 0) {
                $scores[$fragmentIdx] = [
                    'score' => $bestAliasScore,
                    'alias' => $bestAlias,
                    'fragment' => $fragment,
                ];
            }
        }

        if (empty($scores)) {
            return null;
        }

        // Sort by score (descending)
        usort($scores, fn($a, $b) => $b['score'] <=> $a['score']);

        $bestMatch = $scores[0];

        // Check if below threshold
        if ($bestMatch['score'] < $this->matchThreshold) {
            return null;
        }

        // Check for ambiguity (multiple matches with similar score)
        $ambiguous = false;
        if (count($scores) > 1) {
            $secondScore = $scores[1]['score'];
            $scoreDiff = $bestMatch['score'] - $secondScore;

            if ($scoreDiff < 0.15) { // Threshold for ambiguity
                $ambiguous = true;
            }
        }

        // Extract the value (part after the alias if colon-separated)
        $value = $this->extractValue($bestMatch['fragment'], $bestMatch['alias']);

        return [
            'value' => $value,
            'score' => $bestMatch['score'],
            'ambiguous' => $ambiguous,
        ];
    }

    /**
     * Calculate match score between fragment and alias
     * 
     * Scoring rules (0.0 to 1.0):
     * - Exact match: 1.0
     * - Prefix match: 0.9
     * - Substring match: 0.7
     * - Partial word match: 0.5
     * - No match: 0.0
     * 
     * @param string $fragment Text fragment (normalized)
     * @param string $alias Field alias (normalized)
     * @return float Match score
     */
    private function calculateMatchScore(string $fragment, string $alias): float
    {
        if (empty($alias) || empty($fragment)) {
            return 0.0;
        }

        // Exact match
        if ($fragment === $alias) {
            return 1.0;
        }

        // Prefix match (fragment starts with alias)
        if (str_starts_with($fragment, $alias)) {
            // Extra points if followed by colon or common delimiters
            $suffix = substr($fragment, strlen($alias));
            if (preg_match('/^[\s:;,=]/u', $suffix)) {
                return 0.95;
            }
            return 0.9;
        }

        // Suffix match (fragment ends with alias)
        if (str_ends_with($fragment, $alias)) {
            return 0.8;
        }

        // Substring match
        if (str_contains($fragment, $alias)) {
            return 0.7;
        }

        // Levenshtein distance for fuzzy matching
        $distance = levenshtein($alias, $fragment);
        $maxLen = max(strlen($alias), strlen($fragment));

        if ($distance <= 2 && $maxLen <= 15) { // Typo tolerance
            return 0.6;
        }

        return 0.0;
    }

    /**
     * Extract value from fragment after alias match
     * 
     * Strategies:
     * 1. If fragment contains colon, take part after colon
     * 2. Otherwise, trim the alias from fragment and keep remainder
     * 3. If nothing remains, return empty string
     * 
     * @param string $fragment Full fragment text
     * @param string $alias Matched alias
     * @return string Extracted value
     */
    private function extractValue(string $fragment, string $alias): string
    {
        // Try colon-based extraction first
        if (preg_match('/^([^:]*):(.+)$/', $fragment, $matches)) {
            return trim($matches[2]);
        }

        // Remove alias from fragment
        $value = trim(str_replace($alias, '', $fragment, $count));

        if ($count > 0 && !empty($value)) {
            return $value;
        }

        // Return the fragment as-is if extraction unsuccessful
        return trim($fragment);
    }

    /**
     * Validate types and convert values
     * 
     * @param array $data Extracted data
     * @param SchemaInterface $schema Schema with type definitions
     * @return array Type-converted data
     */
    private function validateAndConvertTypes(array $data, SchemaInterface $schema): array
    {
        $definition = $schema->getDefinition();
        $converted = [];

        foreach ($data as $fieldName => $value) {
            if (!isset($definition[$fieldName])) {
                $converted[$fieldName] = $value;
                continue;
            }

            $fieldDef = $definition[$fieldName];
            $type = $fieldDef['type'] ?? 'string';

            try {
                $converted[$fieldName] = $this->convertToType($value, $type);
            } catch (\Exception $e) {
                // Type conversion failed - generate warning and keep original
                $this->warnings[] = Warning::create([
                    'field' => $fieldName,
                    'category' => 'type_mismatch',
                    'message' => sprintf(
                        'Field "%s" value "%s" does not match type "%s". ' .
                            'Keeping original value. Error: %s',
                        $fieldName,
                        substr((string)$value, 0, 50),
                        $type,
                        $e->getMessage()
                    ),
                ]);
                $converted[$fieldName] = $value;
            }
        }

        return $converted;
    }

    /**
     * Convert string value to specific type
     * 
     * @param string $value Value to convert
     * @param string $type Target type
     * @return mixed Converted value
     * @throws \Exception If conversion fails
     */
    private function convertToType(string $value, string $type): mixed
    {
        $value = trim($value);

        return match ($type) {
            'string' => $value,
            'integer', 'int' => $this->toInteger($value),
            'float', 'double' => $this->toFloat($value),
            'boolean', 'bool' => $this->toBoolean($value),
            'array' => $this->toArray($value),
            default => $value,
        };
    }

    /**
     * Convert to integer
     * @throws \Exception
     */
    private function toInteger(string $value): int
    {
        if (!preg_match('/^-?\d+/', $value, $matches)) {
            throw new \Exception('No integer found in value');
        }
        return (int)$matches[0];
    }

    /**
     * Convert to float
     * @throws \Exception
     */
    private function toFloat(string $value): float
    {
        if (!preg_match('/^-?\d+\.?\d*/', $value, $matches)) {
            throw new \Exception('No numeric value found');
        }
        return (float)str_replace(',', '.', $matches[0]);
    }

    /**
     * Convert to boolean
     */
    private function toBoolean(string $value): bool
    {
        $truthy = ['true', 'yes', '1', 'on', 'enabled', 'active'];
        return in_array(mb_strtolower($value), $truthy);
    }

    /**
     * Convert to array (comma-separated or semicolon-separated)
     */
    private function toArray(string $value): array
    {
        if (preg_match('/[,;]/', $value)) {
            $separator = str_contains($value, ';') ? ';' : ',';
            return array_map('trim', explode($separator, $value));
        }
        return [$value];
    }

    /**
     * Returns the name of this structurer
     * 
     * @return string Name identifier
     */
    public function getName(): string
    {
        return 'schema-aware';
    }

    /**
     * Get warnings generated during structuring
     * 
     * @return array<Warning> Warning objects
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
