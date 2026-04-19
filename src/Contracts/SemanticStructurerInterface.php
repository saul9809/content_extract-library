<?php

namespace ContentProcessor\Contracts;

use ContentProcessor\Models\DocumentContext;
use ContentProcessor\Models\StructuredDocumentResult;

/**
 * Interface for advanced semantic structurers.
 * 
 * Extends the capability of traditional StructurerInterface
 * to support document context and return results
 * with warnings (Block 3 - Semantic Structuring).
 * 
 * A Structurer implementing this interface can:
 * - Access document metadata (path, name, etc.)
 * - Generate warnings for ambiguousus or missing fields
 * - Return a StructuredDocumentResult with data + warnings
 * 
 * Structurers implementing only StructurerInterface
 * will continue working unchanged (backward compatibility).
 * 
 * @package ContentProcessor\Contracts
 * @since 1.2.0 (Block 3)
 */
interface SemanticStructurerInterface extends StructurerInterface
{
    /**
     * Structures a document in context, with support for warnings.
     * 
     * This is the main method for semantic structuring.
     * The Structurer receives the DocumentContext (with metadata and content),
     * applies the Schema, and returns a StructuredDocumentResult that includes
     * both the structured data and generated warnings.
     * 
     * Warnings are distinct from errors:
     * - Technical errors (Block 2): corrupt files, unreadable, etc.
     * - Semantic warnings (Block 3): ambiguousus fields, incomplete values, etc.
     * 
     * @param DocumentContext $context Document context
     * @param SchemaInterface $schema Structuring schema
     * @return StructuredDocumentResult Result with data + warnings
     * @throws \Exception If structuring is completely impossible
     */
    public function structureWithContext(
        DocumentContext $context,
        SchemaInterface $schema
    ): StructuredDocumentResult;
}
