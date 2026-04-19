<?php

namespace ContentProcessor\Extractors;

use ContentProcessor\Contracts\ExtractorInterface;

/**
 * Simple extractor for plain text files.
 * 
 * Reads .txt files and converts them into a content array.
 * Designed for testing and as a base for more complex extractors.
 * 
 * In the future, will be extended to support PDF, OCR, etc.
 */
class TextFileExtractor implements ExtractorInterface
{
    private const SUPPORTED_EXTENSIONS = ['txt', 'text'];

    /**
     * {@inheritDoc}
     */
    public function extract(string $source): array
    {
        if (!file_exists($source)) {
            throw new \RuntimeException("File '$source' does not exist.");
        }

        if (!is_readable($source)) {
            throw new \RuntimeException("File '$source' is not readable.");
        }

        $content = file_get_contents($source);
        if ($content === false) {
            throw new \RuntimeException("Could not read file '$source'.");
        }

        // Return as array with one element (single page/section)
        // For multi-page PDFs, would be an array with multiple elements
        return [$content];
    }

    /**
     * {@inheritDoc}
     */
    public function canHandle(string $source): bool
    {
        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
        return in_array($extension, self::SUPPORTED_EXTENSIONS, true);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'TextFileExtractor';
    }
}
