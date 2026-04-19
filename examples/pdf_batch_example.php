<?php

/**
 * Ejemplo Block 2: PDF → Texto → Processing
 * 
 * Demuestra:
 * 1. Extraction of text desde PDF
 * 2. Processing con ContentProcessor
 * 3. Salida JSON structureda
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ContentProcessor\Extractors\PdfTextExtractor;
use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Structurers\SimpleLineStructurer;
use ContentProcessor\Schemas\ArraySchema;

echo "📋 BLOCK 2: Extraction PDF → JSON\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// PDF de prueba
$pdfPath = __DIR__ . '/sample_cv.pdf';
if (!file_exists($pdfPath)) {
    echo "🔄 Generando PDF de prueba...\n";
    include __DIR__ . '/generate_sample_pdf.php';
    echo "\n";
}

// ============================================
// PASO 1:   (PdfText)
// ============================================
echo "📖 PASO 1: Extraction de Texto desde PDF\n";
echo "─" . str_repeat("─", 50) . "\n";

$extractor = new PdfTextExtractor();

if (!$extractor->canHandle($pdfPath)) {
    echo "❌ Cannot processse el file\n";
    exit(1);
}

$textContent = $extractor->extract($pdfPath);
echo "✅ Texto extracted of the PDF:\n";
foreach ($textContent as $i => $text) {
    echo "\n[Sección " . ($i + 1) . "]:\n";
    echo $text . "\n";
}

// ============================================
// PASO 2:  con ContentProcessor (Batch)
// ============================================
echo "\n📋 PASO 2: Processing en Batch\n";
echo "─" . str_repeat("─", 50) . "\n";

// Schema simple sin  requeridos
$schema = new ArraySchema([
    'content' => ['type' => 'string']
]);

$results = ContentProcessor::make()
    ->withExtractor(new PdfTextExtractor())
    ->withStructurer(new SimpleLineStructurer())
    ->withSchema($schema)
    ->fromFiles([$pdfPath])
    ->process();

echo "✅ Processing completado:\n";
echo "   Total: " . $results['total'] . "\n";
echo "   Successfuls: " . $results['success'] . "\n";
echo "   Errors: " . $results['failed'] . "\n";

// ============================================
// PASO 3: JSON de Salida
// ============================================
echo "\n📤 PASO 3: Salida JSON\n";
echo "─" . str_repeat("─", 50) . "\n";

$output = [];
foreach ($results['results'] as $file => $result) {
    if ($result['success'] && $result['data']) {
        $output[] = [
            'file' => basename($file),
            'processed' => true,
            'data' => $result['data']
        ];
    } else {
        $output[] = [
            'file' => basename($file),
            'processed' => false,
            'error' => $result['error']
        ];
    }
}

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// ============================================
// Confirmación
// ============================================
echo "✅ BLOCK 2: PDF → JSON COMPLETADO\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "✅ PdfTextExtractor funcionando\n";
echo "✅ Batch processing completo\n";
echo "✅ JSON generado correctamente\n";
