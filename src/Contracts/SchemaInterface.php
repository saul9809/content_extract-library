<?php

namespace ContentProcessor\Contracts;

/**
 * Interfaz para esquemas de estructuración.
 * 
 * Define el contrato que debe cumplir cualquier estrategia de definición
 * de esquemas para validar y estructurar contenido extraído.
 */
interface SchemaInterface
{
    /**
     * Retorna la definición del esquema.
     * 
     * @return array Definición del esquema (estructura de campos y reglas)
     */
    public function getDefinition(): array;

    /**
     * Valida que un contenido cumpla con este esquema.
     * 
     * @param array $data Datos a validar
     * @return array ['valid' => bool, 'errors' => array] Resultado de validación
     */
    public function validate(array $data): array;

    /**
     * Retorna el nombre del esquema.
     * 
     * @return string Nombre identificador del esquema
     */
    public function getName(): string;
}
