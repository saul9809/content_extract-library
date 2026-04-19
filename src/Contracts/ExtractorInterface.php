<?php

namespace ContentProcessor\Contracts;

/**
 * Interface for content extractors.
 * 
 * Defines the contract that any content extraction strategy must implement
 * for different sources (PDF, text, etc.).
 */
interface ExtractorInterface
{
    /**
     * Extracts content from a source.
     * 
     * @param string $source Path or identifier of the source
     * @return array Extracted content as array of strings (by page/section)
     * @throws \Exception If extraction fails
     */
    public function extract(string $source): array;

    /**
     * Validates that the source can be processed by this extractor.
     * 
     * @param string $source Path or identifier of the source
     * @return bool True if can be processed, false otherwise
     */
    public function canHandle(string $source): bool;

    /**
     * Returns the name of the extractor.
     * 
     * @return string Identifier name of the extractor
     */
    public function getName(): string;
}
