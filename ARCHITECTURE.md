src/ ← Código fuente (PSR-4)
├── Contracts/ ← 3 interfaces
├── Core/ ← Clase principal
├── Schemas/ + Extractors/ + Structurers/ ← Implementaciones

examples/ ← Ejemplos y pruebas
├── test*functional.php ← ✅ Ejecutado exitosamente
├── example_basic.php
└── sample_cv*\*.txt ← Datos de prueba

Documentation:
├── README.md ← Introducción
├── GUIA_RAPIDA.md ← Primeros pasos
├── ARCHITECTURE.md ← Diseño completo
├── BLOQUE_1_COMPLETED.md ← Status detallado
├── STATUS.md ← Summary executive
└── VERIFICACION.md ← Checklistsrc/ ← Código fuente (PSR-4)
├── Contracts/ ← 3 interfaces
├── Core/ ← Clase principal
├── Schemas/ + Extractors/ + Structurers/ ← Implementaciones

examples/ ← Ejemplos y pruebas
├── test*functional.php ← ✅ Ejecutado exitosamente
├── example_basic.php
└── sample_cv*\*.txt ← Datos de prueba

Documentation:
├── README.md ← Introducción
├── GUIA_RAPIDA.md ← Primeros pasos
├── ARCHITECTURE.md ← Diseño completo
├── BLOQUE_1_COMPLETED.md ← Status detallado
├── STATUS.md ← Summary executive
└── VERIFICACION.md ← Checklist# Content Processor - Architecture del Project

## 🏗️ Estructura General

```
librery/
├── 📄 composer.json                    # Configuración de Composer
├── 📄 README.md                        # Documentation principal
├── 📄 BLOQUE_1_COMPLETED.md          # Status del bloque 1
├── 📄 .gitignore                       # Exclusiones de git
├── 📄 autoload_manual.php              # Autoloader fallback
├── 📁 src/                             # Código fuente (PSR-4)
│   ├── 📁 Contracts/                   # Interfaces y contratos
│   │   ├── ExtractorInterface.php      # Contrato de extractores
│   │   ├── StructurerInterface.php     # Contrato de estructuradores
│   │   └── SchemaInterface.php         # Contrato de esquemas
│   ├── 📁 Core/
│   │   └── ContentProcessor.php        # Orqustatusr principal
│   ├── 📁 Schemas/
│   │   └── ArraySchema.php             # Esquema basado en array
│   ├── 📁 Extractors/
│   │   └── TextFileExtractor.php       # Extractor de texto plano
│   └── 📁 Structurers/
│       └── SimpleLineStructurer.php    # Estructurador línea-a-línea
├── 📁 examples/                        # Ejemplos y pruebas
│   ├── test_functional.php             # Prueba funcional completa
│   ├── example_basic.php               # Ejemplo básico de uso
│   ├── sample_cv_1.txt                 # Datos de prueba 1
│   └── sample_cv_2.txt                 # Datos de prueba 2
└── 📁 vendor/                          # Dependencias (después de composer install)
    └── autoload.php                    # Autoloader generado por Composer
```

---

## 📐 Diseño de componentes

### Layer 1: Contratos (Interfaces)

Define el "qué" debe hacer cada componente sin especificar el "cómo".

```
ExtractorInterface
├── extract(string $source): array
├── canHandle(string $source): bool
└── getName(): string

StructurerInterface
├── structure(array $content, SchemaInterface $schema): array
└── getName(): string

SchemaInterface
├── getDefinition(): array
├── validate(array $data): array
└── getName(): string
```

### Layer 2: Implementaciones

Cada interfaz tiene una o más implementaciones concretas.

```
Extractors/
├── TextFileExtractor (v1.0)
└── [Futuro: PDFExtractor, OCRExtractor]

Structurers/
├── SimpleLineStructurer (v1.0)
└── [Futuro: RegexStructurer, MLStructurer]

Schemas/
├── ArraySchema (v1.0)
└── [Futuro: JSONSchema, XMLSchema]
```

### Layer 3: Orquestación

`ContentProcessor` coordina todo el pipeline.

```
ContentProcessor
├── Inyecta ExtractorInterface
├── Inyecta StructurerInterface
├── Inyecta SchemaInterface
└── Orquesta: extract() → structure() → validate() → output JSON
```

---

## 🔄 Flujo de procesamiento

```
INICIO
  ↓
[1] make() → Nueva instancia
  ↓
[2] withSchema($schema) → Definir estructura objetivo
  ↓
[3] withExtractor($ext) → Estrategia de lectura
  ↓
[4] withStructurer($struct) → Estrategia de transformación
  ↓
[5] fromDirectory($path) → Fuentes a procesar
  ↓
[6] process() ──┐
               ↓
            PARA CADA FUENTE:
               ├─ $extractor->extract($source)       → array $content
               ├─ $structurer->structure($content)   → array $structured
               ├─ $schema->validate($structured)     → bool valid
               └─ recordResult(...) → agregar a resultados
               ↓
[7] getResults() ← array con todos los resultados
   getSuccessfulData() ← solo los válidos
  ↓
FIN
```

---

## 🔌 Puntos de extensión

La architecture está diseñada para ser extensible en cada capa:

### Extensión 1: Nuevo extractor

```php
class PDFExtractor implements ExtractorInterface {
    public function extract(string $source): array { ... }
    public function canHandle(string $source): bool { ... }
    public function getName(): string { ... }
}

// Uso:
ContentProcessor::make()
    ->withExtractor(new PDFExtractor())
    ...
```

### Extensión 2: Nuevo estructurador

```php
class RegexStructurer implements StructurerInterface {
    public function structure(array $content, SchemaInterface $schema): array { ... }
    public function getName(): string { ... }
}

// Uso:
ContentProcessor::make()
    ->withStructurer(new RegexStructurer())
    ...
```

### Extensión 3: Nuevo esquema

```php
class JSONSchema implements SchemaInterface {
    public function getDefinition(): array { ... }
    public function validate(array $data): array { ... }
    public function getName(): string { ... }
}

// Uso:
ContentProcessor::make()
    ->withSchema(new JSONSchema(__DIR__ . '/schema.json'))
    ...
```

---

## 📦 Principios arquitectónicos

1. **Dependency Injection:** Todo inyectado, nada instanciado internamente.
2. **Single Responsibility:** Cada clase hace UNA cosa bien.
3. **Open/Closed:** Abierto a extensión, cerrado a modificación (interfaces).
4. **Liskov Substitution:** Cualquier implementación de interfaz es intercambiable.
5. **Interface Segregation:** Interfaces pequeñas y específicas.
6. **Composition Over Inheritance:** Usando interfaces, no herencia profunda.

---

## 🎯 Casos de uso soportados

### Caso 1: Batch de CVs (v1.0 - Actual)

```php
$processor = ContentProcessor::make()
    ->withSchema(new CVSchema())
    ->withExtractor(new TextFileExtractor())
    ->withStructurer(new SimpleLineStructurer())
    ->fromDirectory('/cvs')
    ->process();
```

### Caso 2: Batch de PDFs (v2.0 - Futuro)

```php
$processor = ContentProcessor::make()
    ->withExtractor(new PDFExtractor())
    ->withStructurer(new RegexStructurer())
    ...
```

### Caso 3: Batch con OCR (v3.0 - Futuro)

```php
$processor = ContentProcessor::make()
    ->withExtractor(new OCRExtractor())
    ->withStructurer(new MLStructurer())
    ...
```

---

## ✅ Garantías de diseño

- **Type Safety:** PHP 8.1+ con tipos declarados en todas partes.
- **DX (Developer Experience):** Fluent interface, métodos naming claros.
- **Production-Ready:** Error handling, validación, logging preparado.
- **Testability:** Inyección de dependencias permite fácil mocking.
- **Scalability:** Batch processing, manejo de múltiples archivos sin máxima.
- **Pure PHP:** Sin dependencias runtime, solo Composer para autoload.

---

## 🔗 Documentation referencia

- [PSR-4 Autoloading](https://www.php-fig.org/psr/psr-4/)
- [PSR-12 Coding Style](https://www.php-fig.org/psr/psr-12/)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)
- [Composer Documentation](https://getcomposer.org/doc/)
