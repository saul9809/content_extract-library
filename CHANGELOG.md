# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.3.1] - 2026-04-19

### Fixed

- **PdfTextExtractor::canHandle()** now correctly detects PDF files by binary signature (`%PDF` header) instead of file extension
  - This enables processing of PDFs uploaded via HTTP that have temporary filenames (e.g., `phpXXXX.tmp`)
  - Fixes compatibility with Laravel file uploads and similar frameworks
- **Language enforcement**: All error messages, exceptions, and documentation converted to English-only
  - Removed all Spanish text from source code
  - All user-facing messages are now in English for consistency

### Changed

- Updated all class documentation from Spanish to English
- Updated all interface documentation from Spanish to English
- Improved error messages in extractors for clarity
- Enhanced `canHandle()` implementation with defensive file handling

### Security

- Added `@` error suppression on file operations to prevent warning pollution
- Maintained secure file validation practices

### Backward Compatibility

✅ **No breaking changes** - All public APIs remain unchanged

- `canHandle()` still returns bool as expected
- `extract()` still works with the same signature
- Error handling is improved but maintains the same exception types

### Files Modified

- `src/Extractors/PdfTextExtractor.php` - Fixed PDF detection and English-only messages
- `src/Extractors/TextFileExtractor.php` - English-only messages
- `src/Contracts/ExtractorInterface.php` - English documentation
- `src/Contracts/StructurerInterface.php` - English documentation
- `src/Contracts/SchemaInterface.php` - English documentation
- `src/Contracts/SemanticStructurerInterface.php` - English documentation
- `src/Structurers/SimpleLineStructurer.php` - English documentation
- `src/Structurers/RuleBasedStructurer.php` - English documentation

### Testing

```bash
# Verify PdfTextExtractor works with temporary files
php examples/test_pdf_extraction.php

# Verify all extractors work correctly
php examples/example_bloque4_basic.php
```

---

## [1.3.0] - 2025-01-XX

### Added

- Block 5: Security hardening (SecurityValidator, SecurityConfig, SecurityException)
- FinalResult unified API with data(), errors(), warnings(), summary()
- Complete documentation for Packagist publication
- Verification scripts for Packagist compatibility

### Changed

- Enhanced version tracking across all components
- Improved error messages

### Security

- Added input validation framework
- Memory limits enforcement
- Timeout protection

---

## [1.2.0] - 2025-01-XX

### Added

- Block 3: Semantic structuring with RuleBasedStructurer
- Warning system for validation feedback
- SemanticStructurerInterface for advanced structuring
- DocumentContext for metadata support

---

## [1.1.0] - 2025-01-XX

### Added

- Block 2: PDF text extraction with PdfTextExtractor
- Batch processing support
- smalot/pdfparser integration

---

## [1.0.0] - 2025-01-XX

### Added

- Block 1: Core content extraction framework
- TextFileExtractor for plain text files
- SimpleLineStructurer for basic structuring
- Extractor and Structurer interfaces
- PSR-4 autoloading

---

## Installation

```bash
# Install the latest version
composer require content-extract/content-processor

# Install specific version
composer require content-extract/content-processor:^1.3.1
```

---

## Migration Guide

### From 1.3.0 to 1.3.1

**No changes required!** This is a bug-fix release.

If you were working around the PDF file extension issue, you can now use the improved `canHandle()` method with temporary files directly:

```php
// Before (didn't work with phpXXXX.tmp files)
$processor = new ContentProcessor();
$result = $processor->process('/tmp/phpABC123.tmp', 'PdfText');

// After (works perfectly)
$processor = new ContentProcessor();
$result = $processor->process('/tmp/phpABC123.tmp', 'PdfText'); // ✅ Now works!
```

---

## Support

- **Issues:** https://github.com/saul9809/content_extract-library/issues
- **Documentation:** See README.md
- **License:** MIT
