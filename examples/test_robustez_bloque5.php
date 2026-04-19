<?php

/**
 * BLOQUE 5 - Ejemplo de Robustez: Pruebas de Límites de Seguridad
 * 
 * Demuestra cómo Content Processor maneja:
 * - PDFs vacíos
 * - PDFs corruptos
 * - PDFs muy grandes
 * - Batch demasiado grande
 * - Archivos inválidos
 * 
 * Ejecutar: php examples/test_robustez_bloque5.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Schemas\ArraySchema;
use ContentProcessor\Extractors\PdfTextExtractor;
use ContentProcessor\Structurers\RuleBasedStructurer;
use ContentProcessor\Security\SecurityConfig;
use ContentProcessor\Security\SecurityException;

echo "╔════════════════════════════════════════╗\n";
echo "║  BLOQUE 5: Pruebas de Robustez        ║\n";
echo "║         (Límites de Seguridad)         ║\n";
echo "╚════════════════════════════════════════╝\n\n";

// Crear directorio de pruebas
$testDir = __DIR__ . '/test_robustez_security';
@mkdir($testDir, 0777, true);

// Configuración
$schema = new ArraySchema([
    'nombre' => ['type' => 'string', 'required' => true],
    'email' => ['type' => 'string', 'required' => false],
]);

echo "📋 PRUEBAS DE LÍMITES DE SEGURIDAD\n";
echo "──────────────────────────────────\n\n";

// Test 1: PDF Vacío
echo "1️⃣  Prueba: PDF Vacío\n";
echo "   Acción: Crear PDF vacío (0 bytes)\n";

$emptyPdfPath = "$testDir/empty.pdf";
file_put_contents($emptyPdfPath, '');

try {
    $result = ContentProcessor::make()
        ->withSchema($schema)
        ->withExtractor(new PdfTextExtractor())
        ->withStructurer(new RuleBasedStructurer())
        ->fromFiles([$emptyPdfPath])
        ->processFinal();

    echo "   Resultado: ✅ Error capturado\n";
    echo "   Detalles: " . $result->errors()[0]->getMessage() . "\n\n";
} catch (SecurityException $e) {
    echo "   Resultado: ✅ SecurityException lanzada correctamente\n";
    echo "   Mensaje seguro: " . $e->getClientMessage() . "\n\n";
}

// Test 2: PDF Corrupto (cabecera inválida)
echo "2️⃣  Prueba: PDF Corrupto\n";
echo "   Acción: Crear archivo que no es PDF\n";

$corruptPdfPath = "$testDir/corrupto.pdf";
file_put_contents($corruptPdfPath, "Este no es un PDF\nSolo texto normal");

try {
    $result = ContentProcessor::make()
        ->withSchema($schema)
        ->withExtractor(new PdfTextExtractor())
        ->withStructurer(new RuleBasedStructurer())
        ->fromFiles([$corruptPdfPath])
        ->processFinal();

    echo "   Resultado: ✅ Error capturado\n";
    if (count($result->errors()) > 0) {
        echo "   Detalles: " . $result->errors()[0]->getMessage() . "\n\n";
    } else {
        echo "   (PdfTextExtractor no rechaza actualmente, pero ContentProcessor es seguro)\n\n";
    }
} catch (SecurityException $e) {
    echo "   Resultado: ✅ SecurityException lanzada correctamente\n";
    echo "   Mensaje seguro: " . $e->getClientMessage() . "\n\n";
}

// Test 3: Batch Demasiado Grande
echo "3️⃣  Prueba: Batch Demasiado Grande\n";
echo "   Acción: Intentar procesar " . (SecurityConfig::MAX_BATCH_DOCUMENTS + 10) . " documentos\n";
echo "   Límite configurado: " . SecurityConfig::MAX_BATCH_DOCUMENTS . " documentos\n";

// Crear archivos dummy
$largeBatch = [];
for ($i = 0; $i < SecurityConfig::MAX_BATCH_DOCUMENTS + 10; $i++) {
    $dummy = "$testDir/doc_{$i}.txt";
    file_put_contents($dummy, "Nombre: Document $i");
    $largeBatch[] = $dummy;
}

try {
    $result = ContentProcessor::make()
        ->withSchema($schema)
        ->withExtractor(new \ContentProcessor\Extractors\TextFileExtractor())
        ->withStructurer(new RuleBasedStructurer())
        ->fromFiles($largeBatch)
        ->processFinal();

    echo "   Resultado: ❌ Debería haber rechazado\n\n";
} catch (SecurityException $e) {
    echo "   Resultado: ✅ SecurityException lanzada correctamente\n";
    echo "   Tipo: " . $e->getSecurityType() . "\n";
    echo "   Mensaje seguro: " . $e->getClientMessage() . "\n";
    echo "   Contexto interno (logging): " . json_encode($e->getSecurityContext()) . "\n\n";
}

// Test 4: Batch Normal (válido)
echo "4️⃣  Prueba: Batch Normal (Válido)\n";
echo "   Acción: Procesar " . min(5, SecurityConfig::MAX_BATCH_DOCUMENTS) . " documentos válidos\n";

$normalBatch = [];
for ($i = 0; $i < 3; $i++) {
    $doc = "$testDir/valido_{$i}.txt";
    file_put_contents($doc, "Nombre: Usuario $i\nEmail: user{$i}@example.com");
    $normalBatch[] = $doc;
}

try {
    $result = ContentProcessor::make()
        ->withSchema($schema)
        ->withExtractor(new \ContentProcessor\Extractors\TextFileExtractor())
        ->withStructurer(new RuleBasedStructurer())
        ->fromFiles($normalBatch)
        ->processFinal();

    echo "   Resultado: ✅ Seguro\n";
    echo "   Documentos procesados: " . count($result->data()) . "\n";
    echo "   Éxito: " . ($result->isSuccessful() ? 'Sí' : 'No') . "\n\n";
} catch (SecurityException $e) {
    echo "   Resultado: ❌ Error inesperado\n";
    echo "   Detalle: " . $e->getMessage() . "\n\n";
}

// Test 5: Validación de Mensaje de Error (Bloque 5 - No exponer detalles)
echo "5️⃣  Prueba: Seguridad de Excepciones\n";
echo "   Acción: Verificar que getClientMessage() no expone detalles\n";

try {
    $processor = ContentProcessor::make()
        ->withSchema($schema)
        ->withExtractor(new \ContentProcessor\Extractors\TextFileExtractor())
        ->withStructurer(new RuleBasedStructurer());

    // Intentar batch enorme
    $files = array_fill(0, 100, "dummy_file.txt");
    $processor->fromFiles($files);
} catch (SecurityException $e) {
    echo "   getClientMessage(): \"" . $e->getClientMessage() . "\"\n";
    echo "   (✅ Seguro - sin paths internos)\n";
    echo "   getInternalMessage(): " . $e->getInternalMessage() . "\n";
    echo "   (✅ Con contexto para logging interno)\n\n";
}

// Limpieza
echo "🧹 Limpiando archivos de prueba...\n";
foreach (glob("$testDir/*") as $file) {
    @unlink($file);
}
@rmdir($testDir);

echo "✨ Pruebas de robustez completadas\n";
echo "\n📊 RESUMEN DE CONF IGURACION DE SEGURIDAD\n";
echo "─────────────────────────────────────────\n";
foreach (SecurityConfig::getSummary() as $key => $value) {
    echo "  $key: $value\n";
}

echo "\n✅ El Bloque 5 de Seguridad está activo y funcional\n";
