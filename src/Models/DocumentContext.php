<?php

namespace ContentProcessor\Models;

/**
 * Context semántico de un documento.
 * 
 * Agrupa la information de referencia de un documento junto con
 * su content crudo extracted, para que el Structurer pueda
 * generar structures semánticas ricas en context.
 * 
 * En Block 3, el DocumentContext es la entrada principal para
 * la structureción semántica. Permite que los Structurers accedan
 * tanto al content como a los metadata del documento.
 * 
 * @package ContentProcessor\Models
 * @since 1.2.0 (Block 3)
 */
class DocumentContext
{
    /**
     * Ruta o identificador único del documento.
     * @var string
     */
    private string $documentPath;

    /**
     * Name amigable del documento (can ser filename sin ruta).
     * @var string
     */
    private string $documentName;

    /**
     * Content crudo extracted por el Extractor.
     * Típicamente: array de strings (una por página o sección).
     * @var array
     */
    private array $rawText;

    /**
     * Metadata adicionales del documento.
     * Ej: ['mime_type' => 'application/pdf', 'page_count' => 5, ...]
     * @var array
     */
    private array $metadata;

    /**
     * Constructor.
     * 
     * @param string $documentPath Ruta o identificador único
     * @param string $documentName Name amigable (ej: "curriculum.pdf")
     * @param array $rawText Content crudo extracted
     * @param array $metadata Metadata opcionales
     */
    public function __construct(
        string $documentPath,
        string $documentName,
        array $rawText,
        array $metadata = []
    ) {
        $this->documentPath = $documentPath;
        $this->documentName = $documentName;
        $this->rawText = $rawText;
        $this->metadata = $metadata;
    }

    /**
     * Obtiene la ruta del documento.
     * @return string
     */
    public function getDocumentPath(): string
    {
        return $this->documentPath;
    }

    /**
     * Obtiene el name del documento.
     * @return string
     */
    public function getDocumentName(): string
    {
        return $this->documentName;
    }

    /**
     * Obtiene el content crudo.
     * @return array
     */
    public function getRawText(): array
    {
        return $this->rawText;
    }

    /**
     * Obtiene el content crudo combinado en un string.
     * Útil para búsquedas y pattern matching.
     * @return string
     */
    public function getRawTextCombined(): string
    {
        return implode("\n", $this->rawText);
    }

    /**
     * Obtiene los metadata.
     * 
     * Si se proporciona $key, obtiene un metadato específico.
     * Si no se proporciona, obtiene todos los metadata.
     * 
     * @param string|null $key Clave del metadato (opcional)
     * @param mixed $default Valor por defecto si does not exist
     * @return mixed Array de metadata o valor específico
     */
    public function getMetadata(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->metadata;
        }
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Verifica si la ruta cumple un patrón (glob).
     * Útil para filtrar documentos por tipo.
     * 
     * @param string $pattern Patrón glob (ej: "*.pdf", "*resume*")
     * @return bool
     */
    public function matchesPattern(string $pattern): bool
    {
        return fnmatch($pattern, $this->documentPath) || fnmatch($pattern, $this->documentName);
    }
}
