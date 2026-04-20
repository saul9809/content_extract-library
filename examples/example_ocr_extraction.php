<?php

/**
 * Example: OCR Support for Scanned PDFs (v1.5.0+)
 * 
 * This example demonstrates how to use OCR support to extract text
 * from scanned PDF files using Tesseract OCR.
 * 
 * Requirements:
 * - Tesseract OCR installed on system
 * - pdftoppm (Poppler) or ImageMagick convert installed
 * - English language data for Tesseract
 * 
 * Usage:
 *   php example_ocr_extraction.php
 */

require __DIR__ . '/../vendor/autoload.php';

use ContentProcessor\Schemas\ArraySchema;
use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Extractors\PdfOcrExtractor;
use ContentProcessor\Extractors\CompositePdfExtractor;
use ContentProcessor\Extractors\PdfTextExtractor;
use ContentProcessor\Structurers\SchemaAwareStructurer;

echo "=== OCR Support Example (v1.5.0) ===\n\n";

// Define a simple schema for CV extraction
$schema = new ArraySchema([
    'name' => [
        'type' => 'string',
        'required' => true,
        'aliases' => ['name', 'full name', 'applicant name', 'candidate name'],
    ],
    'email' => [
        'type' => 'string',
        'required' => false,
        'aliases' => ['email', 'email address', 'e-mail'],
    ],
    'phone' => [
        'type' => 'string',
        'required' => false,
        'aliases' => ['phone', 'phone number', 'telephone', 'contact number'],
    ],
    'experience_years' => [
        'type' => 'integer',
        'required' => false,
        'aliases' => ['years of experience', 'experience', 'years experience', 'exp'],
    ],
]);

// Example 1: Using CompositePdfExtractor (RECOMMENDED for mixed document sets)
// ============================================================================
// This automatically tries digital extraction first, then falls back to OCR

echo "EXAMPLE 1: Composite Extractor (Automatic Fallback)\n";
echo "───────────────────────────────────────────────────\n\n";

echo "The CompositePdfExtractor is recommended because it:\n";
echo "  • Automatically detects which extraction method to use\n";
echo "  • Falls back to OCR only if digital extraction is insufficient\n";
echo "  • Works transparently without code changes\n";
echo "  • Handles both digital and scanned PDFs\n\n";

echo "Usage:\n";
echo "<?php\n";
echo "\$processor = ContentProcessor::make()\n";
echo "    ->withSchema(\$schema)\n";
echo "    ->withExtractor(new CompositePdfExtractor())\n";
echo "    ->withStructurer(new SchemaAwareStructurer())\n";
echo "    ->fromDirectory('/documents')\n";
echo "    ->processFinal();\n";
echo "?>\n\n";

// Example 2: Using PdfOcrExtractor directly
// ==========================================
// For cases where you know the PDFs are scanned

echo "EXAMPLE 2: Direct OCR Extractor (For Scanned Documents Only)\n";
echo "─────────────────────────────────────────────────────────────\n\n";

echo "Use PdfOcrExtractor directly when:\n";
echo "  • All documents are scanned (no digital text)\n";
echo "  • You want to force OCR processing\n";
echo "  • You need specific OCR configuration\n\n";

echo "Usage:\n";
echo "<?php\n";
echo "\$ocrExtractor = new PdfOcrExtractor('eng');  // Use English\n";
echo "\n";
echo "\$processor = ContentProcessor::make()\n";
echo "    ->withSchema(\$schema)\n";
echo "    ->withExtractor(\$ocrExtractor)\n";
echo "    ->withStructurer(new SchemaAwareStructurer())\n";
echo "    ->fromDirectory('/documents')\n";
echo "    ->processFinal();\n";
echo "?>\n\n";

// Example 3: Configurable text insufficiency detection
// =====================================================

echo "EXAMPLE 3: Custom Text Insufficiency Thresholds\n";
echo "──────────────────────────────────────────────\n\n";

echo "You can customize when OCR fallback is triggered:\n\n";

echo "<?php\n";
echo "\$composite = new CompositePdfExtractor();\n";
echo "\n";
echo "// Require at least 100 characters (default: 50)\n";
echo "\$composite->setMinCharacterThreshold(100);\n";
echo "\n";
echo "// Require alphabetic characters (default: true)\n";
echo "\$composite->setRequireAlphabeticCharacters(true);\n";
echo "\n";
echo "\$processor = ContentProcessor::make()\n";
echo "    ->withSchema(\$schema)\n";
echo "    ->withExtractor(\$composite)\n";
echo "    ->withStructurer(new SchemaAwareStructurer())\n";
echo "    ->fromDirectory('/documents')\n";
echo "    ->processFinal();\n";
echo "?>\n\n";

// Example 4: Detecting which extraction method was used
// ======================================================

echo "EXAMPLE 4: Extraction Method Detection\n";
echo "──────────────────────────────────────\n\n";

echo "CompositePdfExtractor can log which method was used:\n\n";

echo "<?php\n";
echo "\$composite = new CompositePdfExtractor();\n";
echo "\$composite->setLogExtractionMethod(true);\n";
echo "\n";
echo "\$processor = ContentProcessor::make()\n";
echo "    ->withSchema(\$schema)\n";
echo "    ->withExtractor(\$composite)\n";
echo "    ->fromDirectory('/documents')\n";
echo "    ->processFinal();\n";
echo "\n";
echo "// For each document, determine extraction method used\n";
echo "\$method = \$composite->getLastExtractionMethod();\n";
echo "// Returns: 'text' (digital extraction) or 'ocr' (Tesseract)\n";
echo "?>\n\n";

// Example 5: Checking Tesseract availability
// ============================================

echo "EXAMPLE 5: Tesseract Availability Check\n";
echo "────────────────────────────────────────\n\n";

echo "Before processing scanned documents, you can verify Tesseract:\n\n";

echo "<?php\n";
echo "\$ocrExtractor = new PdfOcrExtractor();\n";
echo "\n";
echo "if (!\$ocrExtractor->isTesseractAvailable()) {\n";
echo "    echo 'Error: Tesseract not installed.';\n";
echo "    echo 'Install it using: apt-get install tesseract-ocr';\n";
echo "    exit(1);\n";
echo "}\n";
echo "\n";
echo "// Safe to use OCR extractor\n";
echo "?>\n\n";

// Example 6: Language configuration
// ==================================

echo "EXAMPLE 6: OCR Language Configuration\n";
echo "───────────────────────────────────────\n\n";

echo "<?php\n";
echo "// English (default)\n";
echo "\$engExtractor = new PdfOcrExtractor('eng');\n";
echo "\n";
echo "// Spanish\n";
echo "\$spaExtractor = new PdfOcrExtractor('spa');\n";
echo "\n";
echo "// Multiple languages (if installed)\n";
echo "\$multiExtractor = new PdfOcrExtractor('eng+spa');\n";
echo "\n";
echo "// Change language after creation\n";
echo "\$extractor = new PdfOcrExtractor();\n";
echo "\$extractor->setLanguage('fra');  // French\n";
echo "?>\n\n";

// Key points
echo "KEY POINTS ABOUT OCR SUPPORT\n";
echo "═════════════════════════════\n\n";

echo "1. **Optional Dependency**\n";
echo "   - Tesseract is NOT installed by Composer\n";
echo "   - Install it manually based on your OS\n\n";

echo "2. **Backward Compatible**\n";
echo "   - Existing code works unchanged\n";
echo "   - No breaking changes\n";
echo "   - Simply use CompositePdfExtractor instead of PdfTextExtractor\n\n";

echo "3. **Semantic Pipeline Integration**\n";
echo "   - OCR works with all existing structurers\n";
echo "   - SchemaAwareStructurer handles OCR text just like digital text\n";
echo "   - Field aliases work with both extraction methods\n\n";

echo "4. **Fallback Strategy**\n";
echo "   - Tries digital extraction first (faster, no Tesseract needed)\n";
echo "   - Falls back to OCR only if text insufficient\n";
echo "   - Reduces OCR overhead for mixed document sets\n\n";

echo "5. **Multi-page Handling**\n";
echo "   - Automatically converts all PDF pages to images\n";
echo "   - OCR processes each page\n";
echo "   - Results concatenated in page order\n\n";

echo "REQUIREMENTS RECAP\n";
echo "══════════════════\n\n";

echo "For OCR support, install:\n\n";

echo "Ubuntu/Debian:\n";
echo "  sudo apt-get install tesseract-ocr\n";
echo "  sudo apt-get install poppler-utils\n";
echo "  sudo apt-get install tesseract-ocr-eng\n\n";

echo "macOS:\n";
echo "  brew install tesseract\n";
echo "  brew install poppler\n\n";

echo "Windows:\n";
echo "  Download from: https://github.com/UB-Mannheim/tesseract/wiki\n";
echo "  Use installer and add to system PATH\n\n";

echo "\n✅ Examples complete!\n";
