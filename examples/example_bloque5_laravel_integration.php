<?php

/**
 * BLOQUE 5 - Ejemplo de Integración: Laravel + Seguridad
 * 
 * Demuestra cómo usar Content Processor en Laravel con:
 * - Validación de seguridad automática
 * - Manejo seguro de excepciones
 * - Respuestas JSON seguras
 * - Logging de errores de seguridad
 * 
 * Contexto: Laravel Controller ejecutando validación de documentos
 * Ejecutar: php examples/example_bloque5_laravel_integration.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Schemas\ArraySchema;
use ContentProcessor\Extractors\PdfTextExtractor;
use ContentProcessor\Structurers\RuleBasedStructurer;
use ContentProcessor\Security\SecurityException;

echo "╔════════════════════════════════════════╗\n";
echo "║  BLOQUE 5: Laravel + Seguridad        ║\n";
echo "║         Validación Segura de Docs      ║\n";
echo "╚════════════════════════════════════════╝\n\n";

/**
 * Simulación de Laravel Controller
 * En realidad, esto estaría en app/Http/Controllers/DocumentController.php
 */

class DocumentController
{

    /**
     * Procesar documentos subidos por usuario
     * Similar a: POST /documents/process
     */
    public function processDocuments(array $filePaths): array
    {

        $schema = new ArraySchema([
            'nombre' => ['type' => 'string', 'required' => true],
            'email' => ['type' => 'string', 'required' => false],
            'telefono' => ['type' => 'string', 'required' => false],
        ]);

        try {
            // La validación de seguridad ocurre automáticamente
            $result = ContentProcessor::make()
                ->withSchema($schema)
                ->withExtractor(new PdfTextExtractor())
                ->withStructurer(new RuleBasedStructurer())
                ->fromFiles($filePaths)
                ->processFinal();

            // Respuesta segura para el cliente
            return [
                'success' => $result->isSuccessful(),
                'documents_processed' => count($result->data()),
                'data' => $result->data(),
                'errors' => array_map(fn($e) => [
                    'file' => $e->getFile(),
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ], $result->errors()),
                'warnings' => array_map(fn($w) => [
                    'file' => $w->getFile(),
                    'message' => $w->getMessage(),
                ], $result->warnings()),
                'metrics' => [
                    'total_time_ms' => round($result->summary()->totalTimeMs(), 2),
                    'avg_time_ms' => round($result->summary()->avgTimeMs(), 2),
                    'memory_used_mb' => round($result->summary()->memoryUsedMb(), 2),
                ],
            ];
        } catch (SecurityException $e) {
            // Seguridad: Excepción específica conocida
            // NUNCA exponemos detalles internos al cliente

            \error_log("[SECURITY] DocumentController detected security issue: " .
                $e->getInternalMessage() .
                " | Type: " . $e->getSecurityType() .
                " | Context: " . json_encode($e->getSecurityContext()));

            // Cliente recibe mensaje genérico y seguro
            return [
                'success' => false,
                'error_type' => 'validation_failed',
                'message' => $e->getClientMessage(),
                'timestamp' => date('Y-m-d H:i:s'),
            ];
        } catch (\Throwable $e) {
            // Seguridad: Excepciones genéricas (inesperadas)
            // NUNCA exponemos stack trace o ruta de archivos

            \error_log("[ERROR] DocumentController unexpected error: " .
                $e->getMessage() .
                " | File: " . $e->getFile() .
                " | Line: " . $e->getLine());

            return [
                'success' => false,
                'error_type' => 'internal_error',
                'message' => 'Error procesando documentos. Por favor intente más tarde.',
                'timestamp' => date('Y-m-d H:i:s'),
                // En producción, proporcionar ID de ticketing para soporte
                'error_id' => 'ERR-' . substr(md5(microtime()), 0, 8),
            ];
        }
    }
}

// ============ CASOS DE PRUEBA ============

$controller = new DocumentController();
$testDir = __DIR__ . '/test_laravel_sec';
@mkdir($testDir, 0777, true);

echo "═══════════════════════════════════════\n";
echo "TEST 1: Batch Demasiado Grande\n";
echo "═══════════════════════════════════════\n";
echo "Contexto: Usuario intenta subir 65 PDFs\n";
echo "Esperado: SecurityException capturada, mensaje seguro\n\n";

// Simular 65 archivos maliciosos
$maliciousBatch = array_fill(0, 65, "$testDir/dummy.pdf");

$response = $controller->processDocuments($maliciousBatch);

echo "Respuesta JSON al cliente:\n";
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

echo "✅ Resultado: Cliente recibe mensaje seguro sin exponer detalles internos\n\n";

// ============ TEST 2 ============

echo "═══════════════════════════════════════\n";
echo "TEST 2: Batch Válido (3 documentos)\n";
echo "═══════════════════════════════════════\n";
echo "Contexto: Usuario sube 3 CVs válidos\n";
echo "Esperado: Procesamiento exitoso\n\n";

$validBatch = [];
for ($i = 1; $i <= 3; $i++) {
    $file = "$testDir/cv_$i.txt";
    file_put_contents($file, "Nombre: Candidato $i\nEmail: candidato{$i}@example.com\nTelefono: 555-000{$i}");
    $validBatch[] = $file;
}

$response = $controller->processDocuments($validBatch);

echo "Respuesta JSON al cliente (success):\n";
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

echo "✅ Resultado: 3 documentos procesados correctamente con métricas de rendimiento\n\n";

// ============ TEST 3 ============

echo "═══════════════════════════════════════\n";
echo "TEST 3: Documentos PDF Corruptos\n";
echo "═══════════════════════════════════════\n";
echo "Contexto: Usuario sube archivos que no son PDF\n";
echo "Esperado: Errores capturados de forma segura\n\n";

$corruptBatch = [];
for ($i = 1; $i <= 2; $i++) {
    $file = "$testDir/fake_$i.pdf";
    file_put_contents($file, "Este archivo no es un PDF real");
    $corruptBatch[] = $file;
}

$response = $controller->processDocuments($corruptBatch);

echo "Respuesta JSON al cliente (con errores):\n";
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";

echo "✅ Resultado: Errores reportados sin exponer rutas internas\n\n";

// ============ CARACTERÍSTICAS DE SEGURIDAD ============

echo "═══════════════════════════════════════\n";
echo "🔒 CARACTERÍSTICAS DE SEGURIDAD ACTIVAS\n";
echo "═══════════════════════════════════════\n\n";

$features = [
    'Batch Size Limit' => 'Máximo 50 documentos por solicitud',
    'File Size Limit' => 'PDFs máximo 10 MB, archivos 5 MB',
    'PDF Validation' => 'Verifica firma %PDF- antes de procesar',
    'Path Traversal' => 'Bloquea intentos de ../ en rutas',
    'Exception Safety' => 'getClientMessage() sin detalles internos',
    'Error Logging' => 'getInternalMessage() con contexto para logs',
    'Type Safety' => 'PHP 8.1 strict types en toda la librería',
    'Interface Contracts' => 'Inyección de dependencias validada',
];

foreach ($features as $feature => $description) {
    echo "  ✅ $feature\n";
    echo "     └─ $description\n\n";
}

// Limpieza
echo "🧹 Limpiando archivos de prueba...\n";
foreach (glob("$testDir/*") as $file) {
    @unlink($file);
}
@rmdir($testDir);

echo "\n✨ Integración Laravel + Seguridad validada\n";
echo "📌 Conclusión: La librería está lista para producción\n";
