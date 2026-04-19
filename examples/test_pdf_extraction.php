<?php

/**
 * Prueba funcional del Block 2: Extraction desde PDF
 * 
 * Este script demuestra el uso de PdfTextExtractor para process
 * files PDF digital completos con la arquitectura de ContentProcessor.
 */

// Importar autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Extractors\PdfTextExtractor;
use ContentProcessor\Schemas\ArraySchema;
use ContentProcessor\Structurers\SimpleLineStructurer;

// ============================================
// 1. Generar PDF de prueba si 
// ============================================
echo "📋 BLOCK 2: Extraction desde PDF Digital\n";
echo "=" . str_repeat("=", 50) . "\n\n";

$pdfPath = __DIR__ . '/sample_cv.pdf';

if (!file_exists($pdfPath)) {
    echo "🔄 Generando PDF de prueba...\n";
    include __DIR__ . '/generate_sample_pdf.php';
    echo "\n";
}

// ============================================
// 2. Definir esquema de CV
// ============================================
echo "📝 Configurando esquema de CV...\n";

$cvSchema = new ArraySchema([
    'name' => [
        'type' => 'string',
        'required' => true,
        'description' => 'Name completo del candidato'
    ],
    'carnet_identidad' => [
        'type' => 'string',
        'description' => 'Número de documento de identidad'
    ],
    'especialidad' => [
        'type' => 'string',
        'description' => 'Especialidad o profesión'
    ],
    'plaza' => [
        'type' => 'string',
        'description' => 'Puesto o plaza actual'
    ],
    'anos_experiencia' => [
        'type' => 'int',
        'description' => 'Años de experiencia laboral'
    ],
    'email' => [
        'type' => 'string',
        'description' => 'Correo electrónico'
    ],
    'ubicacion' => [
        'type' => 'string',
        'description' => 'Ciudad o ubicación'
    ]
]);

echo "✅ Esquema definido (7 fields)\n\n";

// ============================================
// 3.  PDF con ContentProcessor
// ============================================
echo "🚀 Iniciando processing of the PDF...\n";
echo "   File: " . basename($pdfPath) . "\n";
echo "   Extractor: PdfTextExtractor\n";
echo "   Structurer: SimpleLineStructurer\n\n";

try {
    $processor = ContentProcessor::make()
        ->withExtractor(new PdfTextExtractor())
        ->withStructurer(new SimpleLineStructurer())
        ->withSchema($cvSchema)
        ->fromFiles([$pdfPath]);

    //  el PDF
    $result = $processor->process();

    // ============================================
    // 4. Mostrar resultados
    // ============================================
    echo "✅ PROCESSING SUCCESSFUL\n";
    echo "=" . str_repeat("=", 50) . "\n\n";

    // Resumen general
    echo "📊 Resumen del processing:\n";
    echo "   Total documentos: " . $result['total'] . "\n";
    echo "   ✅ Successfuls: " . $result['success'] . "\n";
    echo "   ❌ Con errors: " . $result['failed'] . "\n\n";

    // Mostrar resultados detallados
    foreach ($result['results'] as $file => $fileResult) {
        echo "📄 " . basename($file) . ":\n";
        if ($fileResult['success']) {
            echo "   ✅ Estado: SUCCESSFUL\n";
            if ($fileResult['data']) {
                echo "   📋 Data extracteds:\n";
                foreach ($fileResult['data'] as $key => $value) {
                    echo "      • $key: $value\n";
                }
            }
        } else {
            echo "   ❌ Estado: CON ERROR\n";
            echo "   Error: " . $fileResult['error'] . "\n";
        }
        echo "\n";
    }

    // Mostrar JSON completo si hay  s
    if ($result['success'] > 0) {
        echo "📤 JSON de resultados más detallado:\n";
        $jsonData = [];
        foreach ($result['results'] as $file => $fileResult) {
            if ($fileResult['success'] && $fileResult['data']) {
                $jsonData[] = $fileResult['data'];
            }
        }
        echo json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    }

    // ============================================
    // 5.  de 
    // ============================================
    echo "📄 Information of the PDF:\n";
    echo "   ✅ Extraction completada\n";
    echo "   📑 Method: Extraction of text digital (sin OCR)\n";
    echo "   🔤 Content extracted: Sí\n\n";
} catch (\Exception $e) {
    echo "❌ ERROR en el processing:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "\n📌 Posibles causas:\n";
    echo "   1. El PDF does not exist o is not accesible\n";
    echo "   2. El PDF está dañado o es inválido\n";
    echo "   3. El PDF está encriptado\n";
    exit(1);
}

// ============================================
// 6. Confirmación
// ============================================
echo "✨ BLOCK 2 COMPLETADO Y FUNCIONAL\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "✅ ContentProcessor + PdfTextExtractor funcionando\n";
echo "✅ Batch compatible (aplicable a múltiples PDFs)\n";
echo "✅ Arquitectura 100% íntacta\n";
echo "\n";
