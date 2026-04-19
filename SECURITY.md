# SECURITY.md

## Política de Security

**Version:** 1.4.0 (Block 5)  
**Last Updated:** Abril 18, 2026

---

## 1. Responsabilidades de Security

### Lo que Content Processor HACE:

✅ **Valida**:

- Tamaño de archivos PDF (máx 10 MB por defecto)
- Cabecera PDF válida (%PDF-)
- Cantidad de documentos por batch (máx 50 por defecto)
- Extracción segura de contenido

✅ **Protege contra**:

- Denial of Service (DoS) por upload masivo
- PDFs corruptos o vacíos
- Path traversal en rutas
- Consumo excesivo de memoria
- Desbordes de warnings por documento

### Lo que Content Processor NO hace:

❌ **No ejecuta código embebido**:

- PDFs no pueden ejecutar scripts
- No es intérprete de JavaScript embebido
- No realiza evaluaciones dinámicas

❌ **No incluye**:

- OCR (reconocimiento óptico de caracteres)
- Machine learning o IA
- Análisis de contenido profundo
- Antivirus o escaneo de malware

---

## 2. Límites de Security Configurados

### Defaults Recomendados

| Parámetro                  | Límite          | Razón                         |
| -------------------------- | --------------- | ----------------------------- |
| **Tamaño máximo PDF**      | 10 MB           | Prevenir DoS de memoria       |
| **Tamaño máximo texto**    | 5 MB            | Prevenir DoS de procesamiento |
| **Documentos por batch**   | 50              | Limitar operación simultánea  |
| **Warnings por documento** | 100             | Prevenir desbordamiento       |
| **Cabecera PDF mínima**    | 5 bytes (%PDF-) | Validar integridad básica     |

### Cómo Personalizar

```php
// NOTA: Actualmente los límites están en SecurityConfig como constantes.
// Para usar límites personalizados, extiende la clase:

class CustomSecurityConfig extends SecurityConfig
{
    public const MAX_BATCH_DOCUMENTS = 100; // Mayor para tu caso
}

// O crea configuración en tu aplicación:
define('MAX_BATCH_DOCUMENTS', 100);
```

---

## 3. Casos de Riesgo y Mitigación

### 3.1 PDF Malicioso

**Riesgo**: PDF creado para explotar vulnerabilidades del parser

**Mitigación**:

- Se valida cabecera PDF (%PDF-)
- Se ejecuta dentro de proceso PHP sandboxed
- Se usa smalot/pdfparser (auditable, comunidad)
- Los PDFs no pueden ejecutar código

**Responsabilidad del integrador**:

- Usar Content Processor en entorno seguro
- Validar origen of PDFs si es crítico
- Ejecutar antivirus en endpoint si es required

### 3.2 DoS por Upload Masivo

**Riesgo**: Atacante envía 1000 PDFs para colapsar servidor

**Mitigación**:

- Límite de 50 documentos por batch (configurable)
- Límite de 10 MB por PDF
- Detección y error temprano

**Responsabilidad del integrador**:

- Configurar rate limiting en la API
- Usar autenticación si es crítico
- Monitorear uso de memoria y CPU

### 3.3 Path Traversal

**Riesgo**: Atacante usa `../../etc/passwd` para leer archivos

**Mitigación**:

- Normalización de rutas
- Rechazo de `../` en paths
- Validación de existencia de archivo

**Responsabilidad del integrador**:

- No confiar en input del usuario directamente
- Usar whitelist de directorios permitidos
- Validar rutas en nivel de aplicación

### 3.4 Excepción con Stack Trace Expuesto

**Riesgo**: Error de security muestra paths internos del servidor

**Mitigación**:

- SecurityException nunca expone detalles confidenciales
- `getClientMessage()` seguro para cliente
- `getInternalMessage()` solo para logging interno

**Responsabilidad del integrador**:

- Usar `$exception->getClientMessage()` en respuestas API
- Usar `$exception->getInternalMessage()` en logs internos
- Nunca serializar `stackTrace` en JSON

---

## 4. Auditoría y Logging

### Recomendaciones

```php
// ✅ Seguro: Exponer solo mensaje al cliente
try {
    $result = $processor->processFinal();
} catch (SecurityException $e) {
    return response()->json([
        'error' => $e->getClientMessage(), // ✅ Seguro
        'code' => $e->getSecurityType(),
    ], 422);
}

// ✅ Seguro: Logging interno con contexto
catch (SecurityException $e) {
    Log::warning($e->getInternalMessage()); // ✅ Con contexto interno
}

// ❌ Inseguro: Exponer stack trace
$e->getTrace(); // ❌ NUNCA en respuesta API
```

### Qué Loguear

```php
// ✅ Loguear (interno):
- Tipo de excepción de security
- Archivo/documento afectado
- Hora del evento
- IP del cliente (si aplica)
- Contexto adicional (tamaño, batch size, etc)

// ❌ NUNCA loguear en respuesta pública:
- Stack traces
- Paths del filesystem
- Información interna del servidor
- Credenciales
```

---

## 5. Dependencias Seguras

### Dependency: smalot/pdfparser

- **Versión:** ^2.0
- **Uso:** Parsing of PDFs digital
- **Security**: Ampliamente utilizado en producción
- **Mantenimiento**: Comunidad activa
- **Auditoría**: Código abierto, puede ser revisado

### Cómo Auditar

```bash
# Ver dependencia segura
composer show smalot/pdfparser

# Verify vulnerabilidades conocidas
composer audit

# Actualizar si hay parches
composer update smalot/pdfparser --with-dependencies
```

---

## 6. Principios de Security Aplicados

### Defense in Depth

- Validación en múltiples capas
- No confiar en un solo check
- Fallar seguro (default a rechazo)

### Least Privilege

- Solo leer archivos necesarios
- No escribir a filesystem (solo lectura)
- Excepciones específicas, no genéricas

### Secure by Default

- Límites conservadores (10 MB, 50 docs)
- Cabecera PDF obligatoria
- Manejo seguro de excepciones

### Input Validation

- Validación en punto de entrada (fromFiles, fromDirectory)
- Rechazo temprano de inputs inválidos
- Mensajes seguros para cliente

---

## 7. Responsabilidades del Integrador

### Setup Recomendado (Laravel)

```php
// config/content-processor.php
return [
    'max_batch_size' => 50,        // Documentos
    'max_file_size_mb' => 10,      // PDFs
    'security_log' => 'security',  // Canal de logs
    'strict_mode' => env('APP_DEBUG', false) === false,
];

// middleware/ValidateUploads.php
Route::post('/documents', function (Request $request) {
    // Validar tamaño total antes de procesar
    $totalSize = collect($request->file('documents'))
        ->sum(fn($f) => $f->getSize());

    if ($totalSize > 500 * 1024 * 1024) { // 500 MB total
        return response()->json(['error' => 'Total demasiado grande'], 413);
    }

    $result = ContentProcessor::make()
        ->withSchema($schema)
        ->fromFiles($request->file('documents'))
        ->processFinal();

    return response()->json($result->toArray());
})->middleware('auth');
```

### Checklist de Implementación

- [ ] Usar autenticación en endpoints que reciben PDFs
- [ ] Validar tamaño en application-level antes de procesar
- [ ] Loguear eventos de security internamente
- [ ] Monitorear uso de memoria y CPU
- [ ] Usar HTTPS/TLS para upload
- [ ] Ratelimit los endpoints
- [ ] Auditar acceso a archivos procesados
- [ ] Tener plan de incident response
- [ ] Revisar esta documentation periódicamente

---

## 8. Reportar Vulnerabilidades

Si descubre una vulnerabilidad de security:

1. **NO** publique en issue tracker público
2. Contacte privadamente a los maintainers
3. Describa el vector y el impacto
4. Dé tiempo para patch (30 días típicamente)

Email de contacto: `security@content-extract.org`

---

## 9. Changelog de Security

### v1.4.0 (Block 5) - Abril 18, 2026

✅ Agregadas clases SecurityConfig y SecurityValidator  
✅ Validación de tamaño de PDF y batch  
✅ Protección contra path traversal  
✅ Manejo seguro de SecurityException  
✅ Validación de cabecera PDF  
✅ Documentation de security completa

---

## 10. Conformidad Normativa

Content Processor está diseñado respetando:

- ✅ OWASP Top 10
- ✅ PSR-12 (PHP Standards)
- ✅ MIT License
- ✅ Security en input/output

No es un producto de security certificado. Úsalo como parte de una estrategia de security más amplia.

---

**Para más información:** Ver README.md y BLOCK_5_COMPLETED.md

_Este documento es de consulta pública. La security es responsabilidad compartida._
