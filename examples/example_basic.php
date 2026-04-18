<?php

/**
 * EJEMPLO FUNCIONAL 1: Procesamiento básico
 * 
 * Este script demuestra:
 * - Autoload funcional (PSR-4)
 * - Uso de interfaces
 * - Pipeline de procesamiento
 * - Estructuración de datos
 * - Output JSON
 */

// Autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Schemas\ArraySchema;
use ContentProcessor\Extractors\TextFileExtractor;
use ContentProcessor\Structurers\SimpleLineStructurer;

// ===== 1. Definir el esquema =====
$schema = new ArraySchema([
    'nombre' => [
        'type' => 'string',
        'required' => true,
    ],
    'carnet_identidad' => [
        'type' => 'string',
        'required' => false,
    ],
    'especialidad' => [
        'type' => 'string',
        'required' => false,
    ],
    'plaza' => [
        'type' => 'string',
        'required' => false,
    ],
    'anos_experiencia' => [
        'type' => 'int',
        'required' => false,
    ],
], 'CVSchema');

// ===== 2. Crear y configurar el procesador =====
$processor = ContentProcessor::make()
    ->withSchema($schema)
    ->withExtractor(new TextFileExtractor())
    ->withStructurer(new SimpleLineStructurer())
    ->fromDirectory(__DIR__, '*.txt')
    ->withOptions([
        'skip_invalid' => false,
        'preserve_empty' => false,
    ]);

// ===== 3. Procesar =====
echo "Procesando documentos...\n";
echo "================================================\n";

$results = $processor->process();

// ===== 4. Mostrar resultados =====
echo "\n📊 RESUMEN DE PROCESAMIENTO:\n";
echo "================================================\n";
echo sprintf("Total procesados: %d\n", $results['total']);
echo sprintf("✅ Exitosos: %d\n", $results['success']);
echo sprintf("❌ Fallidos: %d\n", $results['failed']);
echo "\n";

// Mostrar detalles de cada archivo
echo "📄 DETALLES POR ARCHIVO:\n";
echo "================================================\n";

foreach ($results['results'] as $file => $result) {
    $status = $result['success'] ? '✅ EXITOSO' : '❌ FALLIDO';
    echo "\n{$status}: {$file}\n";

    if ($result['success']) {
        echo json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo "Error: " . $result['error'];
    }
    echo "\n";
}

// ===== 5. Exportar solo los datos exitosos =====
echo "\n\n📤 EXPORTAR DATOS EXITOSOS (JSON):\n";
echo "================================================\n";

$successfulData = $processor->getSuccessfulData();
echo json_encode($successfulData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo "\n\n✅ Procesamiento completado.\n";
