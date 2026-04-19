#!/usr/bin/env python3
"""Translate all PHP source code comments and strings from Spanish to English"""

import os
import re
from pathlib import Path

# Large comprehensive Spanish to English translation dictionary
translations = {
    # Common technical terms
    "Normalización de errores": "Error normalization",
    "Normalización": "Normalization",
    "Estructura estándar": "Standard structure",
    "durante la ingesta": "during ingestion",
    "ingesta, extracción": "ingestion, extraction",
    "la ingesta": "ingestion",
    "durante la": "during",
    "Contexto adicional": "Additional context",
    "contexto adicional": "additional context",
    "contexto": "context",
    "Contexto": "Context",
    "Tipo de error": "Error type",
    "tipo de error": "error type",
    "Mensaje de error": "Error message",
    "mensaje de error": "error message",
    "Código de error": "Error code",
    "código de error": "error code",
    "Timestamp del error": "Error timestamp",
    "timestamp del error": "error timestamp",
    "Timestamp": "Timestamp",
    "timestamp": "timestamp",
    "Constructor": "Constructor",
    "constructor": "constructor",
    "CONSTRUCTOR": "CONSTRUCTOR",
    
    # Common phrases
    "exitosamente": "successfully",
    "Exitosamente": "Successfully",
    "EXITOSAMENTE": "SUCCESSFULLY",
    "exitoso": "successful",
    "Exitoso": "Successful",
    "EXITOSO": "SUCCESSFUL",
    "no fue": "was not",
    "No fue": "Was not",
    "NO FUE": "WAS NOT",
    "que ocurren": "that occur",
    "Que ocurren": "That occur",
    "QUE OCURREN": "THAT OCCUR",
    "Bloque ": "Block ",
    "bloque ": "block ",
    "BLOQUE ": "BLOCK ",
    
    # Common variable descriptions
    "nombre del archivo": "file name",
    "Nombre del archivo": "File name",
    "nombre": "name",
    "Nombre": "Name",
    "NOMBRE": "NAME",
    "línea": "line",
    "Línea": "Line",
    "LÍNEA": "LINE",
    "archivo": "file",
    "Archivo": "File",
    "ARCHIVO": "FILE",
    "archivos": "files",
    "Archivos": "Files",
    "ARCHIVOS": "FILES",
    
    # PDF-related
    "extracción de PDF": "PDF extraction",
    "Extracción de PDF": "PDF extraction",
    "EXTRACCIÓN DE PDF": "PDF EXTRACTION",
    "de texto": "of text",
    "De texto": "Of text",
    "DE TEXTO": "OF TEXT",
    "del PDF": "of the PDF",
    "Del PDF": "Of the PDF",
    "DEL PDF": "OF THE PDF",
    "páginas": "pages",
    "Páginas": "Pages",
    "PÁGINAS": "PAGES",
    
    # Warnings
    "Advertencia": "Warning",
    "advertencia": "warning",
    "ADVERTENCIA": "WARNING",
    "advertencias": "warnings",
    "Advertencias": "Warnings",
    "ADVERTENCIAS": "WARNINGS",
    
    # Validation
    "validación": "validation",
    "Validación": "Validation",
    "VALIDACIÓN": "VALIDATION",
    "validador": "validator",
    "Validador": "Validator",
    "VALIDADOR": "VALIDATOR",
    "campo requerido": "required field",
    "Campo requerido": "Required field",
    "CAMPO REQUERIDO": "REQUIRED FIELD",
    "campo": "field",
    "Campo": "Field",
    "CAMPO": "FIELD",
    "campos": "fields",
    "Campos": "Fields",
    "CAMPOS": "FIELDS",
    "encontrado": "found",
    "Encontrado": "Found",
    "ENCONTRADO": "FOUND",
    "no encontrado": "not found",
    "No encontrado": "Not found",
    "NO ENCONTRADO": "NOT FOUND",
    "múltiples veces": "multiple times",
    "Múltiples veces": "Multiple times",
    "MÚLTIPLES VECES": "MULTIPLE TIMES",
    "ambiguo": "ambiguous",
    "Ambiguo": "Ambiguous",
    "AMBIGUO": "AMBIGUOUS",
    
    # Processing
    "procesamiento": "processing",
    "Procesamiento": "Processing",
    "PROCESAMIENTO": "PROCESSING",
    "procesar": "process",
    "Procesar": "Process",
    "PROCESAR": "PROCESS",
    "procesado": "processed",
    "Procesado": "Processed",
    "PROCESADO": "PROCESSED",
    "procesador": "processor",
    "Procesador": "Processor",
    "PROCESADOR": "PROCESSOR",
    "no puede": "cannot",
    "No puede": "Cannot",
    "NO PUEDE": "CANNOT",
    "puede": "can",
    "Puede": "Can",
    "PUEDE": "CAN",
    
    # Files
    "no existe": "does not exist",
    "No existe": "Does not exist",
    "NO EXISTE": "DOES NOT EXIST",
    "no es legible": "is not readable",
    "No es legible": "Is not readable",
    "NO ES LEGIBLE": "IS NOT READABLE",
    "legible": "readable",
    "Legible": "Readable",
    "LEGIBLE": "READABLE",
    "existe": "exists",
    "Existe": "Exists",
    "EXISTE": "EXISTS",
    "no es": "is not",
    "No es": "Is not",
    "NO ES": "IS NOT",
    "contenido": "content",
    "Contenido": "Content",
    "CONTENIDO": "CONTENT",
    
    # Errors
    "Error al": "Error when",
    "error al": "error when",
    "ERROR AL": "ERROR WHEN",
    "Error de": "Error of",
    "error de": "error of",
    "ERROR DE": "ERROR OF",
    "error": "error",
    "Error": "Error",
    "ERROR": "ERROR",
    "errores": "errors",
    "Errores": "Errors",
    "ERRORES": "ERRORS",
    "fallo": "failure",
    "Fallo": "Failure",
    "FALLO": "FAILURE",
    "fallos": "failures",
    "Fallos": "Failures",
    "FALLOS": "FAILURES",
    
    # Data
    "datos": "data",
    "Datos": "Data",
    "DATOS": "DATA",
    "información": "information",
    "Información": "Information",
    "INFORMACIÓN": "INFORMATION",
    "estructura": "structure",
    "Estructura": "Structure",
    "ESTRUCTURA": "STRUCTURE",
    "estructurador": "structurer",
    "Estructurador": "Structurer",
    "ESTRUCTURADOR": "STRUCTURER",
    
    # Extraction
    "extracción": "extraction",
    "Extracción": "Extraction",
    "EXTRACCIÓN": "EXTRACTION",
    "extractor": "extractor",
    "Extractor": "Extractor",
    "EXTRACTOR": "EXTRACTOR",
    "extraer": "extract",
    "Extraer": "Extract",
    "EXTRAER": "EXTRACT",
    "extraído": "extracted",
    "Extraído": "Extracted",
    "EXTRAÍDO": "EXTRACTED",
    
    # General
    "descripción": "description",
    "Descripción": "Description",
    "DESCRIPCIÓN": "DESCRIPTION",
    "interfaz": "interface",
    "Interfaz": "Interface",
    "INTERFAZ": "INTERFACE",
    "implementación": "implementation",
    "Implementación": "Implementation",
    "IMPLEMENTACIÓN": "IMPLEMENTATION",
    "versión": "version",
    "Versión": "Version",
    "VERSION": "VERSION",
    "clase": "class",
    "Clase": "Class",
    "CLASE": "CLASS",
    "método": "method",
    "Método": "Method",
    "MÉTODO": "METHOD",
    "función": "function",
    "Función": "Function",
    "FUNCIÓN": "FUNCTION",
    "parámetro": "parameter",
    "Parámetro": "Parameter",
    "PARÁMETRO": "PARAMETER",
    "parámetros": "parameters",
    "Parámetros": "Parameters",
    "PARÁMETROS": "PARAMETERS",
    "retorno": "return",
    "Retorno": "Return",
    "RETORNO": "RETURN",
    "devuelve": "returns",
    "Devuelve": "Returns",
    "DEVUELVE": "RETURNS",
    "devolver": "return",
    "Devolver": "Return",
    "DEVOLVER": "RETURN",
}

def translate_php_file(filepath):
    """Translate PHP file comments and strings"""
    try:
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        original = content
        
        # Apply translations
        for spanish, english in sorted(translations.items(), key=lambda x: -len(x[0])):
            # Be careful with word boundaries in comments and strings
            # Replace in comments (// and /* */)
            content = re.sub(
                r'(//.*)' + re.escape(spanish),
                lambda m: m.group(1).replace(spanish, english),
                content,
                flags=re.IGNORECASE
            )
            # Replace in block comments and docstrings
            content = re.sub(
                r'(/\*.*?\*/)' + re.escape(spanish),
                lambda m: m.group(1).replace(spanish, english),
                content,
                flags=re.IGNORECASE | re.DOTALL
            )
            # Simple replacement
            content = content.replace(spanish, english)
        
        if content != original:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error translating {filepath}: {e}")
        return False

# Process all PHP files
php_files = list(Path('src').rglob('*.php'))
print(f"Translating {len(php_files)} PHP source files...")

translated = 0
for php_file in php_files:
    if translate_php_file(str(php_file)):
        print(f"✓ {php_file.relative_to('src')}")
        translated += 1

# Also translate example files
example_files = list(Path('examples').rglob('*.php'))
print(f"\nTranslating {len(example_files)} example files...")
for example_file in example_files:
    if translate_php_file(str(example_file)):
        print(f"✓ {example_file.relative_to('examples')}")
        translated += 1

print(f"\nPHP translation complete! {translated} files updated.")
