<?php

namespace ContentProcessor\Contracts;

use ContentProcessor\Models\DocumentContext;
use ContentProcessor\Models\StructuredDocumentResult;

/**
 * Interfaz para estructuradores semánticos avanzados.
 * 
 * Extiende la capacidad del StructurerInterface tradicional
 * para soportar contexto de documentos y retornar resultados
 * con warnings (Bloque 3 - Estructuración Semántica).
 * 
 * Un Structurer que implemente esta interfaz puede:
 * - Acceder a metadatos del documento (ruta, nombre, etc.)
 * - Genera warnings para campos ambiguos o ausentes
 * - Retornar un StructuredDocumentResult con datos + warnings
 * 
 * Los Structurers que implementen solo StructurerInterface
 * seguirán funcionando sin cambios (backward compatibility).
 * 
 * @package ContentProcessor\Contracts
 * @since 1.2.0 (Bloque 3)
 */
interface SemanticStructurerInterface extends StructurerInterface
{
    /**
     * Estructura un documento en contexto, con soporte para warnings.
     * 
     * Este es el método principal para la estructuración semántica.
     * El Structurer recibe el DocumentContext (con metadatos y contenido),
     * aplica el Schema, y retorna un StructuredDocumentResult que incluye
     * tanto los datos estructurados como los warnings generados.
     * 
     * Los warnings son distintos de los errores:
     * - Errores técnicos (Bloque 2): archivos corrupt, no legibles, etc.
     * - Warnings semánticos (Bloque 3): campos ambiguos, valores incompletos, etc.
     * 
     * @param DocumentContext $context Contexto del documento
     * @param SchemaInterface $schema Esquema de estructuración
     * @return StructuredDocumentResult Resultado con data + warnings
     * @throws \Exception Si la estructuración resulta completamente imposible
     */
    public function structureWithContext(
        DocumentContext $context,
        SchemaInterface $schema
    ): StructuredDocumentResult;
}
