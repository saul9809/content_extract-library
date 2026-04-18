<?php

namespace ContentProcessor\Models;

/**
 * Contexto semántico de un documento.
 * 
 * Agrupa la información de referencia de un documento junto con
 * su contenido crudo extraído, para que el Structurer pueda
 * generar estructuras semánticas ricas en contexto.
 * 
 * En Bloque 3, el DocumentContext es la entrada principal para
 * la estructuración semántica. Permite que los Structurers accedan
 * tanto al contenido como a los metadatos del documento.
 * 
 * @package ContentProcessor\Models
 * @since 1.2.0 (Bloque 3)
 */
class DocumentContext
{
    /**
     * Ruta o identificador único del documento.
     * @var string
     */
    private string $documentPath;

    /**
     * Nombre amigable del documento (puede ser filename sin ruta).
     * @var string
     */
    private string $documentName;

    /**
     * Contenido crudo extraído por el Extractor.
     * Típicamente: array de strings (una por página o sección).
     * @var array
     */
    private array $rawText;

    /**
     * Metadatos adicionales del documento.
     * Ej: ['mime_type' => 'application/pdf', 'page_count' => 5, ...]
     * @var array
     */
    private array $metadata;

    /**
     * Constructor.
     * 
     * @param string $documentPath Ruta o identificador único
     * @param string $documentName Nombre amigable (ej: "curriculum.pdf")
     * @param array $rawText Contenido crudo extraído
     * @param array $metadata Metadatos opcionales
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
     * Obtiene el nombre del documento.
     * @return string
     */
    public function getDocumentName(): string
    {
        return $this->documentName;
    }

    /**
     * Obtiene el contenido crudo.
     * @return array
     */
    public function getRawText(): array
    {
        return $this->rawText;
    }

    /**
     * Obtiene el contenido crudo combinado en un string.
     * Útil para búsquedas y pattern matching.
     * @return string
     */
    public function getRawTextCombined(): string
    {
        return implode("\n", $this->rawText);
    }

    /**
     * Obtiene los metadatos.
     * 
     * Si se proporciona $key, obtiene un metadato específico.
     * Si no se proporciona, obtiene todos los metadatos.
     * 
     * @param string|null $key Clave del metadato (opcional)
     * @param mixed $default Valor por defecto si no existe
     * @return mixed Array de metadatos o valor específico
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
