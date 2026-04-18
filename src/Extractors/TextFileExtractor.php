<?php

namespace ContentProcessor\Extractors;

use ContentProcessor\Contracts\ExtractorInterface;

/**
 * Extractor simple para archivos de texto plano.
 * 
 * Lee archivos .txt y los convierte en un array de contenido.
 * Diseñado para pruebas y como base para extractores más complejos.
 * 
 * En el futuro, se extenderá para PDF, OCR, etc.
 */
class TextFileExtractor implements ExtractorInterface
{
    private const SUPPORTED_EXTENSIONS = ['txt', 'text'];

    /**
     * {@inheritDoc}
     */
    public function extract(string $source): array
    {
        if (!file_exists($source)) {
            throw new \RuntimeException("Archivo '$source' no existe.");
        }

        if (!is_readable($source)) {
            throw new \RuntimeException("Archivo '$source' no es legible.");
        }

        $content = file_get_contents($source);
        if ($content === false) {
            throw new \RuntimeException("No se pudo leer el archivo '$source'.");
        }

        // Retorna como array con un elemento (una sola página/sección)
        // En PDFs multipágina, sería un array con múltiples elementos
        return [$content];
    }

    /**
     * {@inheritDoc}
     */
    public function canHandle(string $source): bool
    {
        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
        return in_array($extension, self::SUPPORTED_EXTENSIONS, true);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'TextFileExtractor';
    }
}
