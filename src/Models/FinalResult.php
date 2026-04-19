<?php

namespace ContentProcessor\Models;

/**
 * Resultado final unificado del procesamiento batch.
 * 
 * Encapsula todos los resultados del procesamiento:
 * - Datos estructurados exitosos
 * - Errores técnicos normalizados
 * - Warnings semánticos normalizados
 * - Estadísticas y métricas
 * 
 * Proporciona una API uniforme y limpia para consumidores
 * (Laravel, PHP puro, APIs REST, etc.).
 * 
 * **Objetivo del Bloque 4:**
 * Proveer un resultado único, robusto y fácil de consumir.
 * 
 * @package ContentProcessor\Models
 * @since 1.3.0 (Bloque 4)
 */
class FinalResult
{
    /**
     * Array de datos estructurados exitosos.
     * Estructura: [
     *     [documentName => string, data => array],
     *     ...
     * ]
     * @var array
     */
    private array $data = [];

    /**
     * Array de instancias Error normalizadas.
     * @var Error[]
     */
    private array $errors = [];

    /**
     * Array de instancias Warning normalizadas.
     * @var Warning[]
     */
    private array $warnings = [];

    /**
     * Resumen de estadísticas.
     * @var Summary
     */
    private Summary $summary;

    /**
     * Detalles completos de cada documento procesado.
     * Matriz para debugging y auditoría.
     * @var array
     */
    private array $fullResults = [];

    /**
     * Constructor.
     * 
     * @param array $data Datos exitosos
     * @param Error[] $errors Errores normalizados
     * @param Warning[] $warnings Warnings normalizados
     * @param Summary $summary Resumen de estadísticas
     * @param array $fullResults Detalles completos
     */
    public function __construct(
        array $data = [],
        array $errors = [],
        array $warnings = [],
        ?Summary $summary = null,
        array $fullResults = []
    ) {
        $this->data = $data;
        $this->errors = $errors;
        $this->warnings = $warnings;
        $this->fullResults = $fullResults;

        // Si no hay Summary, crear una por defecto
        if ($summary === null) {
            $this->summary = new Summary(
                count($fullResults),
                count($data),
                count($errors),
                count($warnings)
            );
        } else {
            $this->summary = $summary;
        }
    }

    /**
     * Obtiene los datos estructurados exitosos.
     * 
     * Retorna solo los documentos que fueron procesados correctamente.
     * Formato: [
     *     ['document' => 'file1.pdf', 'data' => [...]],
     *     ['document' => 'file2.pdf', 'data' => [...]],
     *     ...
     * ]
     * 
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Obtiene solo los datos (sin metadata de documento).
     * 
     * Útil para carga masiva en BD o APIs.
     * 
     * @return array Array de arrays de datos puros
     */
    public function dataPure(): array
    {
        return array_map(fn($item) => $item['data'], $this->data);
    }

    /**
     * Obtiene los errores normalizados.
     * 
     * @return Error[]
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Obtiene los warnings normalizados.
     * 
     * @return Warning[]
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /**
     * Obtiene el resumen de estadísticas.
     * 
     * @return Summary
     */
    public function summary(): Summary
    {
        return $this->summary;
    }

    /**
     * Verifica si hay errores.
     * 
     * @return bool
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    /**
     * Verifica si hay warnings.
     * 
     * @return bool
     */
    public function hasWarnings(): bool
    {
        return count($this->warnings) > 0;
    }

    /**
     * Verifica si el procesamiento fue completamente exitoso.
     * 
     * (Sin errores, aunque puede haber warnings)
     * 
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return !$this->hasErrors();
    }

    /**
     * Verifica si el procesamiento fue perfecto.
     * 
     * (Sin errores ni warnings)
     * 
     * @return bool
     */
    public function isPerfect(): bool
    {
        return !$this->hasErrors() && !$this->hasWarnings();
    }

    /**
     * Obtiene el número de documentos procesados exitosamente.
     * 
     * @return int
     */
    public function getSuccessCount(): int
    {
        return count($this->data);
    }

    /**
     * Obtiene el número de documentos que fallaron.
     * 
     * @return int
     */
    public function getErrorCount(): int
    {
        return count($this->errors);
    }

    /**
     * Obtiene el número de warnings generados.
     * 
     * @return int
     */
    public function getWarningCount(): int
    {
        return count($this->warnings);
    }

    /**
     * Obtiene los detalles completos de todos los documentos.
     * 
     * Útil para debugging y auditoría.
     * Incluye información de cada documento: éxito, datos, errores, warnings.
     * 
     * @return array
     */
    public function fullResults(): array
    {
        return $this->fullResults;
    }

    /**
     * Obtiene errores filtrados por tipo.
     * 
     * @param string $type Tipo de error (ej: 'extraction', 'validation')
     * @return Error[]
     */
    public function errorsByType(string $type): array
    {
        return array_filter(
            $this->errors,
            fn(Error $e) => $e->getType() === $type
        );
    }

    /**
     * Obtiene warnings filtrados por campo.
     * 
     * @param string $field
     * @return Warning[]
     */
    public function warningsByField(string $field): array
    {
        return array_filter(
            $this->warnings,
            fn(Warning $w) => $w->getField() === $field
        );
    }

    /**
     * Obtiene warnings filtrados por categoría.
     * 
     * @param string $category (ej: 'missing', 'ambiguous')
     * @return Warning[]
     */
    public function warningsByCategory(string $category): array
    {
        return array_filter(
            $this->warnings,
            fn(Warning $w) => $w->getCategory() === $category
        );
    }

    /**
     * Convierte errores a array.
     * 
     * @return array Array de arrays
     */
    public function errorsToArray(): array
    {
        return array_map(fn(Error $e) => $e->toArray(), $this->errors);
    }

    /**
     * Convierte warnings a array.
     * 
     * @return array Array de arrays
     */
    public function warningsToArray(): array
    {
        return array_map(fn(Warning $w) => $w->toArray(), $this->warnings);
    }

    /**
     * Convierte todo el resultado a array.
     * 
     * Estructura útil para JSON.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'success' => $this->isSuccessful(),
            'perfect' => $this->isPerfect(),
            'data' => [
                'count' => count($this->data),
                'items' => $this->data,
            ],
            'errors' => [
                'count' => count($this->errors),
                'items' => $this->errorsToArray(),
            ],
            'warnings' => [
                'count' => count($this->warnings),
                'items' => $this->warningsToArray(),
            ],
            'summary' => $this->summary->toArray(),
        ];
    }

    /**
     * Convierte a JSON.
     * 
     * @param int $flags Flags para json_encode
     * @return string
     */
    public function toJSON(int $flags = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toArray(), $flags);
    }

    /**
     * Convierte a JSON con pretty-print (legible).
     * 
     * @return string
     */
    public function toJSONPretty(): string
    {
        return $this->toJSON(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Retorna un resumen legible.
     * 
     * @return string
     */
    public function __toString(): string
    {
        $status = $this->isPerfect() ? 'PERFECT' : ($this->isSuccessful() ? 'SUCCESS' : 'FAILED');
        return "FinalResult [{$status}] " . $this->summary->getSummaryString();
    }
}
