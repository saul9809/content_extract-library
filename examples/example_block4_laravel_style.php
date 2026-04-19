<?php

/**
 * BLOCK 4 - Ejemplo Laravel-style: Consumo práctico
 * 
 * Demuestra cómo consumir FinalResult en un context Laravel-like
 * o en APIs REST comunes.
 * 
 * Ejecutar: php examples/example_block4_laravel_style.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ContentProcessor\Core\ContentProcessor;
use ContentProcessor\Schemas\ArraySchema;
use ContentProcessor\Extractors\TextFileExtractor;
use ContentProcessor\Structurers\RuleBasedStructurer;

echo "=== BLOCK 4: Consumo Laravel-Style ===\n\n";

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
                'name' => ['type' => 'string', 'required' => true],
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
     * Mensaje de estado readable.
     */
    private function getStatusMessage($result): string
    {
        if ($result->isPerfect()) {
            return 'Todos los documentos processeds correctamente sin problemas';
        } elseif ($result->isSuccessful()) {
            $count = $result->getWarningCount();
            return "Processing successful con {$count} warning(s) detectado(s)";
        } else {
            $count = $result->getErrorCount();
            return "Processing con {$count} error(es) detectado(s)";
        }
    }

    /**
     * Carga files en base de data (simulado).
     */
    public function saveToDB($result): array
    {
        // Solo guardamos documentos s
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
echo "📋 Simulando 3 documentos para process...\n\n";

// Crear  temporales
$tempDir = sys_get_temp_dir() . '/cp_demo_' . uniqid();
@mkdir($tempDir);

file_put_contents("$tempDir/doc1.txt", "Name: Alice\nAños de Experiencia: 3");
file_put_contents("$tempDir/doc2.txt", "Name: Bob\nCarnet de Identidad: 98765432");
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

// Separar éxitos y 
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
        'message' => 'Data guardados correctamente en BD',
    ];
    echo json_encode($sqlResult, JSON_PRETTY_PRINT) . "\n";
}

// Limpieza
foreach (glob("$tempDir/*") as $file) {
    @unlink($file);
}
@rmdir($tempDir);

echo "\n✨ ¡Ejemplo Laravel-style completado!\n";
