<?php

/**
 * Ejemplo avanzado: Multiple PDFs con warnings semánticos
 * 
 * Demuestra:
 * 1. Procesamiento batch de múltiples PDFs
 * 2. Generación de warnings (campos ambiguos, ausentes)
 * 3. Separación clara entre errores técnicos y warnings semánticos
 * 4. Exportación de resultados estructurados
 * 
 * Uso:
 *     php examples/test_structuring_advanced.php
 * 
 * @since Bloque 3
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../autoload_manual.php';

use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Extractors\PdfTextExtractor;
use ContentProcessor\Structurers\RuleBasedStructurer;
use ContentProcessor\Schemas\ArraySchema;

// ============================================================================
// 1. Crear PDFs con contenido estructurado para demostración
// ============================================================================

function createTestPdf($filename, $content)
{
    $pdf = "%PDF-1.1\n";
    
    $obj1 = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $obj2 = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    $obj4 = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
    
    $streamCmd = "BT\n/F1 12 Tf\n50 750 Td\n(" . addslashes($content) . ") Tj\nET\n";
    $streamLen = strlen($streamCmd);
    $obj5 = "5 0 obj\n<< /Length $streamLen >>\nstream\n" . $streamCmd . "endstream\nendobj\n";
    
    $obj3 = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 5 0 R /Resources << /Font << /F1 4 0 R >> >> >>\nendobj\n";
    
    $pos = strlen($pdf);
    $offset1 = $pos;
    $pdf .= $obj1;
    
    $pos = strlen($pdf);
    $offset2 = $pos;
    $pdf .= $obj2;
    
    $pos = strlen($pdf);
    $offset3 = $pos;
    $pdf .= $obj3;
    
    $pos = strlen($pdf);
    $offset4 = $pos;
    $pdf .= $obj4;
    
    $pos = strlen($pdf);
    $offset5 = $pos;
    $pdf .= $obj5;
    
    $xref_pos = strlen($pdf);
    $pdf .= "xref\n0 6\n0000000000 65535 f \n";
    $pdf .= sprintf("%010d 00000 n \n", $offset1);
    $pdf .= sprintf("%010d 00000 n \n", $offset2);
    $pdf .= sprintf("%010d 00000 n \n", $offset3);
    $pdf .= sprintf("%010d 00000 n \n", $offset4);
    $pdf .= sprintf("%010d 00000 n \n", $offset5);
    
    $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n$xref_pos\n%%EOF\n";
    
    file_put_contents($filename, $pdf);
}

// Crear PDFs de prueba
$tmpDir = __DIR__ . '/tmp';
@mkdir($tmpDir);

// PDF 1: Completo (por contrastarlo, en teoría tiene todos los campos requeridos)
createTestPdf("$tmpDir/cv_completo.pdf", 
    "name: Maria Garcia\nemail: maria@example.com\nphone: 555-1234\n" .
    "experience_years: 5\nskills: PHP, Java, SQL\neducation: CS Degree"
);

// PDF 2: Incompleto (falta email requerido)
createTestPdf("$tmpDir/cv_incompleto.pdf",
    "name: Carlos Lopez\nphone: 555-5678\nexperience_years: 3"
);

// ============================================================================
// 2. Definir Schema
// ============================================================================

$cvSchema = new ArraySchema([
    'name' => ['type' => 'string', 'required' => true],
    'email' => ['type' => 'string', 'required' => true],
    'phone' => ['type' => 'string', 'required' => false],
    'experience_years' => ['type' => 'integer', 'required' => false],
    'skills' => ['type' => 'array', 'required' => false],
    'education' => ['type' => 'string', 'required' => false],
]);

// ============================================================================
// 3. Procesar batch
// ============================================================================

$processor = ContentProcessor::make()
    ->withSchema($cvSchema)
    ->withExtractor(new PdfTextExtractor())
    ->withStructurer(new RuleBasedStructurer())
    ->fromDirectory($tmpDir, '*.pdf');

$results = $processor->process();

// ============================================================================
// 4. Mostrar resultados con análisis detallado
// ============================================================================

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║        BLOQUE 3: ESTRUCTURACIÓN SEMÁNTICA AVANZADA             ║\n";
echo "║     Batch Processing con Warnings y Análisis de Calidad        ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Estadísticas generales
echo "📊 ESTADÍSTICAS BATCH\n";
echo "───────────────────────────────────────────────────────────────\n";
echo "Total documentos:    {$results['total']}\n";
echo "Exitosos:            {$results['success']}\n";
echo "Con fallos técnicos:  {$results['failed']}\n";
echo "\n";

// Detalles por documento
$totalWarnings = 0;
$docsWithWarnings = 0;

foreach ($results['results'] as $filePath => $result) {
    $fileName = basename($filePath);
    
    echo "─────────────────────────────────────────────────────────────\n";
    echo "📄 {$fileName}\n";
    echo "─────────────────────────────────────────────────────────────\n";
    
    if (!$result['success']) {
        echo "❌ ERROR TÉCNICO\n";
        echo "   {$result['error']}\n";
        echo "\n";
        continue;
    }
    
    // Datos
    echo "✅ PROCESADO EXITOSAMENTE\n";
    echo "📝 DATOS EXTRAÍDOS:\n";
    $data = $result['data'];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $val = json_encode($value);
        } else {
            $val = (string)$value;
        }
        echo "   • {$key}: {$val}\n";
    }
    echo "\n";
    
    // Warnings
    if (isset($result['warnings']) && !empty($result['warnings'])) {
        $warningCount = $result['warnings_count'] ?? count($result['warnings']);
        echo "⚠️  WARNINGS SEMÁNTICOS ({$warningCount})\n";
        foreach ($result['warnings'] as $field => $message) {
            echo "   ⚠ {$field}: {$message}\n";
        }
        $totalWarnings += $warningCount;
        $docsWithWarnings++;
        echo "\n";
    } else {
        echo "✓ SIN WARNINGS - Datos de Excelente Calidad\n";
        echo "\n";
    }
    
    // Calidad
    $quality = 100;
    if (isset($result['warnings'])) {
        $quality -= count($result['warnings']) * 15;
        $quality = max(0, $quality);
    }
    echo "📈 CALIDAD: " . str_repeat("█", $quality / 10) . str_repeat("░", (100 - $quality) / 10) . " {$quality}%\n";
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "📌 RESUMEN FINAL\n";
echo "───────────────────────────────────────────────────────────────\n";
echo "Documentos con warnings: {$docsWithWarnings}\n";
echo "Total warnings generados: {$totalWarnings}\n";
echo "Tasa de éxito: " . ($results['total'] > 0 ? round(($results['success'] / $results['total']) * 100) : 0) . "%\n";
echo "\n";

echo "✅ BLOQUE 3 COMPLETADO\n";
echo "\n";

// Limpiar
array_map('unlink', glob("$tmpDir/*.pdf"));
@rmdir($tmpDir);
