✅ PROMPT PARA CLAUDE CODE — BLOQUE 3
Estructuración semántica: PDF → JSON configurable

1️⃣ Rol (¿Quién eres?)
Eres un arquitecto senior de software PHP, con más de 15 años de experiencia, especializado en:

Diseño de librerías PHP profesionales
Procesamiento de documentos (PDF → datos)
Architectures framework‑agnostic
Diseño por bloques incrementales
PSR‑4, PSR‑12, SOLID
APIs limpias y orientadas a desarrolladores

Tu responsabilidad es implementar la capa semántica de una librería, sin romper compatibilidad previa, respetando contratos existentes y pensando en uso real en producción.

2️⃣ Contexto (¿Dónde estamos?)
Existe una librería PHP en el project actual, ya con:
✅ Block 1 COMPLETED

Core PHP puro
API fluida (ContentProcessor::make())
Interfaces base (ExtractorInterface, StructurerInterface, SchemaInterface)
Batch processing funcional

✅ Block 2 COMPLETED

Ingesta de múltiples archivos
Validación técnica de documentos
Extracción REAL de texto desde PDFs digitales
PdfTextExtractor funcionando
Separación correcta de:

- documentos exitosos
- errores técnicos por documento

Backward compatibility garantizada

📌 NO se debe modificar código de Bloques 1 o 2.

3️⃣ Tarea exacta (¿Qué necesito?)
Debes implementar EL BLOQUE 3 COMPLETO, cuyo objetivo es:

Convertir el texto crudo extraído del PDF en un
JSON estructurado definido por el usuario técnico,
con manejo de warnings y sin afectar errores técnicos previos.

Concretamente debes:

A. Definir los modelos de datos semánticos
Crear clases claras para:

- DocumentContext: referencia al archivo, texto crudo, metadatos
- StructuredDocumentResult: nombre documento, JSON, warnings

B. Definir el contrato del Structurer
Crear/extender StructurerInterface para:

- structure(DocumentContext, SchemaInterface): StructuredDocumentResult

C. Implementar un Structurer inicial (NO IA)
Implementar un Structurer determinista, por ejemplo:

- RuleBasedStructurer o RegexStructurer

Este Structurer debe:

- Leer el texto crudo
- Aplicar reglas simples basadas en el Schema
- Poblar campos del JSON
- Detectar campos ambiguos o ausentes → warnings (no errores)

D. Integrar con ContentProcessor
El ContentProcessor debe:

- Tomar resultados del Block 2
- Aplicar el Structurer
- Generar un resultado final batch con:
  • data (JSON)
  • errors (del Block 2)
  • warnings (del Block 3)

⚠️ No romper el API pública existente.

E. Ejemplo funcional real
Agregar un ejemplo ejecutable que:

- Procese múltiples PDFs
- Aplique un Schema definido por el usuario
- Produzca JSON estructurado por documento
- Liste errores y warnings

4️⃣ Restricciones y reglas (¿Qué límites hay?)
Debes cumplir TODAS estas reglas:

Architecture:
❌ No modificar Bloques 1 ni 2
✅ Solo agregar nuevas clases
✅ PHP 8.1+
✅ PSR‑4 y PSR‑12
✅ SOLID
✅ Interfaces antes de implementaciones
✅ Código claro y documentado

Alcance funcional:
❌ NO OCR
❌ NO IA
❌ NO NLP avanzado
❌ NO heurísticas complejas
✅ Reglas simples y deterministas
✅ Warnings en lugar de errores semánticos

Diseño:
El Schema lo define el usuario técnico
El JSON final NO lo impone la librería
La librería solo ejecuta el contrato

5️⃣ Formato de salida (¿Cómo lo quiero?)
La delivery debe seguir estrictamente este formato:

A. Explicación breve del Block 3
B. Nuevas clases creadas (con rutas)
C. Código completo de cada clase
D. Ejemplo funcional ejecutable
E. Output JSON esperado
F. Pasos para probar
G. Confirmación explícita de closure del Block 3

❗ No adelantar Block 4
❗ No introducir IA
❗ No romper compatibilidad

✅ Resultado esperado
Al finalizar este bloque, debe ser posible:
Shellphp examples/test_structuring.phpShow more lines
Y obtener:
✅ JSON estructurado por documento
✅ Warnings por campos ambiguos
✅ Errores técnicos intactos
✅ API usable directamente desde Laravel

🔚 Fin del prompt
