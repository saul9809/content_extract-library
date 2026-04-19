<?php

/**
 * BLOQUE 4 - Ejemplo básico de FinalResult API
 * 
 * Demuestra cómo usar la nueva API robusta del Bloque 4
 * para procesar documentos y obtener resultados limpios.
 * 
 * Ejecutar: php examples/example_bloque4_basic.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Schemas\ArraySchema;
use ContentProcessor\Extractors\TextFileExtractor;
use ContentProcessor\Structurers\RuleBasedStructurer;

echo "=== BLOQUE 4: Resultado Final Robusto === \n\n";

// 1. Definir el esquema
$schema = new ArraySchema([
    'nombre' => ['type' => 'string', 'required' => true],
    'carnet_identidad' => ['type' => 'string', 'required' => false],
    'anos_experiencia' => ['type' => 'int', 'required' => false],
    'email' => ['type' => 'string', 'required' => false],
]);

// 2. Configurar el procesador
$processor = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new TextFileExtractor())
    ->withStructurer(new RuleBasedStructurer())
    ->fromDirectory(__DIR__, 'sample_cv_*.txt');

// 3. Procesar con el nuevo método FinalResult
echo "📦 Procesando archivos...\n";
$result = $processor->processFinal();

// 4. Obtener datos
echo "\n✅ DATOS EXITOSOS (" . count($result->data()) . "):\n";
echo json_encode($result->dataPure(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

// 5. Errores
echo "\n❌ ERRORES (" . $result->getErrorCount() . "):\n";
if ($result->hasErrors()) {
    foreach ($result->errors() as $error) {
        echo "  - {$error}\n";
    }
} else {
    echo "  (Sin errores)\n";
}

// 6. Warnings
echo "\n⚠️  WARNINGS (" . $result->getWarningCount() . "):\n";
if ($result->hasWarnings()) {
    foreach ($result->warnings() as $warning) {
        echo "  - {$warning}\n";
    }
} else {
    echo "  (Sin warnings)\n";
}

// 7. Resumen
echo "\n📊 RESUMEN:\n";
echo "  " . $result->summary() . "\n";
echo "  Tasa de éxito: " . $result->summary()->getSuccessRate() . "%\n";
echo "  Warnings promedio/documento: " . $result->summary()->getAverageWarningsPerDocument() . "\n";

// 8. Estados
echo "\n🎯 ESTADOS:\n";
echo "  ¿Exitoso? " . ($result->isSuccessful() ? 'SÍ' : 'NO') . "\n";
echo "  ¿Perfecto? " . ($result->isPerfect() ? 'SÍ' : 'NO') . "\n";

// 9. JSON para export
echo "\n📤 EXPORT (primeros 500 chars):\n";
$json = $result->toJSON();
echo substr($json, 0, 500) . "...\n";

echo "\n✨ ¡Bloque 4 Completado!\n";
