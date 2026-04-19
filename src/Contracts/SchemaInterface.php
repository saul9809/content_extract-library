<?php

namespace ContentProcessor\Contracts;

/**
 * Interface for structuring schemas.
 * 
 * Defines the contract that any schema definition strategy must implement
 * to validate and structure extracted content.
 */
interface SchemaInterface
{
    /**
     * Returns the schema definition.
     * 
     * @return array Schema definition (field structure and rules)
     */
    public function getDefinition(): array;

    /**
     * Validates that content conforms to this schema.
     * 
     * @param array $data Data to validate
     * @return array ['valid' => bool, 'errors' => array] Validation result
     */
    public function validate(array $data): array;

    /**
     * Returns the name of the schema.
     * 
     * @return string Identifier name of the schema
     */
    public function getName(): string;
}
