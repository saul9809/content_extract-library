<?php

/**
 * Ejemplo: Estructuración Semántica de PDFs (Bloque 3)
 * 
 * Demuestra el flujo completo:
 * 1. Extracta texto de múltiples PDFs (Bloque 2)
 * 2. Estructura el texto con RuleBasedStructurer (Bloque 3)
 * 3. Genera JSON estructurado + warnings + errores técnicos
 * 4. Output con análisis de calidad
 * 
 * Uso:
 *     php examples/test_structuring.php
 * 
 * @since Bloque 3 - Estructuración Semántica
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../autoload_manual.php';

use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Extractors\PdfTextExtractor;
use ContentProcessor\Structurers\RuleBasedStructurer;
use ContentProcessor\Schemas\ArraySchema;

// ============================================================================
// 1. Definir el Schema
// ============================================================================
// Este schema define la estructura esperada para un CV

$cvSchema = new ArraySchema([
    'name' => [
        'type' => 'string',
        'required' => true,
    ],
    'email' => [
        'type' => 'string',
        'required' => true,
    ],
    'phone' => [
        'type' => 'string',
        'required' => false,
    ],
    'experience_years' => [
        'type' => 'integer',
        'required' => false,
    ],
    'skills' => [
        'type' => 'array',
        'required' => false,
    ],
    'education' => [
        'type' => 'string',
        'required' => false,
    ],
]);

// ============================================================================
// 2. Crear un Structurer (con soporte para warnings)
// ============================================================================

$structurer = new RuleBasedStructurer();

// ============================================================================
// 3. Procesar PDFs
// ============================================================================

$processor = ContentProcessor::make()
    ->withSchema($cvSchema)
    ->withExtractor(new PdfTextExtractor())
    ->withStructurer($structurer)
    ->fromFiles([
        __DIR__ . '/sample_cv.pdf',
    ]);

$results = $processor->process();

// ============================================================================
// 4. Mostrar resultados
// ============================================================================

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  BLOQUE 3: ESTRUCTURACIÓN SEMÁNTICA DE PDFs\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

echo "📊 RESUMEN DE PROCESAMIENTO\n";
echo "───────────────────────────────────────────────────────────────\n";
echo "Total de documentos:     {$results['total']}\n";
echo "Éxito:                   {$results['success']}\n";
echo "Fallos:                  {$results['failed']}\n";
echo "\n";

foreach ($results['results'] as $filePath => $result) {
    $fileName = basename($filePath);

    echo "📄 DOCUMENTO: $fileName\n";
    echo "───────────────────────────────────────────────────────────────\n";

    if (!$result['success']) {
        echo "❌ ERROR TÉCNICO: {$result['error']}\n";
        echo "\n";
        continue;
    }

    echo "✅ Procesado exitosamente\n";
    echo "\n";

    // Datos estructurados
    echo "📋 DATOS ESTRUCTURADOS:\n";
    echo json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    echo "\n";

    // Warnings semánticos
    if (!empty($result['warnings'])) {
        echo "⚠️  WARNINGS SEMÁNTICOS (" . count($result['warnings']) . "):\n";
        foreach ($result['warnings'] as $field => $warning) {
            echo "   • {$field}: {$warning}\n";
        }
        echo "\n";
    } else {
        echo "✓ Sin warnings - Datos de alta calidad\n";
        echo "\n";
    }
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "  END OF BLOQUE 3 EXAMPLE\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
