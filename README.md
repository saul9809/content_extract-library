# Content Processor

**Librería PHP para procesamiento batch de documentos con extracción y estructuración de contenido.**

Diseñada para ser framework-agnostic, escalable y production-ready desde el inicio.

## 🎯 Objetivo

Procesar múltiples documentos (PDFs, textos, etc.), extraer su contenido y convertirlo en estructuras JSON configurables, preparadas para carga masiva en bases de datos o servicios.

### Ejemplo inicial esperado:

```php
$processor = ContentProcessor::make()
    ->withSchema($schema)
    ->fromDirectory('/cvs')
    ->process();
```

## 📦 Instalación

```bash
composer install
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
    'carnet_identidad' => ['type' => 'string', 'required' => false],
    'anos_experiencia' => ['type' => 'int', 'required' => false],
]);
```

### 2. Configurar el procesador

```php
use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Extractors\TextFileExtractor;
use ContentProcessor\Structurers\SimpleLineStructurer;

$processor = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new TextFileExtractor())
    ->withStructurer(new SimpleLineStructurer())
    ->fromDirectory('/path/to/docs', '*.txt')
    ->process();
```

### 3. Procesar y obtener resultados

```php
$results = $processor->process();

echo $results['success'];             // 10 (exitosos)
echo $results['failed'];              // 2  (fallidos)

// Obtener solo datos exitosos
$data = $processor->getSuccessfulData();
echo json_encode($data);              // JSON listo para exportar
```

## 🧪 Probar

```bash
cd examples
php example_basic.php
```

Esperado: Dos archivos procesados correctamente con su estructura JSON.

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

## 🚀 Próximos pasos planeados

- [ ] Extractor para PDFs
- [ ] Extractor con OCR
- [ ] Integración de IA para extracción inteligente
- [ ] Estructurador avanzado con regex/ML
- [ ] CLI para batch processing desde terminal
- [ ] Integración Laravel (como optional package)
- [ ] Tests exhaustivos (PHPUnit)
- [ ] Documentación API completa

## 📋 Requisitos

- PHP >= 8.1
- Composer

## 📄 Licencia

MIT
