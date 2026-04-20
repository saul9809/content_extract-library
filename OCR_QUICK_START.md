# OCR Quick Start Guide (v1.5.0)

## Overview

This library now supports OCR (Optical Character Recognition) for processing scanned PDF documents using Tesseract OCR. OCR is **optional** and used as an automatic fallback when digital text extraction is insufficient.

## Installation & Setup

### 1. Install Tesseract OCR

#### Ubuntu/Debian
```bash
sudo apt-get update
sudo apt-get install tesseract-ocr
sudo apt-get install poppler-utils
sudo apt-get install tesseract-ocr-eng
```

#### macOS
```bash
brew install tesseract
brew install poppler
```

#### Windows
1. Download from: https://github.com/UB-Mannheim/tesseract/wiki
2. Run the installer
3. Add Tesseract to system PATH (installer does this automatically)

### 2. Verify Installation

```bash
tesseract --version
```

You should see version information. If command not found, add Tesseract to PATH.

### 3. Update Your Application

No changes needed! Your existing code continues to work. To add OCR support:

```php
// Before (v1.4.0) - only handles digital PDFs
$processor = ContentProcessor::make()
    ->withExtractor(new PdfTextExtractor())
    ->...

// After (v1.5.0) - handles both digital and scanned
$processor = ContentProcessor::make()
    ->withExtractor(new CompositePdfExtractor())  // Changed this line
    ->...
```

## Usage Patterns

### Pattern 1: Automatic Fallback (RECOMMENDED)

```php
use ContentProcessor\Extractors\CompositePdfExtractor;

$processor = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new CompositePdfExtractor())
    ->withStructurer(new SchemaAwareStructurer())
    ->fromDirectory('/documents')
    ->processFinal();
```

**Behavior:**
- Tries digital text extraction first
- If text insufficient (empty, <50 chars, no letters), tries OCR
- Falls back gracefully if Tesseract unavailable

### Pattern 2: OCR Only

```php
use ContentProcessor\Extractors\PdfOcrExtractor;

$processor = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new PdfOcrExtractor('eng'))
    ->withStructurer(new SchemaAwareStructurer())
    ->fromDirectory('/scanned-documents')
    ->processFinal();
```

**Use when:** All documents are scanned or you want to force OCR processing.

### Pattern 3: Custom Configuration

```php
$composite = new CompositePdfExtractor();

// Adjust when to trigger OCR fallback
$composite
    ->setMinCharacterThreshold(100)
    ->setRequireAlphabeticCharacters(true);

$processor = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor($composite)
    ->withStructurer(new SchemaAwareStructurer())
    ->fromDirectory('/documents')
    ->processFinal();
```

## Extraction Methods

### Digital Text Extraction (PdfTextExtractor)

**Pros:**
- Fast (no Tesseract needed)
- High accuracy for digital PDFs
- No external command execution

**Cons:**
- Fails on scanned PDFs
- Cannot extract from images

**When to use:**
- Digital PDFs with embedded text
- Mixed document sets (with CompositePdfExtractor)

### OCR Extraction (PdfOcrExtractor)

**Pros:**
- Works with scanned PDFs
- Works with images
- Language-agnostic

**Cons:**
- Slower than digital extraction
- Requires Tesseract installed
- May have accuracy issues with poor scans

**When to use:**
- Scanned PDF documents
- Business documents from copiers/scanners
- As fallback in composite extractor

## Configuration

### Text Insufficiency Criteria

CompositePdfExtractor triggers OCR fallback when extracted text:
1. **Is empty** - No content extracted
2. **Below character threshold** - Default: 50 characters (configurable)
3. **No alphabetic characters** - If requirement enabled (default: true)

```php
$composite = new CompositePdfExtractor();

// Require 100 characters minimum
$composite->setMinCharacterThreshold(100);

// Require at least one letter
$composite->setRequireAlphabeticCharacters(true);
```

### Language Configuration

```php
// English (default)
$extractor = new PdfOcrExtractor('eng');

// Spanish
$extractor = new PdfOcrExtractor('spa');

// Change after creation
$extractor->setLanguage('fra');  // French
```

**Common language codes:**
- `eng` - English
- `spa` - Spanish
- `fra` - French
- `deu` - German
- `ita` - Italian
- See Tesseract docs for complete list

## Monitoring & Debugging

### Check Tesseract Availability

```php
$extractor = new PdfOcrExtractor();

if (!$extractor->isTesseractAvailable()) {
    echo "Tesseract is not installed!";
    exit(1);
}
```

### Log Which Method Was Used

```php
$composite = new CompositePdfExtractor();
$composite->setLogExtractionMethod(true);

$processor = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor($composite)
    ->...
    ->processFinal();

// After processing a file
$method = $composite->getLastExtractionMethod();
// Returns: 'text' or 'ocr'

echo "Used extraction method: " . ($method === 'text' ? 'Digital' : 'OCR');
```

### Error Handling

```php
try {
    $result = $processor->processFinal();
} catch (\RuntimeException $e) {
    if (strpos($e->getMessage(), 'Tesseract') !== false) {
        echo "OCR not available: " . $e->getMessage();
        // Fall back to digital-only processing
    }
}
```

## Semantic Structuring with OCR

OCR output integrates seamlessly with semantic structuring:

```php
$schema = new ArraySchema([
    'name' => [
        'type' => 'string',
        'aliases' => ['name', 'full name', 'applicant name'],
    ],
    'email' => [
        'type' => 'string',
        'aliases' => ['email', 'e-mail', 'email address'],
    ],
]);

$processor = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new CompositePdfExtractor())  // Works with OCR or digital
    ->withStructurer(new SchemaAwareStructurer()) // Uses same aliases
    ->fromDirectory('/documents')
    ->processFinal();

// Structured output is identical regardless of extraction method
// Field aliases work for both digital and OCR-extracted text
```

## Best Practices

### 1. Use CompositePdfExtractor for Production

```php
// Good for production
$extractor = new CompositePdfExtractor();
```

**Why:** Automatically handles both digital and scanned PDFs without code changes.

### 2. Check Tesseract Before Processing Many Files

```php
$extractor = new PdfOcrExtractor();
if (!$extractor->isTesseractAvailable()) {
    // Log warning or fail gracefully
}
```

### 3. Optimize OCR for Your Document Type

```php
$composite = new CompositePdfExtractor();

// For structured forms (receipts, invoices)
$composite->setMinCharacterThreshold(50);  // Default

// For freeform text (letters, resumes)
$composite->setMinCharacterThreshold(100);

// For dense documents (books, PDFs)
$composite->setMinCharacterThreshold(200);
```

### 4. Test with Sample Documents

```php
// Start with small batch
$result = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new CompositePdfExtractor())
    ->fromDirectory('/test-documents', '*.pdf')
    ->processFinal();

// Check results
foreach ($result->data() as $item) {
    echo "Document: " . $item['document'] . "\n";
    var_dump($item['data']);
}

if ($result->hasWarnings()) {
    foreach ($result->warnings() as $warning) {
        echo "⚠️ " . $warning->getMessage() . "\n";
    }
}
```

## Performance Notes

### OCR is Slower than Digital Extraction

- **Digital extraction:** ~50ms per PDF
- **OCR extraction:** 1-5 seconds per PDF (depends on complexity)

### Optimize with CompositePdfExtractor

```php
// CompositePdfExtractor is most efficient because:
// 1. Skips Tesseract for digital PDFs
// 2. Only uses OCR when necessary
// 3. Fails fast if Tesseract unavailable

$composite = new CompositePdfExtractor();
```

### Large Batch Processing

```php
// For 1000+ documents, monitor progress
$processor = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new CompositePdfExtractor())
    ->withStructurer(new SchemaAwareStructurer());

$result = $processor->fromDirectory('/large-batch')->processFinal();

echo "Processed: " . count($result->data()) . " successful\n";
echo "Failed: " . count($result->errors()) . " failed\n";
echo "Warnings: " . count($result->warnings()) . " warnings\n";
```

## Troubleshooting

### "Tesseract is not installed"

```
Error: Tesseract OCR is not installed or not available in system PATH.
```

**Solution:**
1. Install Tesseract (see Installation section)
2. Verify: `tesseract --version`
3. Check system PATH includes Tesseract directory

### "pdftoppm or convert not found"

```
Error: Neither pdftoppm nor ImageMagick convert is available.
```

**Solution:**
```bash
# Ubuntu/Debian
sudo apt-get install poppler-utils imagemagick

# macOS
brew install poppler imagemagick
```

### Low OCR Accuracy

**Solutions:**
1. Improve image quality (higher DPI in PDF)
2. Pre-process images (contrast, rotation)
3. Try different Tesseract language settings
4. Check document quality before processing

### Processing Hangs

**Solutions:**
1. Set reasonable file size limits (security config)
2. Use batch processing with timeouts
3. Monitor system resources
4. Process in smaller batches if needed

## API Reference

### CompositePdfExtractor

```php
$composite = new CompositePdfExtractor();

// Configuration
$composite->setMinCharacterThreshold(50);
$composite->setRequireAlphabeticCharacters(true);
$composite->setLogExtractionMethod(true);

// Extraction
$text = $composite->extract('/path/to/file.pdf');

// Status checks
$method = $composite->getLastExtractionMethod();  // 'text' or 'ocr'
```

### PdfOcrExtractor

```php
$ocr = new PdfOcrExtractor('eng');

// Configuration
$ocr->setLanguage('spa');

// Status check
if (!$ocr->isTesseractAvailable()) {
    exit("Tesseract not available");
}

// Extraction
$text = $ocr->extract('/path/to/file.pdf');
```

## Additional Resources

- [Tesseract Documentation](https://github.com/tesseract-ocr/tesseract/wiki)
- [Tesseract Languages](https://github.com/tesseract-ocr/tessdata)
- [Main Documentation](../ARCHITECTURE.md)
- [Semantic Structuring Guide](./SEMANTIC_STRUCTURING_GUIDE.md)
- [Security Configuration](../SECURITY.md)

---

**v1.5.0** - OCR Phase Complete | 100% English | Production Ready
