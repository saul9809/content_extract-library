<?php

namespace ContentProcessor\Extractors;

use ContentProcessor\Contracts\ExtractorInterface;
use Smalot\PdfParser\Parser;

/**
 * Extractor de contenido desde archivos PDF digitales.
 * 
 * Utiliza smalot/pdfparser para extraer el texto de PDFs digitales
 * (no escaneados, no requiere OCR).
 * 
 * @author Tu Nombre
 * @version 1.0
 */
class PdfTextExtractor implements ExtractorInterface
{
    /**
     * Instancia del parser de PDF.
     * 
     * @var Parser
     */
    private Parser $parser;

    /**
     * Constructor.
     * 
     * Inicializa el parser de PDFs.
     */
    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Extrae el contenido textual de un archivo PDF.
     * 
     * @param string $source Ruta absoluta del archivo PDF
     * @return array Array con el texto extraído (una entrada por página/PDF)
     * @throws \RuntimeException Si el archivo no existe o no puede procesarse
     */
    public function extract(string $source): array
    {
        // Validar que el archivo existe
        if (!$this->canHandle($source)) {
            throw new \RuntimeException(
                "No se puede procesar el archivo: '{$source}'. Verifique que existe y es un PDF válido."
            );
        }

        try {
            // Parsear el PDF
            $pdf = $this->parser->parseFile($source);

            // Extraer texto de todas las páginas
            $pages = $pdf->getPages();

            if (empty($pages)) {
                throw new \RuntimeException(
                    "El PDF '{$source}' no contiene páginas o el contenido no es accesible."
                );
            }

            // Acumular texto de todas las páginas
            $extractedText = [];
            foreach ($pages as $page) {
                $text = $page->getText();
                if (!empty($text)) {
                    $extractedText[] = $text;
                }
            }

            // Si no se extrajo texto de ninguna página, retornar array vacío pero válido
            if (empty($extractedText)) {
                $extractedText[] = '';
            }

            return $extractedText;
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Error al procesar el PDF '{$source}': " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Valida si este extractor puede procesar la fuente.
     * 
     * Verifica:
     * - Que el archivo existe
     * - Que tiene extensión .pdf
     * - Que es legible
     * 
     * @param string $source Ruta del archivo a validar
     * @return bool True si puede procesarse, false en caso contrario
     */
    public function canHandle(string $source): bool
    {
        // Verifica que sea un archivo, exista y sea legible
        if (!is_file($source) || !is_readable($source)) {
            return false;
        }

        // Verifica que tenga extensión .pdf
        if (strtolower(pathinfo($source, PATHINFO_EXTENSION)) !== 'pdf') {
            return false;
        }

        return true;
    }

    /**
     * Retorna el nombre identificador de este extractor.
     * 
     * @return string Nombre del extractor
     */
    public function getName(): string
    {
        return 'pdf-text-extractor';
    }
}
