# PROMPT PARA CLAUDE CODE - BLOCK 2: PDF EXTRACTION DIGITAL

## 1️⃣ Rol (¿Quién eres?)

Eres un arquitecto senior de software PHP con más de 15 años de experiencia, especializado en:

- Diseño de librerías PHP profesionales
- Composer / Packagist
- PSR‑4, PSR‑12, SOLID
- Procesamiento de documentos (PDF)
- Architectures framework‑agnostic
- Evolución por blocks sin romper compatibility

Tu misión es extender una librería existente, respetando estrictamente su architecture, sin introducir acoplamientos innecesarios ni romper blocks ya validados.

---

## 2️⃣ Contexto (¿Dónde estamos?)

Existe una librería PHP llamada **Content Processor**, ya en producción interna, con el Block 1 completamente finalizado y probado.

### Status actual confirmado:

✅ PHP puro (sin Laravel, Symfony ni frameworks)
✅ Autoload PSR‑4 funcionando
✅ Core ContentProcessor
✅ ExtractorInterface, StructurerInterface, SchemaInterface
✅ Extractor simple (TextFileExtractor)
✅ Structurer simple (SimpleLineStructurer)
✅ Batch processing funcional
✅ Ejemplos ejecutados correctamente
✅ Documentation y architecture definidas

**⚠️ IMPORTANTE:**

- La librería NO debe reestructurarse
- Solo debe extenderse correctamente
- El Block 1 es INMUTABLE

---

## 3️⃣ Tarea exacta (¿Qué necesito?)

Debes implementar **EL BLOCK 2 COMPLETO**, con foco exclusivo en:

✅ Extracción REAL de texto desde PDF DIGITAL (no scanned)

### Concretamente:

**Crear un nuevo extractor:**

- `PdfTextExtractor`
- Implementa `ExtractorInterface`
- Extrae texto REAL desde archivos `.pdf`

**Usar una dependencia PHP adecuada:**

- Recomendada: `smalot/pdfparser`

### El extractor debe:

- Recibir la ruta del PDF
- Validar existencia del archivo
- Extraer TODO el texto del PDF
- Retornar string
- Lanzar excepción controlada si falla

### Mantener 100% compatibility con:

- ContentProcessor
- Extractores existentes
- Ejemplos del Block 1

### Agregar un ejemplo funcional nuevo que procese un PDF real:

```php
->withExtractor(new PdfTextExtractor())
```

---

## 4️⃣ Restricciones y reglas (¿Qué límites hay?)

Debes cumplir TODAS estas reglas sin excepción:

### Architecture y diseño

❌ No modificar código del Block 1
✅ SOLO agregar nuevas clases
✅ Respetar interfaces existentes
✅ PHP 8.1+
✅ PSR‑4 y PSR‑12
✅ Código limpio, sin magia
✅ Dependency Injection (no singletons)
✅ Lógica clara y documentada

### Alcance funcional

❌ NO OCR
❌ NO IA
❌ NO heurísticas avanzadas
❌ NO regex complejos
✅ PDFs DIGITAL solamente
✅ Multipágina (si el parser lo permite)
✅ Batch compatible

**Si algo no corresponde a este block, déjalo explícitamente fuera.**

---

## 5️⃣ Formato de salida (¿Cómo lo quiero?)

Delivery el resultado de forma estrictamente ordenada, siguiendo este esquema:

### A. Explicación breve del Block 2

### B. Dependencia a instalar (comando Composer)

### C. Archivos nuevos creados (con rutas claras)

### D. Código completo y final de cada archivo

### E. Ejemplo funcional nuevo (PDF → JSON)

### F. Pasos exactos para probar

### G. Confirmación explícita de closure del Block 2

### ❗ RESTRICCIONES DE DELIVERY

- No adelantes blocks futuros
- No generes código innecesario
- No reformules la architecture existente

---

## ✅ Resultado esperado

Al terminar este block, debo poder hacer:

```bash
composer install
php examples/test_pdf_extraction.php
```

Y obtener:

✅ Texto real extraído desde PDF
✅ Pipeline completo funcionando
✅ JSON estructurado correctamente
✅ Sin romper ningún ejemplo anterior

---

## 📌 Archivos existentes de referencia

La librería ya tiene estos archivos que debes respetar:

- `composer.json` — Configuración
- `src/Contracts/ExtractorInterface.php` — Interfaz base
- `src/Core/ContentProcessor.php` — Orqustatusr
- `examples/test_functional.php` — Test Block 1
- `README.md`, `ARCHITECTURE.md` — Documentation

---

**END OF PROMPT**

---

> 💡 **Instrucciones para usar este prompt:**
>
> 1. Copia este prompt completo
> 2. Pégalo en Claude Code
> 3. Claude generará el Block 2 exactamente como se especifica
> 4. Integra los archivos en tu project
> 5. Ejecuta: `php examples/test_pdf_extraction.php`
