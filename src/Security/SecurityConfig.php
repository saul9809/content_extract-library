<?php

namespace ContentProcessor\Security;

/**
 * Configuración centralizada de límites de security.
 * 
 * Define los umbrales máximos para:
 * - Tamaño de files PDF
 * - Cantidad de documentos por batch
 * - Validaciones de integridad
 * 
 * Estos valores cann ser personalizados pero es recomendable
 * mantener los defaults para producción.
 * 
 * NOTAS DE SEGURIDAD:
 * Estos límites protegen contra:
 * - DoS (Denial of Service) por upload masivo
 * - Consumo excesivo de memoria
 * - Tiempos de processing incontrolados
 * 
 * @package ContentProcessor\Security
 * @since 1.4.0 (Block 5)
 */
class SecurityConfig
{
    /**
     * Tamaño máximo por file PDF en bytes.
     * Default: 10 MB
     * @var int
     */
    public const MAX_PDF_SIZE_BYTES = 10 * 1024 * 1024; // 10 MB

    /**
     * Tamaño máximo por file of text en bytes.
     * Default: 5 MB
     * @var int
     */
    public const MAX_TEXT_FILE_SIZE_BYTES = 5 * 1024 * 1024; // 5 MB

    /**
     * Cantidad máxima de documentos por batch.
     * Default: 50 documentos
     * 
     * Rationale:
     * - Previene DoS de processing
     * - Mantiene uso de memoria controlado
     * - Batch típico en producción: 10-30 documentos
     * 
     * @var int
     */
    public const MAX_BATCH_DOCUMENTS = 50;

    /**
     * Cantidad máxima de warnings por documento.
     * Default: 100 warnings
     * @var int
     */
    public const MAX_WARNINGS_PER_DOCUMENT = 100;

    /**
     * Cabecera mínima esperada para validar PDF.
     * Todos los PDFs validos comienzan con %PDF-
     * @var string
     */
    public const PDF_HEADER_SIGNATURE = '%PDF-';

    /**
     * Tamaño mínimo de cabecera PDF a leer para validation.
     * @var int
     */
    public const PDF_HEADER_CHECK_BYTES = 5;

    /**
     * Caracteres permitidos en rutas normalizadas.
     * Rechaza: paths relativos con ../, etc.
     * @var string (regex)
     */
    public const SAFE_PATH_REGEX = '/^[a-zA-Z0-9\._\-\/\\\\:\s]+$/';

    /**
     * Obtiene el tamaño máximo readable para un tipo de file.
     * 
     * @param string $type Tipo ('pdf', 'text', 'default')
     * @return int Bytes
     */
    public static function getMaxFileSize(string $type = 'default'): int
    {
        return match ($type) {
            'pdf' => self::MAX_PDF_SIZE_BYTES,
            'text' => self::MAX_TEXT_FILE_SIZE_BYTES,
            default => self::MAX_PDF_SIZE_BYTES,
        };
    }

    /**
     * Obtiene un resumen de la configuración de security.
     * Útil para logging y debugging.
     * 
     * @return array
     */
    public static function getSummary(): array
    {
        return [
            'max_pdf_size_mb' => self::MAX_PDF_SIZE_BYTES / (1024 * 1024),
            'max_text_file_size_mb' => self::MAX_TEXT_FILE_SIZE_BYTES / (1024 * 1024),
            'max_batch_documents' => self::MAX_BATCH_DOCUMENTS,
            'max_warnings_per_document' => self::MAX_WARNINGS_PER_DOCUMENT,
            'pdf_header_signature' => self::PDF_HEADER_SIGNATURE,
        ];
    }
}
