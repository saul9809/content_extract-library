<?php

/**
 * Ejemplo: Structureción Semantic of PDFs (Block 3)
 * 
 * Demuestra el flujo completo:
 * 1. Extracta texto de múltiples PDFs (Block 2)
 * 2. Structure el texto con RuleBasedStructurer (Block 3)
 * 3. Genera JSON structuredo + warnings + errors técnicos
 * 4. Output con análisis de calidad
 * 
 * Uso:
 *     php examples/test_structuring.php
 * 
 * @since Block 3 - Structureción Semantic
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
// Este schema define la  esperada para un CV

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
// 3.  PDFs
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
echo "  BLOCK 3: STRUCTURECIÓN SEMANTIC DE PDFs\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

echo "📊 RESUMEN DE PROCESSING\n";
echo "───────────────────────────────────────────────────────────────\n";
echo "Total de documentos:     {$results['total']}\n";
echo "Éxito:                   {$results['success']}\n";
echo "Failures:                  {$results['failed']}\n";
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

    echo "✅ Processed successfully\n";
    echo "\n";

    //  dos
    echo "📋 DATA STRUCTUREDOS:\n";
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
        echo "✓ Sin warnings - Data de alta calidad\n";
        echo "\n";
    }
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "  END OF BLOCK 3 EXAMPLE\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
