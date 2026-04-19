# 🚀 Quick Start Guide - v1.4.0

**Latest Version:** v1.4.0  
**Status:** Production Ready ✅  
**Installation:** 1 minute  
**First Example:** 5 minutes

---

## ⚡ Quick Installation

```bash
# Install via Composer
composer require content-extract/content-processor:^1.4.0

# That's it! Ready to use.
```

---

## 📖 Basic Usage - 2 Minutes

```php
<?php

require 'vendor/autoload.php';

use ContentProcessor\Extractors\PdfTextExtractor;
use ContentProcessor\Structurers\SchemaAwareStructurer;
use ContentProcessor\Schemas\ArraySchema;

// 1. Define what data you want to extract
$schema = [
    'name' => [
        'type' => 'string',
        'required' => true,
        'aliases' => ['name', 'full name', 'applicant name'],
    ],
    'email' => [
        'type' => 'string',
        'required' => true,
        'aliases' => ['email', 'email address', 'contact email'],
    ],
    'phone' => [
        'type' => 'string',
        'required' => false,
        'aliases' => ['phone', 'telephone', 'mobile'],
    ],
];

// 2. Extract text from PDF
$extractor = new PdfTextExtractor();
$extracted = $extractor->extract('resume.pdf');
$rawText = $extracted['content'];

// 3. Structure with schema
$structurer = new SchemaAwareStructurer();
$result = $structurer->structure($rawText, new ArraySchema($schema));

// 4. Use the results
$data = $result['data'];
$warnings = $result['warnings'];

echo "Name: " . $data['name'] . "\n";
echo "Email: " . $data['email'] . "\n";
echo "Phone: " . ($data['phone'] ?? 'Not provided') . "\n";

if (!empty($warnings)) {
    echo "\nWarnings:\n";
    foreach ($warnings as $w) {
        echo "  - {$w['field']}: {$w['message']}\n";
    }
}
```

---

## 🎯 Common Scenarios

### Scenario 1: Extract CV Data

```php
use ContentProcessor\Extractors\PdfTextExtractor;
use ContentProcessor\Structurers\SchemaAwareStructurer;
use ContentProcessor\Schemas\ArraySchema;

$cvSchema = [
    'name' => [
        'type' => 'string',
        'required' => true,
        'aliases' => ['name', 'full name', 'candidate name'],
    ],
    'years_experience' => [
        'type' => 'integer',
        'required' => false,
        'aliases' => ['years of experience', 'experience', 'years exp'],
    ],
    'email' => [
        'type' => 'string',
        'required' => true,
        'aliases' => ['email', 'e-mail', 'contact email'],
    ],
    'phone' => [
        'type' => 'string',
        'required' => false,
        'aliases' => ['phone', 'mobile', 'cell'],
    ],
    'skills' => [
        'type' => 'array',
        'required' => false,
        'aliases' => ['skills', 'technical skills', 'competencies'],
    ],
];

$extractor = new PdfTextExtractor();
$structurer = new SchemaAwareStructurer();

$extracted = $extractor->extract('cv.pdf');
$result = $structurer->structure($extracted['content'], new ArraySchema($cvSchema));

// Access extracted data
var_dump($result['data']);
```

### Scenario 2: Process Multiple Documents

```php
$documents = ['cv1.pdf', 'cv2.pdf', 'cv3.pdf'];
$schema = new ArraySchema($cvSchema);

$extractor = new PdfTextExtractor();
$structurer = new SchemaAwareStructurer();

foreach ($documents as $file) {
    if ($extractor->canHandle($file)) {
        $extracted = $extractor->extract($file);
        $result = $structurer->structure($extracted['content'], $schema);

        // Process result
        saveToDatabase($result['data']);
    }
}
```

### Scenario 3: With Error Handling

```php
try {
    $extracted = $extractor->extract('invoice.pdf');

    if (!$extracted['success']) {
        echo "Extraction failed: " . $extracted['error']['message'];
        exit;
    }

    $result = $structurer->structure(
        $extracted['content'],
        new ArraySchema($schema)
    );

    // Check warnings
    foreach ($result['warnings'] as $warning) {
        if ($warning['category'] === 'missing') {
            error_log("Missing required field: " . $warning['field']);
        }
    }

    // Use data
    return $result['data'];

} catch (Exception $e) {
    error_log("Processing error: " . $e->getMessage());
    throw $e;
}
```

### Scenario 4: Custom Configuration

```php
// With custom normalization
$structurer = new SchemaAwareStructurer(
    $matchThreshold = 0.65,              // Stricter matching
    $normalizerConfig = [
        'lowercase' => true,
        'remove_accents' => true,        // Handle Pérez → Perez
    ],
    $maxWordsPerFragment = 15            // Longer phrases
);

$result = $structurer->structure($text, new ArraySchema($schema));
```

---

## 🔧 Schema Design Best Practices

### ✅ Good Schema Example

```php
$schema = [
    // Required fields with good aliases
    'name' => [
        'type' => 'string',
        'required' => true,
        'aliases' => [
            'name',
            'full name',
            'applicant name',
            'candidate name',
            'personnel name',
        ],
    ],

    // Optional field with type conversion
    'years_experience' => [
        'type' => 'integer',
        'required' => false,
        'aliases' => [
            'years of experience',
            'years experience',
            'years exp',
            'experience level',
        ],
    ],

    // Array type for multi-value fields
    'skills' => [
        'type' => 'array',
        'required' => false,
        'aliases' => [
            'skills',
            'technical skills',
            'competencies',
            'key skills',
        ],
    ],
];
```

### ❌ Bad Schema Example

```php
$schema = [
    // No aliases - only field name used
    'name' => ['type' => 'string', 'required' => true],

    // Single alias - less resilient
    'email' => [
        'type' => 'string',
        'required' => true,
        'aliases' => ['email'],  // Should have more variations
    ],

    // No aliases for boolean
    'is_active' => ['type' => 'string', 'required' => false],
];
```

---

## 📊 Understanding Results

### Result Structure

```php
$result = [
    'data' => [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'years_experience' => 5,
        'skills' => ['PHP', 'Laravel', 'MySQL'],
    ],
    'warnings' => [
        [
            'field' => 'phone',
            'category' => 'missing',
            'message' => 'Required field "phone" not found in text...',
        ],
        [
            'field' => 'skills',
            'category' => 'incomplete',
            'message' => 'Field "skills" was found but may be incomplete.',
        ],
    ],
];
```

### Warning Categories

| Category        | Meaning                  | Action                           |
| --------------- | ------------------------ | -------------------------------- |
| `missing`       | Required field not found | Check aliases, document content  |
| `incomplete`    | Optional field missing   | Expected, may be okay            |
| `ambiguous`     | Multiple similar matches | Disambiguate with better aliases |
| `type_mismatch` | Type conversion failed   | Check data format                |

---

## 🎓 Advanced Topics

### Text Processing Pipeline

The library applies these transformations in order:

```
Raw Text
  ↓
[1] TextNormalizer
    - Lowercase
    - Remove control chars
    - Normalize whitespace
    - Normalize punctuation
  ↓
[2] TextSegmenter
    - Split by newlines
    - Split by colons ("key: value")
    - Split by bullets
    - Split by numbers
    - Create phrase windows
  ↓
[3] SchemaAwareStructurer
    - Match fragments to fields using aliases
    - Score matches (0.0-1.0)
    - Extract values
    - Convert types
    - Generate warnings
  ↓
Structured Data with Warnings
```

### Matching Algorithm Details

For each field:

1. **Read aliases** from schema (or use field name)
2. **Score each fragment** against each alias:
   - Exact match: **1.0**
   - Prefix match: **0.9**
   - Substring match: **0.7**
   - Fuzzy/typo: **0.6**
3. **Find highest score** (threshold: 0.6 by default)
4. **Check ambiguity** (< 0.15 score gap → warning)
5. **Extract value** from matched fragment
6. **Convert type** (string → int/float/bool/array)

### Configuration Options

```php
$structurer = new SchemaAwareStructurer(
    // Matching threshold (0.0-1.0)
    // Lower = more lenient, may have false matches
    // Higher = stricter, may miss valid matches
    $matchThreshold = 0.6,

    // Text normalization options
    $normalizerConfig = [
        'lowercase' => true,              // Always lowercase
        'trim_whitespace' => true,        // Trim edges
        'collapse_spaces' => true,        // Collapse multiple spaces
        'remove_control_chars' => true,   // Remove invisible chars
        'normalize_punctuation' => true,  // Standardize punctuation
        'remove_accents' => false,        // Optional: Pérez → Perez
    ],

    // Text segmentation option
    $maxWordsPerFragment = 12             // Max words per phrase
);
```

---

## 🐛 Troubleshooting

### Problem: Fields not being extracted

**Diagnosis:** Aliases don't match text content

**Solution:** Add more aliases that match variations in your documents

```php
// Before - too specific
'email' => ['aliases' => ['email']]

// After - more variations
'email' => [
    'aliases' => [
        'email',
        'email address',
        'e-mail',
        'contact email',
        'email de contacto',  // Spanish variant
    ]
]
```

### Problem: Wrong values being extracted

**Diagnosis:** Matching threshold too low or text ambiguous

**Solution:** Increase threshold or improve aliases

```php
// More strict matching
$structurer = new SchemaAwareStructurer($matchThreshold = 0.75);
```

### Problem: Type conversion errors

**Diagnosis:** Text value doesn't match expected type

**Solution:** Better alias targeting or preprocessing

```php
// Field expects integer
'years_experience' => [
    'type' => 'integer',
    'aliases' => [
        'years of experience',
        'years exp',
        'experience level',
        // Avoid aliases that might match non-numeric values
    ]
]
```

---

## 🔗 Related Documentation

- **Full Guide:** [SEMANTIC_STRUCTURING_GUIDE.md](SEMANTIC_STRUCTURING_GUIDE.md)
- **Architecture:** [ARCHITECTURE.md](ARCHITECTURE.md)
- **Examples:** `examples/example_semantic_structuring.php`
- **Security:** [SECURITY.md](SECURITY.md)
- **API Reference:** [README.md](README.md)

---

## 💡 Tips & Tricks

### Tip 1: Use descriptive aliases

```php
// Good
'phone' => ['aliases' => ['phone', 'mobile', 'telephone', 'contact number']]

// Bad
'phone' => ['aliases' => ['ph', 'tel']]
```

### Tip 2: Mix languages in aliases

```php
'name' => [
    'aliases' => [
        'name',           // English
        'nombre',         // Spanish
        'nom',            // French
        'full name',
        'nombres y apellidos',
    ]
]
```

### Tip 3: Test with sample documents

```php
// Create test documents first
// Define schema
// Run extraction
// Adjust aliases based on results
```

### Tip 4: Monitor warnings

```php
// Always check warnings
if (!empty($result['warnings'])) {
    foreach ($result['warnings'] as $w) {
        // Log, alert, or adjust schema
    }
}
```

---

## 📞 Support & Resources

- **GitHub:** https://github.com/saul9809/content_extract-library
- **Packagist:** https://packagist.org/packages/content-extract/content-processor
- **Version:** v1.4.0+
- **Status:** Production Ready ✅

---

**Ready to extract structured data? Start with the examples above!**

For more details, see the full documentation in the repository.
