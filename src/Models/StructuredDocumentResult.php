<?php

namespace ContentProcessor\Models;

/**
 * Resultado de la estructuración semántica de un documento.
 * 
 * Encapsula el JSON estructurado junto con los warnings generados
 * durante el procesamiento semántico (Bloque 3).
 * 
 * A diferencia de los errores técnicos (Bloque 2), los warnings
 * son advertencias semánticas: campos ambiguos, valores ausentes,
 * información incompleta, etc.
 * 
 * Los warnings NO impiden que el documento se procese exitosamente,
 * pero alertan al usuario técnico sobre la calidad de los datos.
 * 
 * @package ContentProcessor\Models
 * @since 1.2.0 (Bloque 3)
 */
class StructuredDocumentResult
{
    /**
     * Nombre del documento procesado.
     * @var string
     */
    private string $documentName;

    /**
     * Datos estructurados en JSON (como array asociativo).
     * Debe cumplir el Schema especificado.
     * @var array
     */
    private array $data;

    /**
     * Array de warnings semánticos generados durante la estructuración.
     * Estructura: ['field_name' => 'descripción del warning', ...]
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
     * @param string $documentName Nombre del documento
     * @param array $data Datos estructurados
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
     * Obtiene el nombre del documento.
     * @return string
     */
    public function getDocumentName(): string
    {
        return $this->documentName;
    }

    /**
     * Obtiene los datos estructurados.
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Obtiene los datos como JSON string.
     * @param int $flags Flags de json_encode (ej: JSON_PRETTY_PRINT)
     * @return string
     */
    public function toJSON(int $flags = 0): string
    {
        return json_encode($this->data, $flags | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obtiene un campo específico de los datos.
     * 
     * @param string $field Nombre del campo (soporta notación punto: "person.name")
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
     * @param string $field Campo relacionado al warning
     * @param string $message Descripción del warning
     * @return self (fluent)
     */
    public function addWarning(string $field, string $message): self
    {
        $this->warnings[$field] = $message;
        return $this;
    }

    /**
     * Obtiene el timestamp de procesamiento.
     * @return int
     */
    public function getProcessedAt(): int
    {
        return $this->processedAt;
    }

    /**
     * Convierte el resultado a array para serialización.
     * Incluye data y warnings en una estructura plana.
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
