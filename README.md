# Content Processor

**Librería PHP para procesamiento batch de documentos con extracción y estructuración de contenido.**

Diseñada para ser framework-agnostic, escalable y production-ready desde el inicio.

## 🎯 Objetivo

Procesar múltiples documentos (PDFs, textos, etc.), extraer su contenido y convertirlo en estructuras JSON configurables, preparadas para carga masiva en bases de datos o servicios.

### Ejemplo inicial esperado:

```php
$result = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new PdfTextExtractor())
    ->withStructurer(new RuleBasedStructurer())
    ->fromDirectory('/cvs')
    ->processFinal();  // Retorna FinalResult con API limpia
```

## 📦 Instalación

```bash
composer require content-extract/content-processor
```

Or add to your `composer.json`:

```json
{
  "require": {
    "content-extract/content-processor": "^1.3.0"
  }
}
```

## 🏗️ Estructura del proyecto

```
src/
├── Contracts/          # Interfaces que definen el contrato
│   ├── ExtractorInterface.php
│   ├── StructurerInterface.php
│   └── SchemaInterface.php
├── Core/               # Clases principales
│   └── ContentProcessor.php
├── Schemas/            # Implementaciones de esquemas
│   └── ArraySchema.php
├── Extractors/         # Implementaciones de extractores
│   └── TextFileExtractor.php
└── Structurers/        # Implementaciones de estructuradores
    └── SimpleLineStructurer.php

examples/
├── example_basic.php   # Ejemplo básico funcional
├── sample_cv_1.txt     # Archivo de prueba 1
└── sample_cv_2.txt     # Archivo de prueba 2
```

## ⚡ Uso rápido

### 1. Definir un esquema

```php
use ContentProcessor\Schemas\ArraySchema;

$schema = new ArraySchema([
    'nombre' => ['type' => 'string', 'required' => true],
    'email' => ['type' => 'string', 'required' => true],
    'anos_experiencia' => ['type' => 'integer', 'required' => false],
]);
```

### 2. Configurar el procesador

```php
use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Extractors\TextFileExtractor;
use ContentProcessor\Structurers\RuleBasedStructurer;

$result = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new TextFileExtractor())
    ->withStructurer(new RuleBasedStructurer())
    ->fromDirectory('/path/to/docs', '*.txt')
    ->processFinal();  // Returns FinalResult
```

### 3. Consumir resultados

```php
// Verificar estatus
if (!$result->isSuccessful()) {
    echo "Algunos documentos fallaron:\n";
    foreach ($result->errors() as $error) {
        echo "  - " . $error->getMessage() . "\n";
    }
}

// Procesar datos exitosos
foreach ($result->data() as $item) {
    echo "Procesado: " . $item['document'] . "\n";
    // $item['data'] contiene los datos estructurados
}

// Inspeccionar warnings (calidad de datos)
if ($result->hasWarnings()) {
    foreach ($result->warnings() as $warning) {
        echo "⚠️ Campo '{$warning->getField()}': {$warning->getMessage()}\n";
    }
}

// Exportar a JSON
echo $result->toJSONPretty();
```

## 🧪 Testing

### Run examples

```bash
cd examples
php example_bloque4_basic.php
php example_bloque4_laravel_style.php
```

### Full test suite

```bash
composer test
```

### Code style

```bash
composer lint
```

## 🔌 Interfaces disponibles

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

## 📝 Opciones del procesador

```php
$processor->withOptions([
    'skip_invalid' => true,    // Saltar documentos que no pasen validación
    'preserve_empty' => false, // Preservar campos vacíos en resultado
]);
```

## ✅ Características implementadas (Bloques 1-5)

### Bloque 1: Core ✅

- Framework-agnostic design con interfaces limpias
- Extractor/Structurer pattern
- JSON schema validation
- Batch processing

### Bloque 2: PDF Support ✅

- PdfTextExtractor con smalot/pdfparser
- Batch processing con múltiples PDFs
- Error handling robusto

### Bloque 3: Semantic Structuring ✅

- RuleBasedStructurer para extracción avanzada
- DocumentContext para información de procesamiento
- Warning system para calidad de datos

### Bloque 4: Final Result API ✅

- FinalResult object unificado
- Normalización de errores y warnings
- Summary con estadísticas
- JSON export y serialización completa

### Bloque 5: Security & Hardening ✅

- File size limits (10 MB por defecto)
- Batch document limits (50 documentos por defecto)
- Path traversal protection
- Security configuration y validation
- Production-ready defaults

## 📚 Documentation

- [SECURITY.md](SECURITY.md) - Política de seguridad y límites configurables
- [ARQUITECTURA.md](ARQUITECTURA.md) - Diseño arquitectónico completo
- [GUIA_RAPIDA.md](GUIA_RAPIDA.md) - Referencia rápida de uso

## 🔌 API Reference

### FinalResult

```php
$result = ContentProcessor::make()->...->processFinal();

// Access data
$result->data();           // Array de documentos exitosos
$result->errors();         // Array de errores normalizados
$result->warnings();       // Array de warnings semánticos
$result->summary();        // Summary con estadísticas

// Status checks
$result->isSuccessful();   // bool - ¿Al menos 1 exitoso?
$result->isPerfect();      // bool - ¿Sin errores ni warnings?
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

## 🚀 Next steps

Ready for production. See [SECURITY.md](SECURITY.md) for deployment recommendations.

## 📋 Requisitos

- PHP >= 8.1
- Composer

## 📄 Licencia

MIT
