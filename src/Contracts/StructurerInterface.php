<?php

namespace ContentProcessor\Contracts;

/**
 * Interfaz para estructuradores de contenido.
 * 
 * Define el contrato que debe cumplir cualquier estrategia de transformación
 * de contenido extraído en una estructura JSON según un esquema.
 */
interface StructurerInterface
{
    /**
     * Estructura el contenido según el esquema proporcionado.
     * 
     * @param array $content Contenido extraído (array de strings)
     * @param SchemaInterface $schema Esquema de destino
     * @return array Contenido estructurado conforme al esquema
     * @throws \Exception Si la estructuración falla
     */
    public function structure(array $content, SchemaInterface $schema): array;

    /**
     * Retorna el nombre del estructurador.
     * 
     * @return string Nombre identificador del estructurador
     */
    public function getName(): string;
}
