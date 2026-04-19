<?php

namespace ContentProcessor\Utils;

/**
 * Text Normalization Utility
 * 
 * Provides a reusable text normalization pipeline for semantic alignment.
 * Cleans and standardizes extracted text for accurate field matching.
 * 
 * Operations performed:
 * - Convert to lowercase
 * - Normalize whitespace (trim, collapse multiple spaces)
 * - Remove control characters and invisible characters
 * - Normalize punctuation
 * - Optional accent removal (safe, configurable)
 * 
 * This component is domain-agnostic and deterministic.
 * No semantic inference or domain-specific logic is applied.
 * 
 * @package ContentProcessor\Utils
 * @since 1.4.0 (Semantic Structuring Phase)
 */
class TextNormalizer
{
    /**
     * Configuration for normalization behavior
     * @var array
     */
    private array $config = [
        'lowercase' => true,
        'trim_whitespace' => true,
        'collapse_spaces' => true,
        'remove_control_chars' => true,
        'normalize_punctuation' => true,
        'remove_accents' => false, // Safe by default
    ];

    /**
     * Constructor
     * 
     * @param array $config Optional configuration overrides
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Normalize raw extracted text
     * 
     * @param string $rawText Raw text from extractor
     * @return string Normalized text
     */
    public function normalize(string $rawText): string
    {
        $text = $rawText;

        // Step 1: Remove control characters and invisible characters
        if ($this->config['remove_control_chars']) {
            $text = $this->removeControlCharacters($text);
        }

        // Step 2: Convert to lowercase
        if ($this->config['lowercase']) {
            $text = mb_strtolower($text, 'UTF-8');
        }

        // Step 3: Normalize whitespace
        if ($this->config['trim_whitespace']) {
            $text = trim($text);
        }

        // Step 4: Collapse multiple spaces and normalize line breaks
        if ($this->config['collapse_spaces']) {
            $text = $this->collapseWhitespace($text);
        }

        // Step 5: Normalize punctuation
        if ($this->config['normalize_punctuation']) {
            $text = $this->normalizePunctuation($text);
        }

        // Step 6: Optional accent removal
        if ($this->config['remove_accents']) {
            $text = $this->removeAccents($text);
        }

        return $text;
    }

    /**
     * Normalize a field name for alias matching
     * 
     * Field names need extra care to match with extracted values:
     * - Remove prefixes like "campo_", "field_"
     * - Normalize spacing
     * - Keep semantic meaning
     * 
     * @param string $fieldName Field name from schema
     * @return string Normalized field name
     */
    public function normalizeFieldName(string $fieldName): string
    {
        $name = $fieldName;

        // Apply base normalization
        $name = $this->normalize($name);

        // Remove common prefixes
        $prefixes = ['campo_', 'field_', 'prop_', 'attribute_'];
        foreach ($prefixes as $prefix) {
            if (str_starts_with($name, $prefix)) {
                $name = substr($name, strlen($prefix));
            }
        }

        return trim($name);
    }

    /**
     * Remove control characters and invisible Unicode characters
     * 
     * @param string $text Text to clean
     * @return string Cleaned text
     */
    private function removeControlCharacters(string $text): string
    {
        // Remove null bytes
        $text = str_replace("\0", '', $text);

        // Remove control characters (except newline, tab, carriage return)
        $text = preg_replace('/[\x00-\x08\x0E-\x1F\x7F]/u', '', $text);

        // Remove invisible Unicode characters (zero-width spaces, etc.)
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text);

        return $text;
    }

    /**
     * Collapse multiple whitespace and normalize line breaks
     * 
     * @param string $text Text to process
     * @return string Processed text
     */
    private function collapseWhitespace(string $text): string
    {
        // Normalize line breaks to \n
        $text = preg_replace('/\r\n|\r/', "\n", $text);

        // Collapse multiple spaces
        $text = preg_replace('/ {2,}/', ' ', $text);

        // Collapse multiple newlines (allow up to 2 for paragraph breaks)
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        // Trim trailing spaces from each line
        $lines = explode("\n", $text);
        $lines = array_map('rtrim', $lines);
        $text = implode("\n", $lines);

        return trim($text);
    }

    /**
     * Normalize punctuation for better semantic matching
     * 
     * @param string $text Text to process
     * @return string Processed text
     */
    private function normalizePunctuation(string $text): string
    {
        // Normalize various dash types to single hyphen
        $text = preg_replace('/[–—−]/u', '-', $text);

        // Normalize quotes
        $text = preg_replace('/[""]/u', '"', $text);
        $text = preg_replace('/['']/u', "'", $text);

        // Remove trailing punctuation from lines (except for meaningful delimiters)
        // This helps with "field:" vs "field :" matching
        $text = preg_replace('/(\w+)\s+:/', '$1:', $text);

        return $text;
    }

    /**
     * Remove accents and diacritical marks
     * 
     * This is optional and safe - converts accented characters to base forms
     * Example: "Pérez" -> "Perez", "naïve" -> "naive"
     * 
     * @param string $text Text to process
     * @return string Text without accents
     */
    private function removeAccents(string $text): string
    {
        // Use intl extension if available (most reliable)
        if (extension_loaded('intl')) {
            $transliterator = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC');
            if ($transliterator !== null) {
                return $transliterator->transliterate($text);
            }
        }

        // Fallback: manual mapping
        $normalized = preg_replace_callback('/./u', function ($matches) {
            $char = $matches[0];
            $decomposed = @\iconv('UTF-8', 'ASCII//TRANSLIT', $char);
            return ($decomposed !== false && $decomposed !== '') ? $decomposed : $char;
        }, $text);

        return $normalized ?? $text;
    }

    /**
     * Get normalization configuration
     * 
     * @return array Current configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
