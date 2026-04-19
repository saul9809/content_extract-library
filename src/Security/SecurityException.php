<?php

namespace ContentProcessor\Security;

/**
 * Excepción de security genérica.
 * 
 * Se lanza cuando se detecta una violación de security
 * (límite excedido, file malicioso, path traversal, etc.)
 * 
 * IMPORTANTE: Nunca expone detalles internos del filesystem
 * ni stack traces a clientes no autorizados.
 * 
 * @package ContentProcessor\Security
 * @since 1.4.0 (Block 5)
 */
class SecurityException extends \Exception
{
    /**
     * Tipo de excepción de security.
     * @var string
     */
    private string $securityType;

    /**
     * Additional context (para logging interno, nunca para cliente).
     * @var array
     */
    private array $securityContext;

    /**
     * Constructor.
     * 
     * @param string $message Mensaje seguro para mostrar
     * @param string $securityType Tipo de violación
     * @param array $context Context interno (nunca se expone)
     * @param int $code HTTP-like code
     */
    public function __construct(
        string $message = 'Security exception',
        string $securityType = 'unknown',
        array $context = [],
        int $code = 0
    ) {
        parent::__construct($message, $code);
        $this->securityType = $securityType;
        $this->securityContext = $context;
    }

    /**
     * Obtiene el tipo de violación de security.
     * @return string
     */
    public function getSecurityType(): string
    {
        return $this->securityType;
    }

    /**
     * Obtiene el context (para logging, no para cliente).
     * @return array
     */
    public function getSecurityContext(): array
    {
        return $this->securityContext;
    }

    /**
     * Factory para excepción de tamaño excedido.
     * 
     * @param string $reason
     * @param array $context
     * @return self
     */
    public static function fileTooLarge(string $reason, array $context = []): self
    {
        return new self(
            'El file excede el tamaño máximo permitido.',
            'file_size_exceeded',
            array_merge($context, ['reason' => $reason])
        );
    }

    /**
     * Factory para excepción de batch demasiado grande.
     * 
     * @param int $count
     * @param int $max
     * @return self
     */
    public static function batchTooLarge(int $count, int $max): self
    {
        return new self(
            "Batch contiene $count documentos (máximo: $max).",
            'batch_size_exceeded',
            ['count' => $count, 'max' => $max]
        );
    }

    /**
     * Factory para excepción de PDF inválido.
     * 
     * @param string $reason
     * @return self
     */
    public static function invalidPdf(string $reason): self
    {
        return new self(
            'El file is not un PDF válido.',
            'invalid_pdf',
            ['reason' => $reason]
        );
    }

    /**
     * Factory para excepción de path traversal.
     * 
     * @param string $attempted_path
     * @return self
     */
    public static function pathTraversalDetected(string $attempted_path): self
    {
        return new self(
            'Path no autorizado detectado.',
            'path_traversal',
            ['attempted_path' => $attempted_path]
        );
    }

    /**
     * Factory para excepción de file not found.
     * 
     * @return self
     */
    public static function fileNotFound(): self
    {
        return new self(
            'File not found o no accesible.',
            'file_not_found'
        );
    }

    /**
     * Obtiene un mensaje seguro para el cliente (nunca expone detalles).
     * @return string
     */
    public function getClientMessage(): string
    {
        return $this->getMessage() ?: 'Error of security detectado.';
    }

    /**
     * Obtiene un mensaje para logging interno (con context).
     * @return string
     */
    public function getInternalMessage(): string
    {
        $msg = "[{$this->securityType}] " . $this->getMessage();
        if (!empty($this->securityContext)) {
            $msg .= ' | Context: ' . json_encode($this->securityContext);
        }
        return $msg;
    }
}
