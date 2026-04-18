<?php

namespace ContentProcessor\Contracts;

/**
 * Interfaz para extractores de contenido.
 * 
 * Define el contrato que debe cumplir cualquier estrategia de extracción
 * de contenido desde diferentes fuentes (PDF, texto, etc.).
 */
interface ExtractorInterface
{
    /**
     * Extrae contenido de una fuente.
     * 
     * @param string $source Ruta o identificador de la fuente
     * @return array Contenido extraído como array de strings (por página/sección)
     * @throws \Exception Si la extracción falla
     */
    public function extract(string $source): array;

    /**
     * Valida que la fuente sea procesable por este extractor.
     * 
     * @param string $source Ruta o identificador de la fuente
     * @return bool True si puede procesarse, false en caso contrario
     */
    public function canHandle(string $source): bool;

    /**
     * Retorna el nombre del extractor.
     * 
     * @return string Nombre identificador del extractor
     */
    public function getName(): string;
}
