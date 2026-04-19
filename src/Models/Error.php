<?php

namespace ContentProcessor\Models;

/**
 * Normalización de errores técnicos.
 * 
 * Estructura estándar para todos los errores que ocurren durante
 * la ingesta, extracción o validación de documentos.
 * 
 * A diferencia de los warnings (Bloque 3), los errores indican
 * que el documento NO fue procesado exitosamente.
 * 
 * @package ContentProcessor\Models
 * @since 1.3.0 (Bloque 4)
 */
class Error
{
    /**
     * Tipo de error (ej: 'extraction', 'validation', 'runtime')
     * @var string
     */
    private string $type;

    /**
     * Mensaje de error legible.
     * @var string
     */
    private string $message;

    /**
     * Contexto adicional del error (ej: nombre del archivo, línea, etc.)
     * @var array
     */
    private array $context;

    /**
     * Código de error (opcional, para debugging).
     * @var string|null
     */
    private ?string $code;

    /**
     * Timestamp del error.
     * @var int
     */
    private int $timestamp;

    /**
     * Constructor.
     * 
     * @param string $type Tipo de error
     * @param string $message Mensaje descriptivo
     * @param array $context Contexto adicional
     * @param string|null $code Código de error opcional
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
     * Factory para errores de extracción.
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
     * Factory para errores de validación.
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
     * Factory para errores en tiempo de ejecución.
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
     * Obtiene el tipo de error.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Obtiene el mensaje de error.
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Obtiene el contexto del error.
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Obtiene un valor específico del contexto.
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
     * Obtiene el código de error.
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Obtiene el timestamp del error.
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
     * Convierte a string legible.
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
