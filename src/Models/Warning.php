<?php

namespace ContentProcessor\Models;

/**
 * Normalización de warnings semánticos.
 * 
 * Los warnings son advertencias semánticas generadas durante
 * la estructuración de documentos (Bloque 3).
 * 
 * A diferencia de los errores, los warnings NO impiden
 * que el documento sea procesado exitosamente, pero alertan
 * al usuario técnico sobre la calidad o integridad de los datos.
 * 
 * Ejemplos:
 * - Campo requerido faltante o vacío
 * - Valor de tipo incorrecto (fue corregido automáticamente)
 * - Regla de parsing ambigua
 * - Información incompleta
 * 
 * @package ContentProcessor\Models
 * @since 1.3.0 (Bloque 4)
 */
class Warning
{
    /**
     * Campo o sección del documento donde se generó el warning.
     * @var string
     */
    private string $field;

    /**
     * Categoría del warning (ej: 'missing', 'ambiguous', 'incomplete')
     * @var string
     */
    private string $category;

    /**
     * Mensaje descriptivo del warning.
     * @var string
     */
    private string $message;

    /**
     * Valor que causó el warning (para debugging).
     * @var mixed
     */
    private $value;

    /**
     * Timestamp del warning.
     * @var int
     */
    private int $timestamp;

    /**
     * Constructor.
     * 
     * @param string $field Campo donde ocurre el warning
     * @param string $category Categoría del warning
     * @param string $message Mensaje descriptivo
     * @param mixed $value Valor que causó el warning
     */
    public function __construct(
        string $field,
        string $category,
        string $message,
        $value = null
    ) {
        $this->field = $field;
        $this->category = $category;
        $this->message = $message;
        $this->value = $value;
        $this->timestamp = time();
    }

    /**
     * Factory para warnings de campo faltante.
     * 
     * @param string $field
     * @param mixed $value
     * @return self
     */
    public static function missing(string $field, $value = null): self
    {
        return new self(
            $field,
            'missing',
            "Campo '$field' faltante o vacío",
            $value
        );
    }

    /**
     * Factory para warnings de tipo incorrecto.
     * 
     * @param string $field
     * @param string $expected
     * @param string $actual
     * @return self
     */
    public static function typeMismatch(string $field, string $expected, string $actual): self
    {
        return new self(
            $field,
            'type_mismatch',
            "Campo '$field' esperaba tipo '$expected' pero recibió '$actual'",
            $actual
        );
    }

    /**
     * Factory para warnings de parsing ambiguo.
     * 
     * @param string $field
     * @param string $message
     * @return self
     */
    public static function ambiguous(string $field, string $message): self
    {
        return new self(
            $field,
            'ambiguous',
            $message
        );
    }

    /**
     * Factory para warnings de información incompleta.
     * 
     * @param string $field
     * @param string $message
     * @return self
     */
    public static function incomplete(string $field, string $message): self
    {
        return new self(
            $field,
            'incomplete',
            $message
        );
    }

    /**
     * Obtiene el campo.
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Obtiene la categoría.
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Obtiene el mensaje.
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Obtiene el valor.
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Obtiene el timestamp.
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * Convierte el warning a array.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'field' => $this->field,
            'category' => $this->category,
            'message' => $this->message,
            'value' => $this->value,
            'timestamp' => $this->timestamp,
        ];
    }

    /**
     * Convierte a string legible.
     * @return string
     */
    public function __toString(): string
    {
        return "[{$this->category}:{$this->field}] {$this->message}";
    }
}
