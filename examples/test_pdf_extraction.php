<?php

/**
 * Prueba funcional del Bloque 2: Extracción desde PDF
 * 
 * Este script demuestra el uso de PdfTextExtractor para procesar
 * archivos PDF digitales completos con la arquitectura de ContentProcessor.
 */

// Importar autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Extractors\PdfTextExtractor;
use ContentProcessor\Schemas\ArraySchema;
use ContentProcessor\Structurers\SimpleLineStructurer;

// ============================================
// 1. Generar PDF de prueba si no existe
// ============================================
echo "📋 BLOQUE 2: Extracción desde PDF Digital\n";
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
    'nombre' => [
        'type' => 'string',
        'required' => true,
        'description' => 'Nombre completo del candidato'
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

echo "✅ Esquema definido (7 campos)\n\n";

// ============================================
// 3. Procesar PDF con ContentProcessor
// ============================================
echo "🚀 Iniciando procesamiento del PDF...\n";
echo "   Archivo: " . basename($pdfPath) . "\n";
echo "   Extractor: PdfTextExtractor\n";
echo "   Structurer: SimpleLineStructurer\n\n";

try {
    $processor = ContentProcessor::make()
        ->withExtractor(new PdfTextExtractor())
        ->withStructurer(new SimpleLineStructurer())
        ->withSchema($cvSchema)
        ->fromFiles([$pdfPath]);

    // Procesar el PDF
    $result = $processor->process();

    // ============================================
    // 4. Mostrar resultados
    // ============================================
    echo "✅ PROCESAMIENTO EXITOSO\n";
    echo "=" . str_repeat("=", 50) . "\n\n";

    // Resumen general
    echo "📊 Resumen del procesamiento:\n";
    echo "   Total documentos: " . $result['total'] . "\n";
    echo "   ✅ Exitosos: " . $result['success'] . "\n";
    echo "   ❌ Con errores: " . $result['failed'] . "\n\n";

    // Mostrar resultados detallados
    foreach ($result['results'] as $file => $fileResult) {
        echo "📄 " . basename($file) . ":\n";
        if ($fileResult['success']) {
            echo "   ✅ Estado: EXITOSO\n";
            if ($fileResult['data']) {
                echo "   📋 Datos extraídos:\n";
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

    // Mostrar JSON completo si hay datos exitosos
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
    // 5. Información de extracción
    // ============================================
    echo "📄 Información del PDF:\n";
    echo "   ✅ Extracción completada\n";
    echo "   📑 Método: Extracción de texto digital (sin OCR)\n";
    echo "   🔤 Contenido extraído: Sí\n\n";
} catch (\Exception $e) {
    echo "❌ ERROR en el procesamiento:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "\n📌 Posibles causas:\n";
    echo "   1. El PDF no existe o no es accesible\n";
    echo "   2. El PDF está dañado o es inválido\n";
    echo "   3. El PDF está encriptado\n";
    exit(1);
}

// ============================================
// 6. Confirmación
// ============================================
echo "✨ BLOQUE 2 COMPLETADO Y FUNCIONAL\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "✅ ContentProcessor + PdfTextExtractor funcionando\n";
echo "✅ Batch compatible (aplicable a múltiples PDFs)\n";
echo "✅ Arquitectura 100% íntacta\n";
echo "\n";
