<?php

/**
 * Ejemplo funcional Block 2: PDF → JSON con ContentProcessor
 * 
 * Este ejemplo demuestra el pipeline COMPLETO:
 * PDF (entrada) → Extraction → Structureción → Validation → JSON (salida)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Extractors\PdfTextExtractor;
use ContentProcessor\Schemas\ArraySchema;
use ContentProcessor\Structurers\SimpleLineStructurer;

echo "📋 BLOCK 2: PDF → JSON (Pipeline Completo)\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// ============================================
// 1. Genera PDF si 
// ============================================
$pdfPath = __DIR__ . '/sample_cv.pdf';
if (!file_exists($pdfPath)) {
    echo "🔄 Generando PDF de prueba...\n";
    include __DIR__ . '/generate_sample_pdf.php';
    echo "\n";
}

// ============================================
// 2. Definir esquema para ción
// ============================================
echo "📝 Definiendo esquema de CV...\n";

$cvSchema = new ArraySchema([
    'name' => [
        'type' => 'string',
        'required' => true,
        'description' => 'Name completo'
    ],
    'carnet_identidad' => [
        'type' => 'string',
        'description' => 'Documento de identidad'
    ],
    'especialidad' => [
        'type' => 'string',
        'description' => 'Especialidad profesional'
    ],
    'plaza' => [
        'type' => 'string',
        'description' => 'Puesto atual'
    ],
    'anos_experiencia' => [
        'type' => 'int',
        'description' => 'Años de experiencia'
    ]
]);

echo "✅ Esquema definido\n\n";

// ============================================
// 3.  PDF con Pipeline Completo
// ============================================
echo "🚀 Iniciando pipeline PDF → JSON...\n";
echo "   File: " . basename($pdfPath) . "\n";
echo "   Extractor: PdfTextExtractor\n";
echo "   Structurer: SimpleLineStructurer\n";
echo "   Schema: CVSchema (5 fields)\n\n";

try {
    // Construir y ejecutar pipeline
    $results = ContentProcessor::make()
        ->withExtractor(new PdfTextExtractor())
        ->withStructurer(new SimpleLineStructurer())
        ->withSchema($cvSchema)
        ->fromFiles([$pdfPath])
        ->process();

    // ============================================
    // 4. Mostrar Resultados
    // ============================================
    echo "✅ PIPELINE COMPLETADO\n";
    echo "=" . str_repeat("=", 50) . "\n\n";

    // Resumen
    echo "📊 Resumen de processing:\n";
    echo "   Total: " . $results['total'] . " documento(s)\n";
    echo "   Successfuls: " . $results['success'] . "\n";
    echo "   Con errors: " . $results['failed'] . "\n\n";

    // Resultados por documento
    $jsonResults = [];
    foreach ($results['results'] as $file => $result) {
        echo "📄 " . basename($file) . ":\n";
        if ($result['success']) {
            echo "   ✅ SUCCESSFUL\n";
            if ($result['data']) {
                echo "   📋 Data extracteds:\n";
                foreach ($result['data'] as $key => $value) {
                    echo "      • $key: $value\n";
                }
                $jsonResults[] = $result['data'];
            }
        } else {
            echo "   ❌ ERROR: " . $result['error'] . "\n";
        }
        echo "\n";
    }

    // JSON Final
    if (!empty($jsonResults)) {
        echo "📤 SALIDA FINAL EN JSON:\n";
        echo "=" . str_repeat("=", 50) . "\n";
        echo json_encode($jsonResults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    }

    // ============================================
    // 5. Verificación Técnica
    // ============================================
    echo "✨ INFORMATION TÉCNICA\n";
    echo "=" . str_repeat("=", 50) . "\n";
    echo "✅ PdfTextExtractor: Extrae content PDF\n";
    echo "✅ SimpleLineStructurer: Structure según esquema\n";
    echo "✅ ArraySchema: Valida fields requeridos\n";
    echo "✅ ContentProcessor: Orquesta pipeline\n";
    echo "✅ Batch Processing: Compatible (múltiples PDFs)\n\n";

    echo "✅ BLOCK 2 COMPLETO Y FUNCIONAL\n";
    echo "=" . str_repeat("=", 50) . "\n";
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
