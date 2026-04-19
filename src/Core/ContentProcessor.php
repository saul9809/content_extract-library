<?php

namespace ContentProcessor\Core;

use ContentProcessor\Contracts\ExtractorInterface;
use ContentProcessor\Contracts\StructurerInterface;
use ContentProcessor\Contracts\SemanticStructurerInterface;
use ContentProcessor\Contracts\SchemaInterface;
use ContentProcessor\Models\DocumentContext;
use ContentProcessor\Models\StructuredDocumentResult;
use ContentProcessor\Models\FinalResult;
use ContentProcessor\Models\Error;
use ContentProcessor\Models\Warning;
use ContentProcessor\Models\Summary;
use ContentProcessor\Security\SecurityValidator;
use ContentProcessor\Security\SecurityException;

/**
 * Processor de content principal.
 * 
 * Orquesta la extraction, limpieza y structureción de content
 * desde múltiples fuentes usando estrategias configurables.
 * 
 * Diseñado para batch processing: procesa múltiples documentos
 * de forma consistente y eficiente.
 * 
 * @example
 * $processor = ContentProcessor::make()
 *     ->withSchema($schema)
 *     ->fromDirectory('/path/to/docs')
 *     ->process();
 */
class ContentProcessor
{
    private ?SchemaInterface $schema = null;
    private ?ExtractorInterface $extractor = null;
    private ?StructurerInterface $structurer = null;
    private array $sources = [];
    private array $options = [];
    private array $results = [];

    /**
     * Constructor privado (usar make() para instanciar).
     */
    private function __construct()
    {
        $this->options = [
            'skip_invalid' => true,    // Saltar documentos inválidos
            'preserve_empty' => false, // Preservar  vacíos
        ];
    }

    /**
     * Factory method. Crea una nueva instancia del processor.
     * 
     * @return self
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * Define el esquema para structureción.
     * 
     * @param SchemaInterface $schema
     * @return $this
     */
    public function withSchema(SchemaInterface $schema): self
    {
        $this->schema = $schema;
        return $this;
    }

    /**
     * Define el extractor de content.
     * 
     * @param ExtractorInterface $extractor
     * @return $this
     */
    public function withExtractor(ExtractorInterface $extractor): self
    {
        $this->extractor = $extractor;
        return $this;
    }

    /**
     * Define el structurer de content.
     * 
     * @param StructurerInterface $structurer
     * @return $this
     */
    public function withStructurer(StructurerInterface $structurer): self
    {
        $this->structurer = $structurer;
        return $this;
    }

    /**
     * Añade múltiples files como fuentes.
     * 
     * BLOCK 5: Se valida que el batch no exceda el máximo permitido.
     * 
     * @param array $files Array de rutas a files
     * @return $this
     * @throws SecurityException Si el batch excede los límites
     */
    public function fromFiles(array $files): self
    {
        // Validar tamaño del batch (5 - Security)
        SecurityValidator::validateBatchSize($files);

        $this->sources = array_merge($this->sources, $files);
        return $this;
    }

    /**
     * Añade todos los files de un directorio como fuentes.
     * 
     * BLOCK 5: Se valida que el batch no exceda el máximo permitido.
     * 
     * @param string $directory Ruta del directorio
     * @param string $pattern Patrón de files (ej: '*.pdf')
     * @return $this
     * @throws SecurityException Si el batch excede los límites
     */
    public function fromDirectory(string $directory, string $pattern = '*'): self
    {
        if (!is_dir($directory)) {
            throw new \InvalidArgumentException("El directorio '$directory' does not exist.");
        }

        $files = glob(rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $pattern);
        $files = $files === false ? [] : $files;

        // Validar tamaño del batch (5 - Security)
        SecurityValidator::validateBatchSize($files);

        $this->sources = array_merge($this->sources, $files);
        return $this;
    }

    /**
     * Configura opciones del processor.
     * 
     * @param array $options Opciones a configurar
     * @return $this
     */
    public function withOptions(array $options): self
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * Procesa todas las fuentes configuradas.
     * 
     * Retorna un array con resultados de cada fuente procesada.
     * Structure: [
     *     'success' => int,
     *     'failed' => int,
     *     'total' => int,
     *     'results' => [
     *         'path/to/file' => ['success' => bool, 'data' => array, 'error' => string|null],
     *         ...
     *     ]
     * ]
     * 
     * @return array
     * @throws \RuntimeException Si se requiere schema pero is nottá configurado
     */
    public function process(): array
    {
        if (!$this->schema) {
            throw new \RuntimeException('Esquema requerido. Usa withSchema() primero.');
        }

        $this->results = [
            'success' => 0,
            'failed' => 0,
            'total' => count($this->sources),
            'results' => [],
        ];

        foreach ($this->sources as $source) {
            $this->processSource($source);
        }

        return $this->results;
    }

    /**
     * Procesa una fuente individual.
     * 
     * Soporta tanto Structurers tradicionales como SemanticStructurers.
     * Si el Structurer implementa SemanticStructurerInterface, se capturan warnings.
     * 
     * @param string $source
     * @return void
     */
    private function processSource(string $source): void
    {
        try {
            // Extrae 
            if (!$this->extractor) {
                throw new \RuntimeException('Extractor no configurado. Usa withExtractor() primero.');
            }

            if (!$this->extractor->canHandle($source)) {
                throw new \RuntimeException("El extractor '{$this->extractor->getName()}' cannot process '$source'.");
            }

            $content = $this->extractor->extract($source);

            //  el 
            if (!$this->structurer) {
                throw new \RuntimeException('Structurer no configurado. Usa withStructurer() primero.');
            }

            // Detecta si es un SemanticStructurer (Block 3) o un Structurer tradicional (1)
            if ($this->structurer instanceof SemanticStructurerInterface) {
                // 3: ción Semantic con warnings
                $documentName = basename($source);
                $context = new DocumentContext($source, $documentName, $content);
                $result = $this->structurer->structureWithContext($context, $this->schema);
                $structured = $result->getData();
                $warnings = $result->getWarnings();
            } else {
                // 1: ción tradicional (sin warnings)
                $structured = $this->structurer->structure($content, $this->schema);
                $warnings = [];
            }

            // Valida contra el esquema
            $validation = $this->schema->validate($structured);

            if (!$validation['valid']) {
                if ($this->options['skip_invalid']) {
                    $this->recordResult($source, false, null, 'Validation fallida: ' . implode(', ', $validation['errors']));
                    return;
                }
            }

            $this->recordResult($source, true, $structured, null, $warnings);
        } catch (SecurityException $se) {
            // 5: Manejo seguro de excepciones de security
            // Nunca exponemos detalles internos del filesystem o stack traces
            $this->recordResult($source, false, null, $se->getClientMessage());
        } catch (\Throwable $e) {
            // Otras excepciones (se mantiene compatible con comportamiento anterior)
            $this->recordResult($source, false, null, $e->getMessage());
        }
    }

    /**
     * Registra el resultado del processing de una fuente.
     * 
     * Block 1 y 2: registro simple (success, data, error)
     * Block 3: también captura warnings semánticos
     * 
     * @param string $source
     * @param bool $success
     * @param array|null $data
     * @param string|null $error
     * @param array $warnings Warnings del Block 3 (opcional)
     * @return void
     */
    private function recordResult(
        string $source,
        bool $success,
        ?array $data = null,
        ?string $error = null,
        array $warnings = []
    ): void {
        if ($success) {
            $this->results['success']++;
        } else {
            $this->results['failed']++;
        }

        $result = [
            'success' => $success,
            'data' => $data,
            'error' => $error,
        ];

        // 3: Si hay warnings, incluirlos
        if (!empty($warnings)) {
            $result['warnings'] = $warnings;
            $result['warnings_count'] = count($warnings);
        }

        $this->results['results'][$source] = $result;
    }

    /**
     * Retorna el último conjunto de resultados processeds.
     * 
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Retorna solo los data successfuls processeds.
     * 
     * Útil para batch export o carga masiva.
     * 
     * @return array Array de data structuredos successfuls
     */
    public function getSuccessfulData(): array
    {
        $data = [];
        foreach ($this->results['results'] as $result) {
            if ($result['success']) {
                $data[] = $result['data'];
            }
        }
        return $data;
    }

    /**
     * Procesa todas las fuentes y retorna un FinalResult robusto (Block 4).
     * 
     * Este method es la API recomendada para Block 4+.
     * Retorna un objeto FinalResult unificado con:
     * - Data structuredos successfuls
     * - Errors normalizados
     * - Warnings semánticos normalizados
     * - Estadísticas y métricas
     * 
     * @return FinalResult
     * @throws \RuntimeException Si se requiere schema pero is nottá configurado
     */
    public function processFinal(): FinalResult
    {
        if (!$this->schema) {
            throw new \RuntimeException('Esquema requerido. Usa withSchema() primero.');
        }

        $this->results = [
            'success' => 0,
            'failed' => 0,
            'total' => count($this->sources),
            'results' => [],
        ];

        $startTime = microtime(true);

        foreach ($this->sources as $source) {
            $this->processSource($source);
        }

        $processingTime = microtime(true) - $startTime;

        // Construye el FinalResult a partir de los resultados acumulados
        return $this->buildFinalResult($processingTime);
    }

    /**
     * Construye un objeto FinalResult a partir de los resultados acumulados.
     * 
     * Normaliza errors y warnings, y genera el resumen de estadísticas.
     * 
     * @param float $processingTime Tiempo total de processing
     * @return FinalResult
     */
    private function buildFinalResult(float $processingTime): FinalResult
    {
        $data = [];
        $errors = [];
        $warnings = [];
        $fullResults = [];

        foreach ($this->results['results'] as $source => $result) {
            // Registro completo para debugging
            $fullResults[] = array_merge(
                ['source' => $source],
                $result
            );

            if ($result['success']) {
                // Documentos s
                $data[] = [
                    'document' => basename($source),
                    'path' => $source,
                    'data' => $result['data'],
                ];

                // Warnings del 3
                if (!empty($result['warnings'] ?? [])) {
                    foreach ($result['warnings'] as $fieldWarning) {
                        $warnings[] = new Warning(
                            $fieldWarning['field'] ?? 'unknown',
                            $fieldWarning['category'] ?? 'unknown',
                            $fieldWarning['message'] ?? 'Unknown warning',
                            $fieldWarning['value'] ?? null
                        );
                    }
                }
            } else {
                // Documento con 
                $errorType = 'runtime';
                if (strpos($result['error'] ?? '', 'Validation fallida') !== false) {
                    $errorType = 'validation';
                } elseif (strpos($result['error'] ?? '', 'cannot process') !== false) {
                    $errorType = 'extraction';
                }

                $errors[] = new Error(
                    $errorType,
                    $result['error'] ?? 'Unknown error',
                    ['file' => basename($source), 'source' => $source]
                );
            }
        }

        // Crea el Summary
        $summary = new Summary(
            $this->results['total'],
            $this->results['success'],
            $this->results['failed'],
            count($warnings),
            count($errors),
            $processingTime
        );

        return new FinalResult(
            $data,
            $errors,
            $warnings,
            $summary,
            $fullResults
        );
    }
}
