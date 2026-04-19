# ✅ PROMPT PARA CLAUDE CODE - BLOQUE 2: EXTRACCIÓN DE PDF DIGITALES

(Cópialo tal cual. No hace falta modificarlo.)

1️⃣ Rol (¿Quién eres?)
Eres un arquitecto senior de software PHP con más de 15 años de experiencia, especializado en:

Diseño de librerías PHP profesionales
Composer / Packagist
PSR‑4, PSR‑12, Clean Architecture
Procesamiento de documentos (PDF)
Librerías framework‑agnostic
DX (Developer Experience)

Tu responsabilidad es diseñar y generar código production‑ready, bien estructurado, documentado y extensible, como si la librería fuera a ser publicada y usada por terceros en entornos reales.

2️⃣ Contexto (¿Dónde estamos?)

Estoy construyendo una librería FREE y open‑source en PHP puro, cuyo objetivo es:

Procesar PDFs multipágina (digitales u opcionalmente escaneados)
Extraer su contenido textual
Convertir ese contenido en una estructura JSON configurable
Pensada para carga masiva de documentos (ej. currículos)
Usable directamente en Laravel vía composer require, pero sin depender de Laravel ni de ningún framework

La librería debe ser:

Framework‑agnostic
Instalable con Composer
Compatible con PHP 8.1+
Diseñada para batch processing
Preparada para futuras extensiones (OCR, IA, etc.)

Status actual confirmado del Block 1:
✅ PHP puro (sin Laravel, Symfony ni frameworks)
✅ Autoload PSR‑4 funcionando
✅ Core ContentProcessor
✅ ExtractorInterface, StructurerInterface, SchemaInterface
✅ Extractor simple (TextFileExtractor)
✅ Structurer simple (SimpleLineStructurer)
✅ Batch processing funcional
✅ Ejemplos ejecutados correctamente
✅ Documentation y architecture definidas

⚠️ IMPORTANTE: La librería NO debe reestructurarse. Solo debe extenderse correctamente.

3️⃣ Tarea exacta (¿Qué necesito?)

Necesito que generes paso a paso la extensión BLOQUE 2, comenzando SOLO por la base:

📦 Extracción REAL de texto desde PDF DIGITALES (no escaneados)
✅ Crear nuevo extractor: PdfTextExtractor
✅ Implementa ExtractorInterface
✅ Extrae texto REAL desde archivos .pdf
✅ Usar: smalot/pdfparser

El extractor debe:

- Recibir la ruta del PDF
- Validar existencia del archivo
- Extraer TODO el texto del PDF
- Retornar string
- Lanzar excepción controlada si falla

Mantener 100% compatibilidad con:

- ContentProcessor
- Extractores existentes
- Ejemplos del Block 1

Agregar un ejemplo funcional nuevo que procese un PDF real usando:
->withExtractor(new PdfTextExtractor())

⚠️ IMPORTANTE:
No avances a pasos futuros sin que el paso anterior esté completo y funcional.
Diseña la librería para que pueda evolucionar, pero empieza mínima.

4️⃣ Restricciones y reglas (¿Qué límites hay?)

Debes cumplir todas estas reglas sin excepción:

Architecture y diseño

❌ No modificar código del Block 1
✅ SOLO agregar nuevas clases
✅ Respetar interfaces existentes
✅ PHP 8.1+
✅ PSR‑4 para autoload
✅ PSR‑12 para estilo
✅ Código limpio, sin magia
✅ Interfaces antes de implementaciones
✅ Dependency Injection (no singletons)
✅ Nada de IA por ahora
✅ Nada de dependencias innecesarias
✅ El core debe funcionar por CLI o Laravel indistintamente
✅ Pensar siempre en batch processing (no single file)

Alcance funcional

❌ NO OCR
❌ NO IA
❌ NO heurísticas avanzadas
❌ NO regex complejos
✅ PDFs DIGITALES solamente
✅ Multipágina (si el parser lo permite)
✅ Batch compatible

Si algo aún no se implementa (ej. OCR), déjalo preparado con interfaces, no con código incompleto.

5️⃣ Formato de salida (¿Cómo lo quiero?)

Quiero la respuesta estructurada y accionable, exactamente así:

A. Explicación corta del paso actual
B. Dependencia a instalar (comando Composer)
C. Archivos a crear (con rutas claras)
D. Código completo de cada archivo
E. Ejemplo funcional nuevo (PDF → JSON)
F. Pasos exactos para probar
G. Confirmación explícita de que el paso está terminado

No des saltos grandes.
No inventes features.
No simplifiques en exceso.

✅ Resultado esperado

Al terminar el bloque, yo pueda:
composer install
php examples/test_pdf_extraction.php

Y comprobar que el autoload, namespaces y core funcionan correctamente.

🔚 Fin del prompt
