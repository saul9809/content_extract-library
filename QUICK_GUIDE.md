# 🚀 Guide quick de inicio

## Instalación y primeras pruebas

### 1. Verify que PHP está instalado

```powershell
php -v
```

### 2. Instalar dependencias con Composer

```powershell
cd c:\equipo\Ingeniería\tesis\TESIS\sistema\librery
composer install
```

### 3. Ejecutar la prueba funcional

```powershell
cd examples
php test_functional.php
```

**Resultado esperado:** Verde ✅ con 2 documentos procesados correctamente.

---

## Uso básico

### Paso 1: Incluir autoloader

```php
require_once __DIR__ . '/vendor/autoload.php';

use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Schemas\ArraySchema;
use ContentProcessor\Extractors\TextFileExtractor;
use ContentProcessor\Structurers\SimpleLineStructurer;
```

### Paso 2: Definir esquema

```php
$schema = new ArraySchema([
    'nombre' => ['type' => 'string', 'required' => true],
    'email' => ['type' => 'string', 'required' => false],
    'edad' => ['type' => 'int', 'required' => false],
]);
```

### Paso 3: Configurar procesador

```php
$processor = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new TextFileExtractor())
    ->withStructurer(new SimpleLineStructurer())
    ->fromDirectory('/ruta/a/documentos', '*.txt');
```

### Paso 4: Procesar

```php
$results = $processor->process();

// Ver summary
echo "Exitosos: " . $results['success'];
echo "Fallidos: " . $results['failed'];

// Obtener solo datos válidos
$data = $processor->getSuccessfulData();
echo json_encode($data, JSON_PRETTY_PRINT);
```

---

## Ejemplos completos

### Ejemplo 1: Procesar un directorio

```php
<?php
require_once 'vendor/autoload.php';

use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Schemas\ArraySchema;
use ContentProcessor\Extractors\TextFileExtractor;
use ContentProcessor\Structurers\SimpleLineStructurer;

$schema = new ArraySchema([
    'nombre' => ['type' => 'string', 'required' => true],
    'carnet' => ['type' => 'string', 'required' => true],
]);

$results = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new TextFileExtractor())
    ->withStructurer(new SimpleLineStructurer())
    ->fromDirectory('./documentos')
    ->process();

if ($results['success'] > 0) {
    $data = $processor->getSuccessfulData();
    file_put_contents('./output.json', json_encode($data, JSON_PRETTY_PRINT));
    echo "✅ {$results['success']} archivos procesados correctamente.";
} else {
    echo "❌ No se procesaron archivos exitosamente.";
}
```

### Ejemplo 2: Procesar archivos específicos

```php
$processor = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new TextFileExtractor())
    ->withStructurer(new SimpleLineStructurer())
    ->fromFiles([
        './cv_juan.txt',
        './cv_maria.txt',
    ])
    ->process();
```

### Ejemplo 3: Uso en Laravel controller

```php
namespace App\Http\Controllers;

use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Schemas\ArraySchema;
use ContentProcessor\Extractors\TextFileExtractor;
use ContentProcessor\Structurers\SimpleLineStructurer;

class DocumentProcessorController extends Controller
{
    public function processCVs()
    {
        $schema = new ArraySchema([
            'nombre' => ['type' => 'string', 'required' => true],
            'experiencia' => ['type' => 'int', 'required' => false],
        ]);

        $results = ContentProcessor::make()
            ->withSchema($schema)
            ->withExtractor(new TextFileExtractor())
            ->withStructurer(new SimpleLineStructurer())
            ->fromDirectory(storage_path('cvs'))
            ->process();

        return response()->json([
            'total' => $results['total'],
            'processed' => $results['success'],
            'failed' => $results['failed'],
            'data' => $processor->getSuccessfulData(),
        ]);
    }
}
```

---

## Estructura de archivos de entrada

Los archivos deben estar en formato `campo: valor` (una línea por campo):

**Ejemplo: cv_juan.txt**

```
nombre: Juan García López
carnet: 1234567890
especialidad: Ingeniero de Software
anos_experiencia: 8
plaza: Desarrollador Senior
```

La línea de cualquier campo:

- ✅ `nombre: Juan García` → valor = "Juan García"
- ✅ `anos_experiencia: 8` → número convertido automáticamente
- ✅ Espacios alrededor del valor se eliminan automáticamente

---

## Estructura de salida JSON

La librería genera JSON listo para:

- Base de datos (INSERT)
- APIs REST
- Análisis posterior
- Exportación a CSV/Excel

Ejemplo:

```json
[
  {
    "nombre": "Juan García López",
    "carnet": "1234567890",
    "especialidad": "Ingeniero de Software",
    "anos_experiencia": 8,
    "plaza": "Desarrollador Senior"
  }
]
```

---

## Preguntas frecuentes

### ¿Puedo usar sin Composer?

Sí, usa el autoloader manual:

```php
require_once 'autoload_manual.php';
```

Pero Composer es recomendado para projects profesionales.

### ¿Puedo extender las clases?

Sí, implementa las interfaces:

```php
class MiExtractor implements ExtractorInterface { ... }
```

### ¿Qué tipos soporta ArraySchema?

- `string`: texto
- `int`: números enteros
- `float`: números decimales
- `bool`: verdadero/falso
- `array`: listas separadas por comas

### ¿Qué sucede si falta un campo requerido?

El documento es marcado como fallido y registrado el error.

### ¿Puedo procesar miles de archivos?

Sí. El diseño está optimizado para batch processing.

---

## Próximos pasos

1. **Crear un extractor PDF** (Block 2)
2. **Crear un estructurador avanzado** (Block 3)
3. **Agregar tests unitarios** (Block 4)
4. **Publicar en Packagist** (Producción)

---

## Soporte y reportar bugs

Por ahora, la librería está en fase alpha. Cambios pueden ocurrir en each block.

**Status actual:** ✅ Block 1 funcional, lista para extensión.
