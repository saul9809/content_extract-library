# BLOQUE 4 - Resultado Final, Robustez y DX

**Versión:** 1.3.0 (Block 4)  
**Fecha:** Abril 2026  
**Status:** ✅ COMPLETED

---

## 🎯 Objetivo del Block 4

Proveer un resultado final único, robusto y fácil de consumir para desarrolladores que utilizan Laravel, PHP puro, o cualquier framework.

**Principios:**

- ✅ API limpia y unificada
- ✅ Normalización de errores y warnings
- ✅ Métricas y estadísticas integradas
- ✅ Debuggability completa
- ✅ Export a JSON directo
- ✅ Backward compatible (Bloques 1-3 intactos)

---

## 📦 Clases Nuevas (Block 4)

### 1. `FinalResult` — Resultado Principal

**Ubicación:** `src/Models/FinalResult.php`

Encapsula todo el resultado de un procesamiento batch:

- Datos estructurados exitosos
- Errores técnicos normalizados
- Warnings semánticos normalizados
- Estadísticas y métricas

#### API Pública

```php
// Obtener datos
$result->data()           // Array de documentos exitosos
$result->dataPure()       // Solo los datos sin metadata

// Obtener problemas
$result->errors()         // Array de Error[]
$result->warnings()       // Array de Warning[]

// Checks rápidos
$result->hasErrors()      // bool
$result->hasWarnings()    // bool
$result->isSuccessful()   // bool (sin errores)
$result->isPerfect()      // bool (sin errores ni warnings)

// Contar
$result->getSuccessCount() // int
$result->getErrorCount()   // int
$result->getWarningCount() // int

// Filtrar
$result->errorsByType('validation')      // Error[]
$result->warningsByField('nombre')       // Warning[]
$result->warningsByCategory('missing')   // Warning[]

// Estadísticas
$result->summary()        // Summary object

// Debugging
$result->fullResults()    // Array con detalles completos

// Export
$result->toArray()        // Array PHP
$result->toJSON()         // JSON string
$result->toJSONPretty()   // JSON formateado
$result->__toString()     // String legible
```

### 2. `Error` — Normalización de Errores

**Ubicación:** `src/Models/Error.php`

Estructura normalizada para errores técnicos.

```php
$error = new Error(
    'validation',
    'Campo requerido faltante',
    ['file' => 'documento.pdf'],
    'ERR_SCHEMA_005'
);

// Métodos
$error->getType()      // string: 'extraction', 'validation', 'runtime'
$error->getMessage()   // string
$error->getContext()   // array
$error->contextGet('file') // mixed (con default)
$error->getCode()      // string|null
$error->getTimestamp() // int
$error->toArray()      // array
$error->__toString()   // string legible

// Factories
Error::extraction('No se pudo extraer texto')
Error::validation('Validación fallida')
Error::runtime('Error en tiempo de ejecución')
```

### 3. `Warning` — Normalización de Warnings

**Ubicación:** `src/Models/Warning.php`

Estructura normalizada para warnings semánticos (de Block 3).

```php
$warning = new Warning(
    'nombre',
    'missing',
    'Campo nombre vacío o faltante',
    null
);

// Métodos
$warning->getField()      // string
$warning->getCategory()   // string
$warning->getMessage()    // string
$warning->getValue()      // mixed
$warning->getTimestamp()  // int
$warning->toArray()       // array
$warning->__toString()    // string legible

// Factories
Warning::missing('email')
Warning::typeMismatch('edad', 'int', 'string')
Warning::ambiguous('fecha', 'Formato de fecha ambiguo')
Warning::incomplete('experiencia', 'Datos incompletos')
```

### 4. `Summary` — Estadísticas y Métricas

**Ubicación:** `src/Models/Summary.php`

Summary de estadísticas del batch processing.

```php
$summary = new Summary(
    100,    // total
    95,     // exitosos
    5,      // fallidos
    12,     // warnings
    5,      // errores
    1.234   // tiempo en segundos
);

// Métricas
$summary->getTotalDocuments()       // 100
$summary->getSuccessfulDocuments()  // 95
$summary->getFailedDocuments()      // 5
$summary->getTotalWarnings()        // 12
$summary->getTotalErrors()          // 5

// Tasas
$summary->getSuccessRate()          // 95.0 %
$summary->getFailureRate()          // 5.0 %

// Promedios
$summary->getAverageWarningsPerDocument() // 0.12

// Timestamps
$summary->getStartedAt()
$summary->getFinishedAt()
$summary->getProcessingTime()

// Formato
$summary->getSummaryString()        // "95/100 exitosos (95.0%), ..."
$summary->toArray()                 // array completo
$summary->__toString()              // igual a getSummaryString()
```

---

## 🔄 Cambios en ContentProcessor

### Nuevo Método: `processFinal()`

```php
/**
 * Procesa y retorna FinalResult (Block 4)
 * API recomendada para nuevos desarrollos.
 */
public function processFinal(): FinalResult
```

### Métodos Antiguos: Mantienen Compatibilidad

```php
// Block 1-3: Seguir funcionando igual
public function process(): array
public function getResults(): array
public function getSuccessfulData(): array
```

### Ejemplo Migración

```php
// Block 3 (viejo)
$raw = $processor->process();
$data = $processor->getSuccessfulData();

// Block 4 (nuevo, recomendado)
$result = $processor->processFinal();
$data = $result->dataPure();
$errors = $result->errors();
$warnings = $result->warnings();
```

---

## 📚 Ejemplos Prácticos

### Ejemplo 1: Uso Básico

```php
use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Schemas\ArraySchema;
use ContentProcessor\Extractors\TextFileExtractor;
use ContentProcessor\Structurers\RuleBasedStructurer;

$schema = new ArraySchema([
    'nombre' => ['type' => 'string', 'required' => true],
    'email' => ['type' => 'string', 'required' => false],
]);

$result = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new TextFileExtractor())
    ->withStructurer(new RuleBasedStructurer())
    ->fromDirectory('/documentos')
    ->processFinal();

// API limpia
$data = $result->data();              // Documentos exitosos
$errors = $result->errors();          // Errores técnicos
$warnings = $result->warnings();      // Warnings semánticos
$summary = $result->summary();        // Métricas

echo $summary->getSummaryString();
// Output: "95/100 exitosos (95.0%), 5 errores, 12 warnings, 1.23s"
```

### Ejemplo 2: Check y Acción

```php
$result = $processor->processFinal();

if ($result->isPerfect()) {
    // Todos OK, sin warnings
    $db->batchInsert($result->dataPure());
} elseif ($result->isSuccessful()) {
    // Hay warnings pero sin errores
    $db->batchInsert($result->dataPure());
    $logger->warning("Warnings: " . count($result->warnings()));
} else {
    // Hay errores
    foreach ($result->errors() as $error) {
        $logger->error((string)$error);
    }
}
```

### Ejemplo 3: Filtrado y Análisis

```php
// Errores de validación
$validationErrors = $result->errorsByType('validation');

// Warnings de campos faltantes
$missingFields = $result->warningsByCategory('missing');

// Warnings en campo específico
$nameWarnings = $result->warningsByField('nombre');

// Tasa de calidad
$qualityScore = $result->summary()->getSuccessRate();
if ($qualityScore < 90) {
    $alert->sendQualityAlert($qualityScore);
}
```

### Ejemplo 4: Export y Auditoría

```php
// JSON para REST API
$json = $result->toJSON();
http_response_code($result->isSuccessful() ? 200 : 422);
header('Content-Type: application/json');
echo $json;

// Guardada en archivo para auditoría
file_put_contents(
    "logs/batch_{$date}.json",
    $result->toJSONPretty()
);

// Debugging: detalles completos
$full = $result->fullResults(); // Array con cada documento
foreach ($full as $doc) {
    if (!$doc['success']) {
        error_log($doc['source'] . ': ' . $doc['error']);
    }
}
```

---

## 🧪 Pasos para Probar

### Setup Inicial

```bash
cd /path/to/librery
composer install
```

### Ejecutar Ejemplos

```bash
# Block 4 - Básico
php examples/example_bloque4_basic.php

# Block 4 - Avanzado (batch con errores)
php examples/example_bloque4_advanced.php

# Block 4 - Laravel-style (consumo API)
php examples/example_bloque4_laravel_style.php
```

### Verificar Compatibilidad (Bloques 1-3)

```bash
# Block 1 - Debe seguir funcionando
php examples/example_basic.php

# Block 3 - Debe seguir funcionando
php examples/test_structuring.php
php examples/test_structuring_advanced.php
```

### Verificar Autoload

```bash
# Si Composer no está disponible
php -r "require 'autoload_manual.php'; echo 'OK';"
```

---

## 📤 Output Esperado

### ejemplo_bloque4_basic.php

```
=== BLOQUE 4: Resultado Final Robusto ===

📦 Procesando archivos...

✅ DATOS EXITOSOS (2):
[
  {
    "nombre": "Juan García Pérez",
    "carnet_identidad": "12345678-K",
    "anos_experiencia": 5,
    "email": "juan@example.com"
  },
  {
    "nombre": "María López Martínez",
    "carnet_identidad": null,
    "anos_experiencia": 8,
    "email": "maria@example.com"
  }
]

❌ ERRORES (0):
  (Sin errores)

⚠️  WARNINGS (2):
  - [missing:email] Campo 'email' faltante o vacío
  - [incomplete:carnet_identidad] Información incompleta en carnet_identidad

📊 SUMMARY:
  2/2 exitosos (100.0%), 0 errores, 2 warnings, 0.045s
  Tasa de éxito: 100%
  Warnings promedio/documento: 1

🎯 STATUSS:
  ¿Exitoso? SÍ
  ¿Perfecto? NO

📤 EXPORT (primeros 500 chars):
{
  "success": true,
  "perfect": false,
  "data": {
    "count": 2,
    "items": [...]
  },
  ...
}

✨ ¡Block 4 Completed!
```

### ejemplo_bloque4_advanced.php

```
=== BLOQUE 4: Batch Processing Robusto ===

📁 Directorio de prueba creado con 5 archivos
   ✅ 2 válidos
   ⚠️  1 incompleto (warnings)
   ❌ 2 con errores

⚙️  Procesando archivos batch...

╔════════════════════════════════════════╗
║          ANÁLISIS DE RESULTADOS        ║
╚════════════════════════════════════════╝

✅ DOCUMENTOS EXITOSOS (2)
─────────────────────────
  📄 valido_1.txt
     └─ {"nombre":"Juan García Pérez",...}
  📄 valido_2.txt
     └─ {"nombre":"María López Martínez",...}

❌ ERRORES POR TIPO
─────────────────────
  [extraction]: 2
    - El extractor no puede procesar...
    - Validación fallida...

⚠️  WARNINGS POR CATEGORÍA
──────────────────────────
  [missing]: 1
    - email: Campo 'email' faltante
  [incomplete]: 2
    - nombre: Campos vacíos detectados

📊 MÉTRICAS
───────────
  Total documentos: 5
  Exitosos: 2 (40%)
  Fallidos: 3 (60%)
  Errores totales: 3
  Warnings totales: 3
  Tiempo procesamiento: 0.032s

🎯 CONSULTAS ESPECÍFICAS
────────────────────────
  ¿Hay errores? SÍ
  ¿Hay warnings? SÍ
  ¿Es exitoso? NO
  ¿Es perfecto? NO

📤 EXPORT COMPLETO (JSON)
────────────────────────
Tamaño: 2456 bytes
Guardado en: /tmp/.../resultado_batch.json

✨ ¡Ejemplo Block 4 Advanced completed!
```

---

## ✅ Checklist de Closure

- [x] **Clase FinalResult** — Encapsula todo el resultado
- [x] **Clase Error** — Normaliza errores técnicos
- [x] **Clase Warning** — Normaliza warnings semánticos
- [x] **Clase Summary** — Proporciona métricas
- [x] **Método ContentProcessor::processFinal()** — API nueva
- [x] **Ejemplos básicos** — example_bloque4_basic.php
- [x] **Ejemplos avanzados** — example_bloque4_advanced.php
- [x] **Ejemplos Laravel-style** — example_bloque4_laravel_style.php
- [x] **Documentation** — Este archivo
- [x] **Backward compatibility** — Bloques 1-3 intactos
- [x] **PSR-4 / PSR-12** — Código limpio y namespaced

---

## 🎯 Lo Que Se Puede Hacer Ahora

### Para Desarrolladores Laravel

```php
// En Controlador
Route::post('/documents/process', function (Request $request) {
    $result = ContentProcessor::make()
        ->withSchema($schema)
        ->fromFiles($request->file('documents'))
        ->processFinal();

    return response()->json([
        'success' => $result->isSuccessful(),
        'data' => $result->data(),
        'errors' => $result->errorsToArray(),
        'warnings' => $result->warningsToArray(),
    ]);
});
```

### Para PHP Puro

```php
$result = $processor->processFinal();

if ($result->isSuccessful()) {
    foreach ($result->dataPure() as $data) {
        // Procesar datos
    }
}

// Log de errores
foreach ($result->errors() as $error) {
    error_log($error->getMessage());
}
```

### Para Batch Jobs

```bash
// CLI script con procesamiento batch
php batch_processor.php /documentos --schema=cv_schema.json

// Genera JSON de resultado
// Usado para auditoría, reintento, etc.
```

---

## 📊 Summary del Block 4

| Aspecto             | Descripción                                |
| ------------------- | ------------------------------------------ |
| **Nuevas clases**   | FinalResult, Error, Warning, Summary (4/4) |
| **Nuevos métodos**  | ContentProcessor::processFinal()           |
| **API limpia**      | ✅ 15+ métodos en FinalResult              |
| **Backward compat** | ✅ process() sigue funcionando             |
| **Normalización**   | ✅ Errores y warnings estandarizados       |
| **Métricas**        | ✅ Completas en Summary                    |
| **Export**          | ✅ JSON, Array, String                     |
| **DX**              | ✅ API intuitiva y descubrible             |
| **Ejemplos**        | ✅ 3 completamente funcionales             |
| **Documentation**   | ✅ Este archivo                            |

---

## 🔚 Conclusión

El **Block 4** cierra el contrato final del Content Processor proporcionando:

1. **Resultado único y robusto** — FinalResult encapsula todo
2. **API clara y limpia** — Métodos intuitivos y bien nombrados
3. **Normalización completa** — Errores y warnings estructurados
4. **Métricas integradas** — Summary con estadísticas útiles
5. **Experiencia de desarrollo mejorada** — DX (Developer Experience)
6. **100% Backward compatible** — Bloques 1-3 sin cambios

**La librería está lista para producción.**

---

_Fin del Block 4_
