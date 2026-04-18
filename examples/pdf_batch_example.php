<?php

/**
 * Ejemplo Bloque 2: PDF → Texto → Procesamiento
 * 
 * Demuestra:
 * 1. Extracción de texto desde PDF
 * 2. Procesamiento con ContentProcessor
 * 3. Salida JSON estructurada
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ContentProcessor\Extractors\PdfTextExtractor;
use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Structurers\SimpleLineStructurer;
use ContentProcessor\Schemas\ArraySchema;

echo "📋 BLOQUE 2: Extracción PDF → JSON\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// PDF de prueba
$pdfPath = __DIR__ . '/sample_cv.pdf';
if (!file_exists($pdfPath)) {
    echo "🔄 Generando PDF de prueba...\n";
    include __DIR__ . '/generate_sample_pdf.php';
    echo "\n";
}

// ============================================
// PASO 1: Extracción de Texto (PdfTextExtractor)
// ============================================
echo "📖 PASO 1: Extracción de Texto desde PDF\n";
echo "─" . str_repeat("─", 50) . "\n";

$extractor = new PdfTextExtractor();

if (!$extractor->canHandle($pdfPath)) {
    echo "❌ No puede procesarse el archivo\n";
    exit(1);
}

$textContent = $extractor->extract($pdfPath);
echo "✅ Texto extraído del PDF:\n";
foreach ($textContent as $i => $text) {
    echo "\n[Sección " . ($i + 1) . "]:\n";
    echo $text . "\n";
}

// ============================================
// PASO 2: Procesamiento con ContentProcessor (Batch)
// ============================================
echo "\n📋 PASO 2: Procesamiento en Batch\n";
echo "─" . str_repeat("─", 50) . "\n";

// Schema simple sin campos requeridos
$schema = new ArraySchema([
    'contenido' => ['type' => 'string']
]);

$results = ContentProcessor::make()
    ->withExtractor(new PdfTextExtractor())
    ->withStructurer(new SimpleLineStructurer())
    ->withSchema($schema)
    ->fromFiles([$pdfPath])
    ->process();

echo "✅ Procesamiento completado:\n";
echo "   Total: " . $results['total'] . "\n";
echo "   Exitosos: " . $results['success'] . "\n";
echo "   Errores: " . $results['failed'] . "\n";

// ============================================
// PASO 3: JSON de Salida
// ============================================
echo "\n📤 PASO 3: Salida JSON\n";
echo "─" . str_repeat("─", 50) . "\n";

$output = [];
foreach ($results['results'] as $file => $result) {
    if ($result['success'] && $result['data']) {
        $output[] = [
            'archivo' => basename($file),
            'procesado' => true,
            'datos' => $result['data']
        ];
    } else {
        $output[] = [
            'archivo' => basename($file),
            'procesado' => false,
            'error' => $result['error']
        ];
    }
}

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// ============================================
// Confirmación
// ============================================
echo "✅ BLOQUE 2: PDF → JSON COMPLETADO\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "✅ PdfTextExtractor funcionando\n";
echo "✅ Batch processing completo\n";
echo "✅ JSON generado correctamente\n";
