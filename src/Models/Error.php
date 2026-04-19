<?php

namespace ContentProcessor\Models;

/**
 * Error normalization técnicos.
 * 
 * Standard structure para todos los errors that occur durante
 * la ingestion, extraction o validation de documentos.
 * 
 * A diferencia de los warnings (Block 3), los errors indican
 * que el documento NO fue processed successfully.
 * 
 * @package ContentProcessor\Models
 * @since 1.3.0 (Block 4)
 */
class Error
{
    /**
     * Error type (ej: 'extraction', 'validation', 'runtime')
     * @var string
     */
    private string $type;

    /**
     * Error message readable.
     * @var string
     */
    private string $message;

    /**
     * Additional context del error (ej: file name, line, etc.)
     * @var array
     */
    private array $context;

    /**
     * Error code (opcional, para debugging).
     * @var string|null
     */
    private ?string $code;

    /**
     * Error timestamp.
     * @var int
     */
    private int $timestamp;

    /**
     * Constructor.
     * 
     * @param string $type Error type
     * @param string $message Mensaje descriptivo
     * @param array $context Additional context
     * @param string|null $code Error code opcional
     */
    public function __construct(
        string $type,
        string $message,
        array $context = [],
        ?string $code = null
    ) {
        $this->type = $type;
        $this->message = $message;
        $this->context = $context;
        $this->code = $code;
        $this->timestamp = time();
    }

    /**
     * Factory para errors de extraction.
     * 
     * @param string $message
     * @param array $context
     * @return self
     */
    public static function extraction(string $message, array $context = []): self
    {
        return new self('extraction', $message, $context);
    }

    /**
     * Factory para errors de validation.
     * 
     * @param string $message
     * @param array $context
     * @return self
     */
    public static function validation(string $message, array $context = []): self
    {
        return new self('validation', $message, $context);
    }

    /**
     * Factory para errors en tiempo de ejecución.
     * 
     * @param string $message
     * @param array $context
     * @return self
     */
    public static function runtime(string $message, array $context = []): self
    {
        return new self('runtime', $message, $context);
    }

    /**
     * Obtiene el error type.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Obtiene el error message.
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Obtiene el context del error.
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Obtiene un valor específico del context.
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function contextGet(string $key, $default = null)
    {
        return $this->context[$key] ?? $default;
    }

    /**
     * Obtiene el error code.
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Obtiene el error timestamp.
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * Convierte el error a array.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'message' => $this->message,
            'code' => $this->code,
            'context' => $this->context,
            'timestamp' => $this->timestamp,
        ];
    }

    /**
     * Convierte a string readable.
     * @return string
     */
    public function __toString(): string
    {
        $str = "[{$this->type}] {$this->message}";
        if ($this->code) {
            $str .= " (code: {$this->code})";
        }
        if (!empty($this->context)) {
            $file = $this->context['file'] ?? null;
            if ($file) {
                $str .= " in {$file}";
            }
        }
        return $str;
    }
}
