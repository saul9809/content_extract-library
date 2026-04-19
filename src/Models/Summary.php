<?php

namespace ContentProcessor\Models;

/**
 * Resumen de estadísticas de procesamiento batch.
 * 
 * Proporciona métricas agregadas sobre la ejecución
 * del procesamiento masivo de documentos.
 * 
 * Útil para:
 * - Reportes de calidad
 * - Monitoreo de batch jobs
 * - Análisis de tasa de éxito
 * - Debugging de problemas comunes
 * 
 * @package ContentProcessor\Models
 * @since 1.3.0 (Bloque 4)
 */
class Summary
{
    /**
     * Total de documentos procesados.
     * @var int
     */
    private int $totalDocuments;

    /**
     * Documentos procesados exitosamente.
     * @var int
     */
    private int $successfulDocuments;

    /**
     * Documentos que fallaron.
     * @var int
     */
    private int $failedDocuments;

    /**
     * Total de warnings generados.
     * @var int
     */
    private int $totalWarnings;

    /**
     * Total de errores generados.
     * @var int
     */
    private int $totalErrors;

    /**
     * Tiempo total de procesamiento en segundos.
     * @var float
     */
    private float $processingTime;

    /**
     * Timestamp de inicio.
     * @var int
     */
    private int $startedAt;

    /**
     * Timestamp de finalización.
     * @var int
     */
    private int $finishedAt;

    /**
     * Constructor.
     * 
     * @param int $total
     * @param int $successful
     * @param int $failed
     * @param int $warnings
     * @param int $errors
     * @param float $processingTime
     * @param int $startedAt
     * @param int $finishedAt
     */
    public function __construct(
        int $total,
        int $successful,
        int $failed,
        int $warnings = 0,
        int $errors = 0,
        float $processingTime = 0.0,
        int $startedAt = 0,
        int $finishedAt = 0
    ) {
        $this->totalDocuments = $total;
        $this->successfulDocuments = $successful;
        $this->failedDocuments = $failed;
        $this->totalWarnings = $warnings;
        $this->totalErrors = $errors;
        $this->processingTime = $processingTime;
        $this->startedAt = $startedAt ?: time();
        $this->finishedAt = $finishedAt ?: time();
    }

    /**
     * Obtiene el total de documentos.
     * @return int
     */
    public function getTotalDocuments(): int
    {
        return $this->totalDocuments;
    }

    /**
     * Obtiene el total de documentos exitosos.
     * @return int
     */
    public function getSuccessfulDocuments(): int
    {
        return $this->successfulDocuments;
    }

    /**
     * Obtiene el total de documentos fallidos.
     * @return int
     */
    public function getFailedDocuments(): int
    {
        return $this->failedDocuments;
    }

    /**
     * Obtiene el total de warnings.
     * @return int
     */
    public function getTotalWarnings(): int
    {
        return $this->totalWarnings;
    }

    /**
     * Obtiene el total de errores.
     * @return int
     */
    public function getTotalErrors(): int
    {
        return $this->totalErrors;
    }

    /**
     * Obtiene el tiempo de procesamiento en segundos.
     * @return float
     */
    public function getProcessingTime(): float
    {
        return $this->processingTime;
    }

    /**
     * Obtiene el timestamp de inicio.
     * @return int
     */
    public function getStartedAt(): int
    {
        return $this->startedAt;
    }

    /**
     * Obtiene el timestamp de finalización.
     * @return int
     */
    public function getFinishedAt(): int
    {
        return $this->finishedAt;
    }

    /**
     * Calcula la tasa de éxito como porcentaje.
     * @return float Entre 0 y 100
     */
    public function getSuccessRate(): float
    {
        if ($this->totalDocuments === 0) {
            return 0.0;
        }
        return round(($this->successfulDocuments / $this->totalDocuments) * 100, 2);
    }

    /**
     * Calcula la tasa de fallo como porcentaje.
     * @return float Entre 0 y 100
     */
    public function getFailureRate(): float
    {
        return 100 - $this->getSuccessRate();
    }

    /**
     * Calcula promedio de warnings por documento.
     * @return float
     */
    public function getAverageWarningsPerDocument(): float
    {
        if ($this->totalDocuments === 0) {
            return 0.0;
        }
        return round($this->totalWarnings / $this->totalDocuments, 2);
    }

    /**
     * Obtiene un resumen en string.
     * @return string
     */
    public function getSummaryString(): string
    {
        return sprintf(
            '%d/%d exitosos (%.1f%%), %d errores, %d warnings, %.2fs',
            $this->successfulDocuments,
            $this->totalDocuments,
            $this->getSuccessRate(),
            $this->totalErrors,
            $this->totalWarnings,
            $this->processingTime
        );
    }

    /**
     * Convierte a array.
     * @return array
     */
    public function toArray(): array
    {
        return [
            'total_documents' => $this->totalDocuments,
            'successful' => $this->successfulDocuments,
            'failed' => $this->failedDocuments,
            'total_warnings' => $this->totalWarnings,
            'total_errors' => $this->totalErrors,
            'success_rate' => $this->getSuccessRate(),
            'failure_rate' => $this->getFailureRate(),
            'average_warnings_per_document' => $this->getAverageWarningsPerDocument(),
            'processing_time_seconds' => $this->processingTime,
            'started_at' => $this->startedAt,
            'finished_at' => $this->finishedAt,
        ];
    }

    /**
     * Convierte a string.
     * @return string
     */
    public function __toString(): string
    {
        return $this->getSummaryString();
    }
}
