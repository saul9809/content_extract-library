<?php

namespace ContentProcessor\Utils;

/**
 * Text Segmentation Utility
 * 
 * Converts normalized text into semantic fragments for schema-aware alignment.
 * Fragments represent short, comparable text units that can be matched to schema fields.
 * 
 * Segmentation strategy (applied in order):
 * 1. Split by newlines (highest priority - often indicates new fields)
 * 2. Split by colon patterns "key: value" (common in structured text)
 * 3. Split by bullet points (-, •, *, etc.)
 * 4. Split by numbered lists (1., 2., etc.)
 * 5. Create phrase windows (max 8-12 words per fragment)
 * 
 * Key properties:
 * - No NLP, no inference
 * - Fragments preserve order
 * - Deterministic output
 * - Domain-agnostic
 * 
 * @package ContentProcessor\Utils
 * @since 1.4.0 (Semantic Structuring Phase)
 */
class TextSegmenter
{
    /**
     * Maximum words per fragment (phrase window)
     * @var int
     */
    private int $maxWordsPerFragment = 12;

    /**
     * Minimum words to create a fragment
     * @var int
     */
    private int $minWordsPerFragment = 1;

    /**
     * Constructor
     * 
     * @param int $maxWordsPerFragment Maximum words per fragment (default: 12)
     */
    public function __construct(int $maxWordsPerFragment = 12)
    {
        $this->maxWordsPerFragment = max(1, $maxWordsPerFragment);
    }

    /**
     * Segment normalized text into semantic fragments
     * 
     * @param string $normalizedText Normalized text from TextNormalizer
     * @return array<string> Array of text fragments (may contain empty strings from structure)
     */
    public function segment(string $normalizedText): array
    {
        if (empty(trim($normalizedText))) {
            return [];
        }

        $fragments = [];

        // Step 1: Split by significant structural markers
        $segments = $this->splitByStructure($normalizedText);

        // Step 2: For each segment, apply phrase windowing if needed
        foreach ($segments as $segment) {
            $segment = trim($segment);
            if (empty($segment)) {
                continue;
            }

            // If segment is short enough, keep as-is
            $wordCount = str_word_count($segment);
            if ($wordCount <= $this->maxWordsPerFragment) {
                $fragments[] = $segment;
            } else {
                // Create phrase windows
                $windows = $this->createPhraseWindows($segment);
                $fragments = array_merge($fragments, $windows);
            }
        }

        return array_filter($fragments, fn($f) => !empty(trim($f)));
    }

    /**
     * Split text by structural markers (high-level segmentation)
     * 
     * Applied in order:
     * 1. Newlines
     * 2. Colon patterns
     * 3. Bullet points
     * 4. Numbered lists
     * 
     * @param string $text Text to segment
     * @return array<string> Segments
     */
    private function splitByStructure(string $text): array
    {
        $segments = [];

        // Split by newlines first (highest priority)
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Try to split by colon pattern (key: value)
            if (preg_match('/^([^:]+):(.+)$/', $line, $matches)) {
                $segments[] = trim($matches[1]); // Field name
                $value = trim($matches[2]);
                if (!empty($value)) {
                    $segments[] = $value;
                }
                continue;
            }

            // Try to split by bullet points
            if (preg_match('/^[\s]*[-•*]\s+(.+)$/', $line, $matches)) {
                $segments[] = trim($matches[1]);
                continue;
            }

            // Try to split by numbered lists
            if (preg_match('/^[\s]*\d+\.\s+(.+)$/', $line, $matches)) {
                $segments[] = trim($matches[1]);
                continue;
            }

            // No special structure, keep as-is
            $segments[] = $line;
        }

        return $segments;
    }

    /**
     * Create phrase windows from long text
     * 
     * Breaks text into overlapping windows of ~8-12 words
     * to maintain semantic coherence while keeping fragments short
     * 
     * @param string $text Text to window
     * @return array<string> Phrase windows
     */
    private function createPhraseWindows(string $text): array
    {
        $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        $windows = [];

        $windowSize = $this->maxWordsPerFragment;
        $stepSize = max(1, (int)($windowSize / 2)); // 50% overlap

        for ($i = 0; $i < count($words); $i += $stepSize) {
            $window = array_slice($words, $i, $windowSize);
            if (count($window) >= $this->minWordsPerFragment) {
                $windows[] = implode(' ', $window);
            }
        }

        return $windows;
    }

    /**
     * Get configuration
     * 
     * @return array Configuration with max words per fragment
     */
    public function getConfig(): array
    {
        return [
            'max_words_per_fragment' => $this->maxWordsPerFragment,
            'min_words_per_fragment' => $this->minWordsPerFragment,
        ];
    }
}
