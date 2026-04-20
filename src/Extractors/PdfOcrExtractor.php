<?php

namespace ContentProcessor\Extractors;

use ContentProcessor\Contracts\ExtractorInterface;

/**
 * Extractor for text content from scanned PDF files using OCR.
 * 
 * Uses Tesseract OCR via CLI to extract text from scanned PDFs
 * where digital text extraction is insufficient or unavailable.
 * 
 * Requirements:
 * - Tesseract OCR installed on system (NOT installed via Composer)
 * - Language data files (e.g., 'eng' for English)
 * 
 * @author Contributor
 * @version 1.5.0
 */
class PdfOcrExtractor implements ExtractorInterface
{
    /**
     * Tesseract language code.
     * 
     * @var string
     */
    private string $language = 'eng';

    /**
     * Temporary directory for intermediate files.
     * 
     * @var string|null
     */
    private ?string $tempDir = null;

    /**
     * Whether Tesseract is available on system.
     * 
     * @var bool|null
     */
    private ?bool $tesseractAvailable = null;

    /**
     * Constructor.
     * 
     * @param string $language Tesseract language code (default: 'eng')
     * @param string|null $tempDir Custom temporary directory (optional)
     */
    public function __construct(string $language = 'eng', ?string $tempDir = null)
    {
        $this->language = $language;
        $this->tempDir = $tempDir;
    }

    /**
     * Extracts text content from a PDF file using OCR.
     * 
     * @param string $source Absolute path to the PDF file
     * @return array Array of extracted text (one entry per page/file)
     * @throws \RuntimeException If file does not exist, OCR fails, or Tesseract unavailable
     */
    public function extract(string $source): array
    {
        // Validate that the file exists
        if (!file_exists($source)) {
            throw new \RuntimeException(
                "Cannot process file: '{$source}'. File does not exist."
            );
        }

        if (!is_readable($source)) {
            throw new \RuntimeException(
                "Cannot process file: '{$source}'. File is not readable."
            );
        }

        // Verify Tesseract is available
        if (!$this->isTesseractAvailable()) {
            throw new \RuntimeException(
                "Tesseract OCR is not installed or not available in system PATH. " .
                "Please install Tesseract OCR and ensure it is in your system PATH."
            );
        }

        // Determine temporary directory
        $tempDir = $this->tempDir ?? sys_get_temp_dir();
        if (!is_writable($tempDir)) {
            throw new \RuntimeException(
                "Temporary directory is not writable: '{$tempDir}'"
            );
        }

        // Convert PDF to image(s) using ImageMagick or pdftoppm
        $imageFiles = $this->pdfToImages($source, $tempDir);

        try {
            // Extract text from each image using Tesseract
            $extractedText = [];
            foreach ($imageFiles as $imageFile) {
                $text = $this->ocrImage($imageFile);
                if (!empty($text)) {
                    $extractedText[] = $text;
                }
            }

            if (empty($extractedText)) {
                throw new \RuntimeException(
                    "OCR extraction failed: no text could be extracted from '{$source}'"
                );
            }

            return $extractedText;
        } finally {
            // Clean up temporary image files
            foreach ($imageFiles as $imageFile) {
                if (file_exists($imageFile)) {
                    @unlink($imageFile);
                }
            }
        }
    }

    /**
     * Validates that the source can be processed by this extractor.
     * 
     * @param string $source Path to the source file
     * @return bool True if can be processed, false otherwise
     */
    public function canHandle(string $source): bool
    {
        // Must be a PDF file
        if (!is_string($source) || !str_ends_with(strtolower($source), '.pdf')) {
            return false;
        }

        // Must exist and be readable
        if (!file_exists($source) || !is_readable($source)) {
            return false;
        }

        return true;
    }

    /**
     * Returns the name of the extractor.
     * 
     * @return string Identifier name of the extractor
     */
    public function getName(): string
    {
        return 'PdfOcrExtractor';
    }

    /**
     * Checks if Tesseract is available on the system.
     * 
     * @return bool True if Tesseract is available, false otherwise
     */
    public function isTesseractAvailable(): bool
    {
        if ($this->tesseractAvailable !== null) {
            return $this->tesseractAvailable;
        }

        // Check if tesseract command is available
        $command = escapeshellcmd('tesseract --version');
        $output = null;
        $exitCode = null;

        // Use proc_open for more reliable command execution
        $process = proc_open(
            $command,
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        if (is_resource($process)) {
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);
            
            $this->tesseractAvailable = ($exitCode === 0);
        } else {
            // Fallback to shell_exec
            $this->tesseractAvailable = false;
            $result = @shell_exec('tesseract --version 2>&1');
            if ($result !== null && strpos($result, 'tesseract') !== false) {
                $this->tesseractAvailable = true;
            }
        }

        return $this->tesseractAvailable;
    }

    /**
     * Sets the Tesseract language code.
     * 
     * @param string $language Language code (e.g., 'eng', 'spa', 'fra')
     * @return self For method chaining
     */
    public function setLanguage(string $language): self
    {
        $this->language = $language;
        return $this;
    }

    /**
     * Converts a PDF file to image(s).
     * 
     * Uses pdftoppm if available, otherwise ImageMagick convert.
     * 
     * @param string $pdfFile Path to the PDF file
     * @param string $tempDir Temporary directory for image files
     * @return array Array of paths to generated image files
     * @throws \RuntimeException If conversion fails
     */
    private function pdfToImages(string $pdfFile, string $tempDir): array
    {
        $baseName = uniqid('pdf_ocr_', true);
        $baseOutputPath = $tempDir . DIRECTORY_SEPARATOR . $baseName;

        // Try pdftoppm first (better quality, preserves multi-page)
        if ($this->commandExists('pdftoppm')) {
            return $this->pdfToImagesViaPdftoppm($pdfFile, $baseOutputPath);
        }

        // Fall back to ImageMagick convert
        if ($this->commandExists('convert')) {
            return $this->pdfToImagesViaConvert($pdfFile, $baseOutputPath);
        }

        throw new \RuntimeException(
            "Neither pdftoppm nor ImageMagick convert is available. " .
            "Please install one of them: 'pdftoppm' (from Poppler) or 'convert' (ImageMagick)"
        );
    }

    /**
     * Converts PDF to images using pdftoppm.
     * 
     * @param string $pdfFile Path to the PDF file
     * @param string $baseOutputPath Base output path for images
     * @return array Array of generated image file paths
     * @throws \RuntimeException If conversion fails
     */
    private function pdfToImagesViaPdftoppm(string $pdfFile, string $baseOutputPath): array
    {
        // pdftoppm outputs as: basepath-1.ppm, basepath-2.ppm, etc.
        $command = sprintf(
            'pdftoppm -png %s %s 2>&1',
            escapeshellarg($pdfFile),
            escapeshellarg($baseOutputPath)
        );

        $output = @shell_exec($command);
        if ($output === null) {
            throw new \RuntimeException(
                "Failed to execute pdftoppm command for PDF: '{$pdfFile}'"
            );
        }

        // Find generated image files (they will have names like basepath-1.png, basepath-2.png)
        $tempDir = dirname($baseOutputPath);
        $baseName = basename($baseOutputPath);
        $imageFiles = [];

        // Scan directory for generated files
        if ($handle = opendir($tempDir)) {
            $i = 1;
            while (($file = readdir($handle)) !== false) {
                if (strpos($file, $baseName) === 0 && preg_match('/\.(png|ppm|tif)$/i', $file)) {
                    $imageFiles[] = $tempDir . DIRECTORY_SEPARATOR . $file;
                }
            }
            closedir($handle);
        }

        if (empty($imageFiles)) {
            throw new \RuntimeException(
                "pdftoppm did not generate any image files for: '{$pdfFile}'"
            );
        }

        // Sort by filename to maintain page order
        sort($imageFiles);
        return $imageFiles;
    }

    /**
     * Converts PDF to images using ImageMagick convert.
     * 
     * @param string $pdfFile Path to the PDF file
     * @param string $baseOutputPath Base output path for images
     * @return array Array of generated image file paths
     * @throws \RuntimeException If conversion fails
     */
    private function pdfToImagesViaConvert(string $pdfFile, string $baseOutputPath): array
    {
        // convert outputs as: basepath-0.png, basepath-1.png, etc.
        $command = sprintf(
            'convert -density 300 %s -quality 85 %s.png 2>&1',
            escapeshellarg($pdfFile),
            escapeshellarg($baseOutputPath)
        );

        $output = @shell_exec($command);
        if ($output === null) {
            throw new \RuntimeException(
                "Failed to execute convert command for PDF: '{$pdfFile}'"
            );
        }

        // Find generated image files
        $tempDir = dirname($baseOutputPath);
        $baseName = basename($baseOutputPath);
        $imageFiles = [];

        // Check if single page (basepath.png) or multi-page (basepath-0.png, etc.)
        $singlePageFile = $baseOutputPath . '.png';
        if (file_exists($singlePageFile)) {
            $imageFiles[] = $singlePageFile;
        } else {
            // Multi-page - scan for -N.png files
            if ($handle = opendir($tempDir)) {
                while (($file = readdir($handle)) !== false) {
                    if (strpos($file, $baseName) === 0 && preg_match('/-\d+\.png$/i', $file)) {
                        $imageFiles[] = $tempDir . DIRECTORY_SEPARATOR . $file;
                    }
                }
                closedir($handle);
            }
        }

        if (empty($imageFiles)) {
            throw new \RuntimeException(
                "ImageMagick convert did not generate any image files for: '{$pdfFile}'"
            );
        }

        // Sort by filename to maintain page order
        sort($imageFiles);
        return $imageFiles;
    }

    /**
     * Extracts text from an image using Tesseract OCR.
     * 
     * @param string $imageFile Path to the image file
     * @return string Extracted text
     * @throws \RuntimeException If OCR extraction fails
     */
    private function ocrImage(string $imageFile): string
    {
        // Use temporary file for output (Tesseract requires this)
        $tempOutputBase = tempnam(sys_get_temp_dir(), 'tesseract_');
        $outputFile = $tempOutputBase . '.txt';

        try {
            // Build Tesseract command
            $command = sprintf(
                'tesseract %s %s -l %s 2>&1',
                escapeshellarg($imageFile),
                escapeshellarg($tempOutputBase),
                escapeshellarg($this->language)
            );

            $output = @shell_exec($command);

            // Check if output file was created
            if (!file_exists($outputFile)) {
                throw new \RuntimeException(
                    "Tesseract OCR failed to create output file for image: '{$imageFile}'"
                );
            }

            // Read extracted text
            $text = file_get_contents($outputFile);
            if ($text === false) {
                throw new \RuntimeException(
                    "Failed to read Tesseract output for image: '{$imageFile}'"
                );
            }

            return trim($text);
        } finally {
            // Clean up temporary Tesseract output files
            if (file_exists($outputFile)) {
                @unlink($outputFile);
            }
            if (file_exists($tempOutputBase)) {
                @unlink($tempOutputBase);
            }
        }
    }

    /**
     * Checks if a command exists in the system PATH.
     * 
     * @param string $command Command name to check
     * @return bool True if command exists, false otherwise
     */
    private function commandExists(string $command): bool
    {
        $command = escapeshellcmd($command);

        // Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = @shell_exec("where " . $command . " 2>nul");
            return !empty($output);
        }

        // Unix-like systems
        $output = @shell_exec("command -v " . $command . " 2>/dev/null");
        return !empty($output);
    }
}
