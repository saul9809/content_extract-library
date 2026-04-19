<?php

namespace ContentProcessor\Contracts;

/**
 * Interface for content structurers.
 * 
 * Defines the contract that any transformation strategy must implement
 * to structure extracted content into a JSON format according to a schema.
 */
interface StructurerInterface
{
    /**
     * Structures the content according to the provided schema.
     * 
     * @param array $content Extracted content (array of strings)
     * @param SchemaInterface $schema Target schema
     * @return array Structured content conforming to the schema
     * @throws \Exception If structuring fails
     */
    public function structure(array $content, SchemaInterface $schema): array;

    /**
     * Returns the name of the structurer.
     * 
     * @return string Identifier name of the structurer
     */
    public function getName(): string;
}
