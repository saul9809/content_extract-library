<?php

namespace ContentProcessor\Extractors;

use ContentProcessor\Contracts\ExtractorInterface;
use Smalot\PdfParser\Parser;

/**
 * Extractor for text content from PDF files.
 * 
 * Uses smalot/pdfparser to extract text from digital PDFs
 * (not scanned, OCR not required).
 * 
 * @author Contributor
 * @version 1.3.1
 */
class PdfTextExtractor implements ExtractorInterface
{
    /**
     * PDF parser instance.
     * 
     * @var Parser
     */
    private Parser $parser;

    /**
     * Constructor.
     * 
     * Initializes the PDF parser.
     */
    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Extracts text content from a PDF file.
     * 
     * @param string $source Absolute path to the PDF file
     * @return array Array of extracted text (one entry per page/file)
     * @throws \RuntimeException If file does not exist or cannot be processed
     */
    public function extract(string $source): array
    {
        // Validate that the file exists and is a valid PDF
        if (!$this->canHandle($source)) {
            throw new \RuntimeException(
                "Cannot process file: '{$source}'. Verify that it exists and is a valid PDF."
            );
        }

        try {
            // Parsear el PDF
            $pdf = $this->parser->parseFile($source);

            // Extract text from all pages
            $pages = $pdf->getPages();

            if (empty($pages)) {
                throw new \RuntimeException(
                    "PDF '{$source}' contains no pages or content is not accessible."
                );
            }

            // Accumulate text from all pages
            $extractedText = [];
            foreach ($pages as $page) {
                $text = $page->getText();
                if (!empty($text)) {
                    $extractedText[] = $text;
                }
            }

            // If no text was extracted, return valid empty array
            if (empty($extractedText)) {
                $extractedText[] = '';
            }

            return $extractedText;
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Error processing PDF '{$source}': " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Validates if this extractor can process the source.
     * 
     * Verifies:
     * - That the file exists and is readable
     * - That the file contains a valid PDF header
     * 
     * Note: Checks PDF binary signature instead of extension to support
     * temporary files from HTTP uploads (phpXXXX.tmp, etc.).
     * 
     * @param string $source Path to the file to validate
     * @return bool True if can be processed, false otherwise
     */
    public function canHandle(string $source): bool
    {
        // Verify that it is a file and is readable
        if (!is_file($source) || !is_readable($source)) {
            return false;
        }

        // Check PDF binary signature (magic bytes: %PDF)
        // This handles temporary files from HTTP uploads that don't have .pdf extension
        $handle = @fopen($source, 'rb');
        if ($handle === false) {
            return false;
        }

        $header = fread($handle, 4);
        fclose($handle);

        // PDF files begin with %PDF
        return $header === '%PDF';
    }

    /**
     * Returns the identifier name of this extractor.
     * 
     * @return string Extractor name
     */
    public function getName(): string
    {
        return 'pdf-text-extractor';
    }
}
