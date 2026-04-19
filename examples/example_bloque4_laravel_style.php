<?php

/**
 * BLOQUE 4 - Ejemplo Laravel-style: Consumo práctico
 * 
 * Demuestra cómo consumir FinalResult en un contexto Laravel-like
 * o en APIs REST comunes.
 * 
 * Ejecutar: php examples/example_bloque4_laravel_style.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Schemas\ArraySchema;
use ContentProcessor\Extractors\TextFileExtractor;
use ContentProcessor\Structurers\RuleBasedStructurer;

echo "=== BLOQUE 4: Consumo Laravel-Style ===\n\n";

// Simulación de controlador Laravel
class DocumentProcessorController
{
    /**
     * Procesa documentos y retorna respuesta API.
     * 
     * Simula un endpoint REST típico en Laravel.
     */
    public function processBatch(array $files): array
    {
        try {
            $schema = new ArraySchema([
                'nombre' => ['type' => 'string', 'required' => true],
                'carnet_identidad' => ['type' => 'string', 'required' => false],
                'anos_experiencia' => ['type' => 'int', 'required' => false],
                'email' => ['type' => 'string', 'required' => false],
            ]);

            $result = ContentProcessor::make()
                ->withSchema($schema)
                ->withExtractor(new TextFileExtractor())
                ->withStructurer(new RuleBasedStructurer())
                ->fromFiles($files)
                ->processFinal();

            return $this->formatResponse($result);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Formatea respuesta API estilo Laravel (JSON).
     */
    private function formatResponse($result): array
    {
        $httpCode = $result->isSuccessful() ? 200 : 422;

        return [
            'success' => $result->isSuccessful(),
            'status_code' => $httpCode,
            'message' => $this->getStatusMessage($result),
            'data' => [
                'documents_processed' => $result->data(),
                'errors' => $result->errorsToArray(),
                'warnings' => $result->warningsToArray(),
                'metrics' => $result->summary()->toArray(),
            ],
        ];
    }

    /**
     * Mensaje de estado legible.
     */
    private function getStatusMessage($result): string
    {
        if ($result->isPerfect()) {
            return 'Todos los documentos procesados correctamente sin problemas';
        } elseif ($result->isSuccessful()) {
            $count = $result->getWarningCount();
            return "Procesamiento exitoso con {$count} warning(s) detectado(s)";
        } else {
            $count = $result->getErrorCount();
            return "Procesamiento con {$count} error(es) detectado(s)";
        }
    }

    /**
     * Carga archivos en base de datos (simulado).
     */
    public function saveToDB($result): array
    {
        // Solo guardamos documentos exitosos
        $saved = 0;

        foreach ($result->dataPure() as $data) {
            // Simular INSERT en BD
            $saved++;
            // En Laravel sería: Document::create($data);
        }

        return [
            'saved' => $saved,
            'skipped' => $result->getErrorCount(),
            'total' => $result->summary()->getTotalDocuments(),
        ];
    }
}

// DEMO
echo "📋 Simulando 3 documentos para procesar...\n\n";

// Crear archivos temporales
$tempDir = sys_get_temp_dir() . '/cp_demo_' . uniqid();
@mkdir($tempDir);

file_put_contents("$tempDir/doc1.txt", "Nombre: Alice\nAños de Experiencia: 3");
file_put_contents("$tempDir/doc2.txt", "Nombre: Bob\nCarnet de Identidad: 98765432");
file_put_contents("$tempDir/doc3.txt", "Años de Experiencia: 5");

// Usar controlador
$controller = new DocumentProcessorController();
$files = [
    "$tempDir/doc1.txt",
    "$tempDir/doc2.txt",
    "$tempDir/doc3.txt",
];

echo "1️⃣  Procesando batch...\n";
$apiResponse = $controller->processBatch($files);

echo "\n2️⃣  Respuesta API:\n";
echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

// Separar éxitos y fallos
$successFiles = [];
foreach ($apiResponse['data']['documents_processed'] as $doc) {
    $successFiles[] = $doc;
}

if (!empty($successFiles)) {
    echo "\n3️⃣  Guardando en BD...\n";
    // Simulación: construir FinalResult nuevamente para getSuccessfulData
    $sqlResult = [
        'success' => true,
        'saved' => count($successFiles),
        'message' => 'Datos guardados correctamente en BD',
    ];
    echo json_encode($sqlResult, JSON_PRETTY_PRINT) . "\n";
}

// Limpieza
foreach (glob("$tempDir/*") as $file) {
    @unlink($file);
}
@rmdir($tempDir);

echo "\n✨ ¡Ejemplo Laravel-style completado!\n";
