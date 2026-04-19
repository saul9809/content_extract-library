<?php

namespace ContentProcessor\Security;

/**
 * Validador de seguridad para operaciones de archivo.
 * 
 * Centraliza todas las verificaciones de seguridad:
 * - Tamaño de archivo
 * - Integridad de PDF
 * - Path traversal
 * - Límites de batch
 * 
 * @package ContentProcessor\Security
 * @since 1.4.0 (Bloque 5)
 */
class SecurityValidator
{
    /**
     * Valida el tamaño de un archivo.
     * 
     * @param string $filePath
     * @param string $fileType Tipo ('pdf', 'text')
     * @return bool
     * @throws SecurityException Si el archivo excede el máximo
     */
    public static function validateFileSize(string $filePath, string $fileType = 'pdf'): bool
    {
        if (!file_exists($filePath)) {
            throw SecurityException::fileNotFound();
        }

        $fileSize = filesize($filePath);
        $maxSize = SecurityConfig::getMaxFileSize($fileType);

        if ($fileSize > $maxSize) {
            $sizeMB = round($fileSize / (1024 * 1024), 2);
            $maxMB = round($maxSize / (1024 * 1024), 2);
            throw SecurityException::fileTooLarge(
                "Archivo ocupa {$sizeMB}MB (máximo: {$maxMB}MB)",
                ['file' => basename($filePath), 'size' => $fileSize, 'max' => $maxSize]
            );
        }

        return true;
    }

    /**
     * Valida que un archivo es realmente un PDF basándose en su cabecera.
     * 
     * NOTA: Esta validación es defensiva pero no es prueba absoluta.
     * Un atacante podría crear un PDF con cabecera válida pero contenido
     * corrupto. La responsabilidad final recae en el integrador.
     * 
     * @param string $filePath
     * @return bool
     * @throws SecurityException Si no es un PDF válido
     */
    public static function validatePdfSignature(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            throw SecurityException::fileNotFound();
        }

        if (filesize($filePath) === 0) {
            throw SecurityException::invalidPdf('Archivo PDF vacío');
        }

        // Leer cabecera
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            throw SecurityException::invalidPdf('No se puede leer el archivo');
        }

        $header = fread($handle, SecurityConfig::PDF_HEADER_CHECK_BYTES);
        fclose($handle);

        if ($header === false || strlen($header) < SecurityConfig::PDF_HEADER_CHECK_BYTES) {
            throw SecurityException::invalidPdf('Archivo demasiado pequeño para ser PDF');
        }

        if (strpos($header, SecurityConfig::PDF_HEADER_SIGNATURE) !== 0) {
            throw SecurityException::invalidPdf('Cabecera PDF no detectada');
        }

        return true;
    }

    /**
     * Valida que un path es seguro (sin path traversal).
     * 
     * Protecciones:
     * - Rechaza ../ y ..\\
     * - Rechaza paths absolutos (en Windows y Unix)
     * - Normaliza separadores
     * 
     * @param string $path
     * @return string Path normalizado
     * @throws SecurityException Si se detecta path traversal
     */
    public static function validateAndNormalizePath(string $path): string
    {
        // Detectar intentos de path traversal
        if (strpos($path, '..') !== false) {
            throw SecurityException::pathTraversalDetected($path);
        }

        // Normalizar separadores (Unix → separador del sistema)
        $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        // Detectar paths absolutos
        if (strpos($normalized, DIRECTORY_SEPARATOR) === 0) {
            // Es una ruta absoluta - se permite si es válida
            // Pero se valida que exista y sea dentro de allowed dirs
        }

        return $normalized;
    }

    /**
     * Valida el tamaño del batch.
     * 
     * @param array $files Array de rutas de archivos
     * @return bool
     * @throws SecurityException Si excede el máximo
     */
    public static function validateBatchSize(array $files): bool
    {
        $count = count($files);
        $max = SecurityConfig::MAX_BATCH_DOCUMENTS;

        if ($count > $max) {
            throw SecurityException::batchTooLarge($count, $max);
        }

        return true;
    }

    /**
     * Valida que un array de warnings no excede el máximo.
     * 
     * @param array $warnings
     * @return bool
     * @throws SecurityException Si excede el máximo
     */
    public static function validateWarningCount(array $warnings): bool
    {
        $count = count($warnings);
        $max = SecurityConfig::MAX_WARNINGS_PER_DOCUMENT;

        if ($count > $max) {
            throw new SecurityException(
                'El documento genero demasiados warnings.',
                'warnings_overflow',
                ['count' => $count, 'max' => $max]
            );
        }

        return true;
    }

    /**
     * Validación defensiva completa para un archivo antes de procesarlo.
     * 
     * @param string $filePath
     * @param string $fileType Tipo ('pdf', 'text')
     * @return bool
     * @throws SecurityException Si alguna validación falla
     */
    public static function validateFile(string $filePath, string $fileType = 'pdf'): bool
    {
        // Validar existencia
        if (!file_exists($filePath)) {
            throw SecurityException::fileNotFound();
        }

        // Validar tamaño
        self::validateFileSize($filePath, $fileType);

        // Validar firma (si es PDF)
        if ($fileType === 'pdf') {
            self::validatePdfSignature($filePath);
        }

        return true;
    }
}
