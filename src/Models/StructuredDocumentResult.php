<?php

namespace ContentProcessor\Models;

/**
 * Resultado de la structureción semantic de un documento.
 * 
 * Encapsula el JSON structuredo junto con los warnings generados
 * durante el processing semántico (Block 3).
 * 
 * A diferencia de los errors técnicos (Block 2), los warnings
 * son warnings semantics: fields ambiguouss, valores ausentes,
 * information incompleta, etc.
 * 
 * Los warnings NO impiden que el documento se procese successfully,
 * pero alertan al usuario técnico sobre la calidad de los data.
 * 
 * @package ContentProcessor\Models
 * @since 1.2.0 (Block 3)
 */
class StructuredDocumentResult
{
    /**
     * Name del documento processed.
     * @var string
     */
    private string $documentName;

    /**
     * Data structuredos en JSON (como array asociativo).
     * Debe cumplir el Schema especificado.
     * @var array
     */
    private array $data;

    /**
     * Array de warnings semánticos generados during structureción.
     * Structure: ['field_name' => 'description del warning', ...]
     * @var array
     */
    private array $warnings;

    /**
     * Timestamp de cuándo se generó este resultado.
     * @var int
     */
    private int $processedAt;

    /**
     * Constructor.
     * 
     * @param string $documentName Name del documento
     * @param array $data Data structuredos
     * @param array $warnings Warnings semánticos (vacío si no hay)
     */
    public function __construct(
        string $documentName,
        array $data,
        array $warnings = []
    ) {
        $this->documentName = $documentName;
        $this->data = $data;
        $this->warnings = $warnings;
        $this->processedAt = time();
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
     * Obtiene los data structuredos.
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Obtiene los data como JSON string.
     * @param int $flags Flags de json_encode (ej: JSON_PRETTY_PRINT)
     * @return string
     */
    public function toJSON(int $flags = 0): string
    {
        return json_encode($this->data, $flags | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obtiene un field específico de los data.
     * 
     * @param string $field Name del field (soporta notación punto: "person.name")
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    public function getField(string $field, $default = null)
    {
        if (strpos($field, '.') === false) {
            return $this->data[$field] ?? $default;
        }

        // Notación punto: "person.name" → $data['person']['name']
        $parts = explode('.', $field);
        $current = $this->data;

        foreach ($parts as $part) {
            if (!is_array($current) || !isset($current[$part])) {
                return $default;
            }
            $current = $current[$part];
        }

        return $current;
    }

    /**
     * Obtiene los warnings.
     * @return array
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Cuenta los warnings.
     * @return int
     */
    public function getWarningsCount(): int
    {
        return count($this->warnings);
    }

    /**
     * Verifica si hay warnings.
     * @return bool
     */
    public function hasWarnings(): bool
    {
        return count($this->warnings) > 0;
    }

    /**
     * Añade un warning.
     * Útil para que el Structurer registre warnings durante el proceso.
     * 
     * @param string $field Field relacionado al warning
     * @param string $message Description del warning
     * @return self (fluent)
     */
    public function addWarning(string $field, string $message): self
    {
        $this->warnings[$field] = $message;
        return $this;
    }

    /**
     * Obtiene el timestamp de processing.
     * @return int
     */
    public function getProcessedAt(): int
    {
        return $this->processedAt;
    }

    /**
     * Convierte el resultado a array para serialización.
     * Incluye data y warnings en una structure plana.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'document' => $this->documentName,
            'data' => $this->data,
            'warnings' => $this->warnings,
            'warnings_count' => $this->getWarningsCount(),
            'processed_at' => $this->processedAt,
        ];
    }

    /**
     * Convierte a formato JSON pretty-printed.
     * Útil para debugging y visualización.
     * 
     * @return string
     */
    public function toPrettyJSON(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
