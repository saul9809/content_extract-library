<?php

namespace ContentProcessor\Core;

use ContentProcessor\Contracts\ExtractorInterface;
use ContentProcessor\Contracts\StructurerInterface;
use ContentProcessor\Contracts\SchemaInterface;

/**
 * Procesador de contenido principal.
 * 
 * Orquesta la extracción, limpieza y estructuración de contenido
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
            'preserve_empty' => false, // Preservar campos vacíos
        ];
    }

    /**
     * Factory method. Crea una nueva instancia del procesador.
     * 
     * @return self
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * Define el esquema para estructuración.
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
     * Define el extractor de contenido.
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
     * Define el estructurador de contenido.
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
     * Añade múltiples archivos como fuentes.
     * 
     * @param array $files Array de rutas a archivos
     * @return $this
     */
    public function fromFiles(array $files): self
    {
        $this->sources = array_merge($this->sources, $files);
        return $this;
    }

    /**
     * Añade todos los archivos de un directorio como fuentes.
     * 
     * @param string $directory Ruta del directorio
     * @param string $pattern Patrón de archivos (ej: '*.pdf')
     * @return $this
     */
    public function fromDirectory(string $directory, string $pattern = '*'): self
    {
        if (!is_dir($directory)) {
            throw new \InvalidArgumentException("El directorio '$directory' no existe.");
        }

        $files = glob(rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $pattern);
        $files = $files === false ? [] : $files;

        $this->sources = array_merge($this->sources, $files);
        return $this;
    }

    /**
     * Configura opciones del procesador.
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
     * Estructura: [
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
     * @throws \RuntimeException Si se requiere schema pero no está configurado
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
     * @param string $source
     * @return void
     */
    private function processSource(string $source): void
    {
        try {
            // Extrae contenido
            if (!$this->extractor) {
                throw new \RuntimeException('Extractor no configurado. Usa withExtractor() primero.');
            }

            if (!$this->extractor->canHandle($source)) {
                throw new \RuntimeException("El extractor '{$this->extractor->getName()}' no puede procesar '$source'.");
            }

            $content = $this->extractor->extract($source);

            // Estructura el contenido
            if (!$this->structurer) {
                throw new \RuntimeException('Structurer no configurado. Usa withStructurer() primero.');
            }

            $structured = $this->structurer->structure($content, $this->schema);

            // Valida contra el esquema
            $validation = $this->schema->validate($structured);

            if (!$validation['valid']) {
                if ($this->options['skip_invalid']) {
                    $this->recordResult($source, false, null, 'Validación fallida: ' . implode(', ', $validation['errors']));
                    return;
                }
            }

            $this->recordResult($source, true, $structured);
        } catch (\Throwable $e) {
            $this->recordResult($source, false, null, $e->getMessage());
        }
    }

    /**
     * Registra el resultado del procesamiento de una fuente.
     * 
     * @param string $source
     * @param bool $success
     * @param array|null $data
     * @param string|null $error
     * @return void
     */
    private function recordResult(string $source, bool $success, ?array $data = null, ?string $error = null): void
    {
        if ($success) {
            $this->results['success']++;
        } else {
            $this->results['failed']++;
        }

        $this->results['results'][$source] = [
            'success' => $success,
            'data' => $data,
            'error' => $error,
        ];
    }

    /**
     * Retorna el último conjunto de resultados procesados.
     * 
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Retorna solo los datos exitosos procesados.
     * 
     * Útil para batch export o carga masiva.
     * 
     * @return array Array de datos estructurados exitosos
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
}
