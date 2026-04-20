<?php

namespace ContentProcessor\Extractors;

use ContentProcessor\Contracts\ExtractorInterface;

/**
 * Composite extractor that combines PDF text and OCR extraction.
 * 
 * Automatically tries digital text extraction first, then falls back
 * to OCR if the extracted text is insufficient.
 * 
 * This allows seamless processing of both digital and scanned PDFs
 * without changing user code.
 * 
 * @author Contributor
 * @version 1.5.0
 */
class CompositePdfExtractor implements ExtractorInterface
{
    /**
     * Primary extractor (digital text extraction).
     * 
     * @var PdfTextExtractor
     */
    private PdfTextExtractor $pdfTextExtractor;

    /**
     * Fallback extractor (OCR).
     * 
     * @var PdfOcrExtractor
     */
    private PdfOcrExtractor $pdfOcrExtractor;

    /**
     * Minimum number of characters for text to be considered sufficient.
     * 
     * @var int
     */
    private int $minCharacterThreshold = 50;

    /**
     * Whether to require alphabetic characters in extracted text.
     * 
     * @var bool
     */
    private bool $requireAlphabeticCharacters = true;

    /**
     * Whether to log extraction method used.
     * 
     * @var bool
     */
    private bool $logExtractionMethod = false;

    /**
     * Last extraction method used ('text' or 'ocr').
     * 
     * @var string|null
     */
    private ?string $lastExtractionMethod = null;

    /**
     * Constructor.
     * 
     * @param PdfTextExtractor|null $textExtractor Custom text extractor (optional)
     * @param PdfOcrExtractor|null $ocrExtractor Custom OCR extractor (optional)
     */
    public function __construct(
        ?PdfTextExtractor $textExtractor = null,
        ?PdfOcrExtractor $ocrExtractor = null
    ) {
        $this->pdfTextExtractor = $textExtractor ?? new PdfTextExtractor();
        $this->pdfOcrExtractor = $ocrExtractor ?? new PdfOcrExtractor();
    }

    /**
     * Extracts text content from a PDF file.
     * 
     * First tries digital text extraction. If the result is insufficient,
     * falls back to OCR extraction. Returns whichever succeeds.
     * 
     * @param string $source Absolute path to the PDF file
     * @return array Array of extracted text
     * @throws \RuntimeException If both methods fail
     */
    public function extract(string $source): array
    {
        if (!$this->canHandle($source)) {
            throw new \RuntimeException(
                "Cannot process file: '{$source}'. Verify that it exists and is a valid PDF."
            );
        }

        $textError = null;
        $ocrError = null;

        // Try digital text extraction first
        try {
            $extractedText = $this->pdfTextExtractor->extract($source);
            if ($this->isSufficientText($extractedText)) {
                $this->lastExtractionMethod = 'text';
                if ($this->logExtractionMethod) {
                    // Could log: Used digital text extraction for '{$source}'
                }
                return $extractedText;
            }
        } catch (\Exception $e) {
            $textError = $e->getMessage();
        }

        // Fall back to OCR
        try {
            $extractedText = $this->pdfOcrExtractor->extract($source);
            $this->lastExtractionMethod = 'ocr';
            if ($this->logExtractionMethod) {
                // Could log: Used OCR extraction for '{$source}'
            }
            return $extractedText;
        } catch (\Exception $e) {
            $ocrError = $e->getMessage();
        }

        // Both methods failed - provide detailed error
        $errorMessages = [];
        if ($textError !== null) {
            $errorMessages[] = "Digital extraction: {$textError}";
        }
        if ($ocrError !== null) {
            $errorMessages[] = "OCR extraction: {$ocrError}";
        }

        throw new \RuntimeException(
            "Failed to extract text from '{$source}'. Both methods failed:\n" .
                implode("\n", $errorMessages)
        );
    }

    /**
     * Validates that the source can be processed by this extractor.
     * 
     * @param string $source Path to the source file
     * @return bool True if can be processed, false otherwise
     */
    public function canHandle(string $source): bool
    {
        return $this->pdfTextExtractor->canHandle($source);
    }

    /**
     * Returns the name of the extractor.
     * 
     * @return string Identifier name of the extractor
     */
    public function getName(): string
    {
        return 'CompositePdfExtractor';
    }

    /**
     * Checks if extracted text is sufficient for processing.
     * 
     * Text is considered sufficient if:
     * 1. Not empty
     * 2. Contains at least minCharacterThreshold characters
     * 3. (If requireAlphabeticCharacters) Contains at least one letter
     * 
     * @param array $extractedText Array of text strings
     * @return bool True if text is sufficient, false otherwise
     */
    private function isSufficientText(array $extractedText): bool
    {
        if (empty($extractedText)) {
            return false;
        }

        // Concatenate all text parts
        $fullText = implode(' ', $extractedText);
        $trimmedText = trim($fullText);

        // Check if empty after trimming
        if (empty($trimmedText)) {
            return false;
        }

        // Check character threshold
        if (strlen($trimmedText) < $this->minCharacterThreshold) {
            return false;
        }

        // Check for alphabetic characters if required
        if ($this->requireAlphabeticCharacters) {
            if (!preg_match('/[a-zA-Z]/u', $trimmedText)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sets the minimum character threshold for sufficient text.
     * 
     * @param int $threshold Minimum number of characters
     * @return self For method chaining
     */
    public function setMinCharacterThreshold(int $threshold): self
    {
        $this->minCharacterThreshold = max(0, $threshold);
        return $this;
    }

    /**
     * Sets whether to require alphabetic characters in extracted text.
     * 
     * @param bool $require If true, extracted text must contain at least one letter
     * @return self For method chaining
     */
    public function setRequireAlphabeticCharacters(bool $require): self
    {
        $this->requireAlphabeticCharacters = $require;
        return $this;
    }

    /**
     * Sets whether to log extraction method used.
     * 
     * @param bool $log If true, extraction method will be stored
     * @return self For method chaining
     */
    public function setLogExtractionMethod(bool $log): self
    {
        $this->logExtractionMethod = $log;
        return $this;
    }

    /**
     * Gets the extraction method used in the last extract() call.
     * 
     * @return string|null 'text', 'ocr', or null if extract() not called yet
     */
    public function getLastExtractionMethod(): ?string
    {
        return $this->lastExtractionMethod;
    }

    /**
     * Gets the underlying text extractor.
     * 
     * @return PdfTextExtractor
     */
    public function getTextExtractor(): PdfTextExtractor
    {
        return $this->pdfTextExtractor;
    }

    /**
     * Gets the underlying OCR extractor.
     * 
     * @return PdfOcrExtractor
     */
    public function getOcrExtractor(): PdfOcrExtractor
    {
        return $this->pdfOcrExtractor;
    }
}
