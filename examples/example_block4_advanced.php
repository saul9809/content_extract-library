<?php

/**
 * BLOCK 4 - Ejemplo avanzado: Batch processing robusto
 * 
 * Simula:
 * - Carga de múltiples documentos
 * - Mezcla de documentos válidos e inválidos
 * - Captura de errors y warnings
 * - Generación de reportes
 * 
 * Ejecutar: php examples/example_bloque4_advanced.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Schemas\ArraySchema;
use ContentProcessor\Extractors\TextFileExtractor;
use ContentProcessor\Structurers\RuleBasedStructurer;

echo "=== BLOCK 4: Batch Processing Robusto ===\n\n";

// 1. Crear  de prueba con  simulados
$testDir = __DIR__ . '/test_bloque4_batch';
@mkdir($testDir, 0777, true);

//  válido 1
file_put_contents(
    "$testDir/valido_1.txt",
    <<<'TXT'
Name: Juan García Pérez
Carnet de Identidad: 12345678-K
Años de Experiencia: 5
Email: juan@example.com
TXT
);

//  válido 2
file_put_contents(
    "$testDir/valido_2.txt",
    <<<'TXT'
Name: María López Martínez
Años de Experiencia: 8
Email: maria@example.com
TXT
);

//  con  incompletos (warnings)
file_put_contents(
    "$testDir/incompleto.txt",
    <<<'TXT'
Name: Carlos 

Años de Experiencia: 10
TXT
);

//  vacío ()
file_put_contents("$testDir/vacio.txt", "");

//  mal formado ( )
file_put_contents("$testDir/malformado.txt", "edad años: treinta años");

echo "📁 Directorio de prueba creado con 5 files\n";
echo "   ✅ 2 válidos\n";
echo "   ⚠️  1 incompleto (warnings)\n";
echo "   ❌ 2 con errors\n\n";

// 2. Esquema de prueba
$schema = new ArraySchema([
    'name' => ['type' => 'string', 'required' => true],
    'carnet_identidad' => ['type' => 'string', 'required' => false],
    'anos_experiencia' => ['type' => 'int', 'required' => false],
    'email' => ['type' => 'string', 'required' => false],
]);

// 3. 
echo "⚙️  Procesando files batch...\n\n";

$processor = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new TextFileExtractor())
    ->withStructurer(new RuleBasedStructurer())
    ->fromDirectory($testDir, '*.txt');

$result = $processor->processFinal();

// 4. Análisis detallado
echo "╔════════════════════════════════════════╗\n";
echo "║          ANÁLISIS DE RESULTADOS        ║\n";
echo "╚════════════════════════════════════════╝\n\n";

//  s
echo "✅ DOCUMENTOS SUCCESSFULS (" . count($result->data()) . ")\n";
echo "─────────────────────────\n";
foreach ($result->data() as $item) {
    echo "  📄 " . $item['document'] . "\n";
    echo "     └─ " . json_encode($item['data']) . "\n";
}

//  por tipo
echo "\n❌ ERRORS POR TIPO\n";
echo "─────────────────────\n";
foreach (['extraction', 'validation', 'runtime'] as $type) {
    $byType = $result->errorsByType($type);
    if (count($byType) > 0) {
        echo "  [$type]: " . count($byType) . "\n";
        foreach ($byType as $error) {
            echo "    - " . $error->getMessage() . "\n";
        }
    }
}

// Warnings por categoría
echo "\n⚠️  WARNINGS POR CATEGORÍA\n";
echo "──────────────────────────\n";
$categories = ['missing', 'ambiguousus', 'incomplete', 'type_mismatch'];
foreach ($categories as $cat) {
    $byCat = $result->warningsByCategory($cat);
    if (count($byCat) > 0) {
        echo "  [$cat]: " . count($byCat) . "\n";
        foreach ($byCat as $warning) {
            echo "    - {$warning->getField()}: {$warning->getMessage()}\n";
        }
    }
}

// Métricas
echo "\n📊 MÉTRICAS\n";
echo "───────────\n";
$summary = $result->summary();
echo "  Total documentos: " . $summary->getTotalDocuments() . "\n";
echo "  Successfuls: " . $summary->getSuccessfulDocuments() . " (" . $summary->getSuccessRate() . "%)\n";
echo "  Fallidos: " . $summary->getFailedDocuments() . " (" . $summary->getFailureRate() . "%)\n";
echo "  Errors totales: " . $summary->getTotalErrors() . "\n";
echo "  Warnings totales: " . $summary->getTotalWarnings() . "\n";
echo "  Tiempo processing: " . number_format($summary->getProcessingTime(), 3) . "s\n";

// APIs interesantes
echo "\n🎯 CONSULTAS ESPECÍFICAS\n";
echo "────────────────────────\n";
echo "  ¿Hay errors? " . ($result->hasErrors() ? 'SÍ' : 'NO') . "\n";
echo "  ¿Hay warnings? " . ($result->hasWarnings() ? 'SÍ' : 'NO') . "\n";
echo "  ¿Es successful? " . ($result->isSuccessful() ? 'SÍ' : 'NO') . "\n";
echo "  ¿Es perfecto? " . ($result->isPerfect() ? 'SÍ' : 'NO') . "\n";

// JSON export
echo "\n📤 EXPORT COMPLETO (JSON)\n";
echo "────────────────────────\n";
echo "Tamaño: " . strlen($result->toJSON()) . " bytes\n";
file_put_contents("$testDir/resultado_batch.json", $result->toJSONPretty());
echo "Guardado en: $testDir/resultado_batch.json\n";

// Limpieza
echo "\n🧹 Limpiando...\n";
foreach (glob("$testDir/*.txt") as $file) {
    @unlink($file);
}
@rmdir($testDir);

echo "\n✨ ¡Ejemplo Block 4 Advanced completado!\n";
