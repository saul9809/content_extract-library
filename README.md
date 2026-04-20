# Content Processor

**Production-ready PHP library for batch document processing with intelligent content extraction and structuring.**

Framework-agnostic, scalable, and optimized for real-world document pipelines from day one.

## 🎯 Purpose

Process multiple documents (PDFs, text files, images, etc.), extract their content, and convert it into configurable JSON structures ready for bulk loading into databases or services.

### Quick Example

```php
$result = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new PdfTextExtractor())
    ->withStructurer(new SchemaAwareStructurer())
    ->fromDirectory('/documents')
    ->processFinal();  // Returns FinalResult with clean API
```

## 📦 Installation

```bash
composer require content-extract/content-processor:^1.4.0
```

Or add to your `composer.json`:

```json
{
  "require": {
    "content-extract/content-processor": "^1.4.0"
  }
}
```

## 🏗️ Project Structure

```
src/
├── Contracts/              # Interfaces defining the contract
│   ├── ExtractorInterface.php
│   ├── StructurerInterface.php
│   └── SchemaInterface.php
├── Core/                   # Main classes
│   └── ContentProcessor.php
├── Extractors/             # Extractor implementations
│   ├── PdfTextExtractor.php
│   ├── TextFileExtractor.php
│   └── PdfOcrExtractor.php (v1.5.0+)
├── Schemas/                # Schema implementations
│   └── ArraySchema.php
├── Structurers/            # Structurer implementations
│   ├── SimpleLineStructurer.php
│   ├── RuleBasedStructurer.php
│   ├── SchemaAwareStructurer.php
│   └── CompositePdfExtractor.php (v1.5.0+)
├── Utils/                  # Utilities
│   ├── TextNormalizer.php
│   └── TextSegmenter.php
└── Models/                 # Domain models
    ├── Warning.php
    ├── Error.php
    └── FinalResult.php

examples/
├── example_basic.php
├── example_semantic_structuring.php
└── sample_cv_*.txt
```

## ⚡ Quick Start

### 1. Define Your Schema

```php
use ContentProcessor\Schemas\ArraySchema;

$schema = new ArraySchema([
    'name' => [
        'type' => 'string',
        'required' => true,
        'aliases' => ['name', 'full name', 'applicant name'],
    ],
    'email' => [
        'type' => 'string',
        'required' => true,
        'aliases' => ['email', 'email address'],
    ],
    'experience_years' => [
        'type' => 'integer',
        'required' => false,
        'aliases' => ['years of experience', 'experience'],
    ],
]);
```

### 2. Configure the Processor

```php
use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Extractors\PdfTextExtractor;
use ContentProcessor\Structurers\SchemaAwareStructurer;

$result = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new PdfTextExtractor())
    ->withStructurer(new SchemaAwareStructurer())
    ->fromDirectory('/path/to/documents', '*.pdf')
    ->processFinal();
```

### 3. Consume Results

```php
// Check status
if (!$result->isSuccessful()) {
    echo "Some documents failed:\n";
    foreach ($result->errors() as $error) {
        echo "  - " . $error->getMessage() . "\n";
    }
}

// Process successful data
foreach ($result->data() as $item) {
    echo "Processed: " . $item['document'] . "\n";
    // $item['data'] contains the structured data
    var_dump($item['data']);
}

// Inspect quality warnings
if ($result->hasWarnings()) {
    foreach ($result->warnings() as $warning) {
        echo "⚠️ Field '{$warning->getField()}': {$warning->getMessage()}\n";
    }
}

// Export to JSON
echo $result->toJSONPretty();
```

## 🧪 Testing

### Run Examples

```bash
cd examples
php example_basic.php
php example_semantic_structuring.php
```

### Full Test Suite

```bash
composer test
```

### Code Quality

```bash
composer lint
```

## 🔌 Available Interfaces

### ExtractorInterface

```php
interface ExtractorInterface {
    public function extract(string $source): array;
    public function canHandle(string $source): bool;
    public function getName(): string;
}
```

### StructurerInterface

```php
interface StructurerInterface {
    public function structure(array $content, SchemaInterface $schema): array;
    public function getName(): string;
}
```

### SchemaInterface

```php
interface SchemaInterface {
    public function getDefinition(): array;
    public function validate(array $data): array;
    public function getName(): string;
}
```

## 📋 Processor Options

```php
$processor->withOptions([
    'skip_invalid' => true,    // Skip documents that fail validation
    'preserve_empty' => false, // Preserve empty fields in result
]);
```

## ✅ Implemented Features (Blocks 1-5)

### Block 1: Core ✅

- Framework-agnostic design with clean interfaces
- Extractor/Structurer pattern
- JSON schema validation
- Batch processing

### Block 2: PDF Support ✅

- PdfTextExtractor with smalot/pdfparser
- Batch processing with multiple PDFs
- Robust error handling

### Block 3: Semantic Structuring ✅

- SchemaAwareStructurer for intelligent extraction
- Field aliases for semantic guidance
- Text normalization and segmentation
- Advanced warning system
- Type conversion and validation

### Block 4: Final Result API ✅

- Unified FinalResult object
- Error and warning normalization
- Summary with statistics
- JSON export and serialization

### Block 5: Security & Hardening ✅

- File size limits (10 MB default)
- Batch document limits (50 documents default)
- Path traversal protection
- Configurable security validation
- Production-ready defaults

### Block 6: OCR Support (v1.5.0+) 🚀

- PdfOcrExtractor for scanned PDFs using Tesseract
- Automatic fallback when digital extraction fails
- Transparent OCR processing without code changes
- Preserves semantic structuring pipeline

## 🔍 OCR Support (Optional)

This library supports OCR for scanned PDFs using **Tesseract OCR**.

### Requirements

- Tesseract OCR installed on the system
- Language data files (e.g., `eng` for English)
- Installation is handled by the operating system, not Composer

### Automatic Fallback

OCR is automatically used when:
- Digital text extraction returns insufficient text
- Extracted text is empty or below threshold (default: 50 characters)
- Extracted text contains no alphabetic characters

### Example with OCR

```php
use ContentProcessor\Extractors\CompositePdfExtractor;

// Automatically tries digital extraction first, then OCR if needed
$result = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new CompositePdfExtractor())  // Tries PDF text first, then OCR
    ->withStructurer(new SchemaAwareStructurer())
    ->fromDirectory('/documents')
    ->processFinal();
```

### Important Notes

- OCR is **optional** - the library works fine with digital PDFs
- OCR is **NOT** installed by Composer
- OCR support does **not** change schema behavior
- Aliases are still defined by your application
- If Tesseract is not available, clear error messages are provided

## 📚 Documentation

- [ARCHITECTURE.md](ARCHITECTURE.md) - Complete architectural design
- [SECURITY.md](SECURITY.md) - Security policy and configurable limits
- [SEMANTIC_STRUCTURING_GUIDE.md](SEMANTIC_STRUCTURING_GUIDE.md) - Schema aliases and matching
- [QUICK_START_V1.4.0.md](QUICK_START_V1.4.0.md) - Quick reference for v1.4.0+

## 🔌 API Reference

### FinalResult

```php
$result = ContentProcessor::make()->...->processFinal();

// Access data
$result->data();           // Array of successful documents
$result->errors();         // Array of normalized errors
$result->warnings();       // Array of semantic warnings
$result->summary();        // Summary with statistics

// Status checks
$result->isSuccessful();   // bool - At least 1 successful?
$result->isPerfect();      // bool - No errors or warnings?
$result->hasErrors();      // bool
$result->hasWarnings();    // bool

// Filtering
$result->errorsByType('validation');
$result->warningsByField('email');
$result->warningsByCategory('missing_value');

// Serialization
$result->toArray();        // array
$result->toJSON();         // string (compact)
$result->toJSONPretty();   // string (formatted)
$result->fullResults();    // array (complete audit trail)
```

## 🚀 Production Ready

The library is tested and ready for production deployment. See [SECURITY.md](SECURITY.md) for deployment recommendations.

## 📋 Requirements

- PHP >= 8.1
- Composer
- (Optional) Tesseract OCR for scanned PDF support

## 📄 License

MIT
