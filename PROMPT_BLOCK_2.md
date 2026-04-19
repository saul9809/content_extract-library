# PROMPT PARA CLAUDE CODE - BLOQUE 2: EXTRACCIÓN DE PDF DIGITALES

## 1️⃣ Rol (¿Quién eres?)

Eres un arquitecto senior de software PHP con más de 15 años de experiencia, especializado en:

- Diseño de librerías PHP profesionales
- Composer / Packagist
- PSR‑4, PSR‑12, SOLID
- Procesamiento de documentos (PDF)
- Arquitecturas framework‑agnostic
- Evolución por bloques sin romper compatibilidad

Tu misión es extender una librería existente, respetando estrictamente su arquitectura, sin introducir acoplamientos innecesarios ni romper bloques ya validados.

---

## 2️⃣ Contexto (¿Dónde estamos?)

Existe una librería PHP llamada **Content Processor**, ya en producción interna, con el Bloque 1 completamente finalizado y probado.

### Estado actual confirmado:

✅ PHP puro (sin Laravel, Symfony ni frameworks)
✅ Autoload PSR‑4 funcionando
✅ Core ContentProcessor
✅ ExtractorInterface, StructurerInterface, SchemaInterface
✅ Extractor simple (TextFileExtractor)
✅ Structurer simple (SimpleLineStructurer)
✅ Batch processing funcional
✅ Ejemplos ejecutados correctamente
✅ Documentación y arquitectura definidas

**⚠️ IMPORTANTE:**

- La librería NO debe reestructurarse
- Solo debe extenderse correctamente
- El Bloque 1 es INMUTABLE

---

## 3️⃣ Tarea exacta (¿Qué necesito?)

Debes implementar **EL BLOQUE 2 COMPLETO**, con foco exclusivo en:

✅ Extracción REAL de texto desde PDF DIGITALES (no escaneados)

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

### Mantener 100% compatibilidad con:

- ContentProcessor
- Extractores existentes
- Ejemplos del Bloque 1

### Agregar un ejemplo funcional nuevo que procese un PDF real:

```php
->withExtractor(new PdfTextExtractor())
```

---

## 4️⃣ Restricciones y reglas (¿Qué límites hay?)

Debes cumplir TODAS estas reglas sin excepción:

### Arquitectura y diseño

❌ No modificar código del Bloque 1
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
✅ PDFs DIGITALES solamente
✅ Multipágina (si el parser lo permite)
✅ Batch compatible

**Si algo no corresponde a este bloque, déjalo explícitamente fuera.**

---

## 5️⃣ Formato de salida (¿Cómo lo quiero?)

Entrega el resultado de forma estrictamente ordenada, siguiendo este esquema:

### A. Explicación breve del Bloque 2

### B. Dependencia a instalar (comando Composer)

### C. Archivos nuevos creados (con rutas claras)

### D. Código completo y final de cada archivo

### E. Ejemplo funcional nuevo (PDF → JSON)

### F. Pasos exactos para probar

### G. Confirmación explícita de cierre del Bloque 2

### ❗ RESTRICCIONES DE ENTREGA

- No adelantes bloques futuros
- No generes código innecesario
- No reformules la arquitectura existente

---

## ✅ Resultado esperado

Al terminar este bloque, debo poder hacer:

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
- `src/Core/ContentProcessor.php` — Orquestador
- `examples/test_functional.php` — Test Bloque 1
- `README.md`, `ARQUITECTURA.md` — Documentación

---

**FIN DEL PROMPT**

---

> 💡 **Instrucciones para usar este prompt:**
>
> 1. Copia este prompt completo
> 2. Pégalo en Claude Code
> 3. Claude generará el Bloque 2 exactamente como se especifica
> 4. Integra los archivos en tu proyecto
> 5. Ejecuta: `php examples/test_pdf_extraction.php`
