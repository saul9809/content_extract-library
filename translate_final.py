#!/usr/bin/env python3
"""Final comprehensive Spanish to English translation"""

import re
from pathlib import Path

# Final comprehensive translation dictionary
translations = {
    # High priority - remaining terms
    "BLOQUE": "BLOCK",
    "bloque": "block",
    "FUNDACIONES": "FOUNDATIONS",
    "fundaciones": "foundations",
    "EXTRACCIÓN DE PDF": "PDF EXTRACTION",
    "Extracción de PDF": "PDF extraction",
    "DIGITALES": "DIGITAL",
    "digitales": "digital",
    "ESTRUCTURACIÓN": "STRUCTURING",
    "estructuración": "structuring",
    "SEMÁNTICA": "SEMANTIC",
    "Semántica": "Semantic",
    "semántica": "semantic",
    "de PDFs": "of PDFs",
    "Próximos bloques": "Next blocks",
    "próximos bloques": "next blocks",
    "planificados": "planned",
    "intactos": "intact",
    "Intactos": "Intact",
    "estructura está": "structure is",
    "está en lugar": "is in place",
    "cada bloque": "each block",
    "completamente independiente": "completely independent",
    "Preparación para": "Preparation for",
    "Futuros": "Future",
    "para PDFs": "for PDFs",
    "escaneados": "scanned",
    "Robustez": "Robustness",
    "robustez": "robustness",
    "DX": "DX",
    "Backward compatible": "Backward compatible",
    "compatible": "compatible",
    "Compatibilidad": "Compatibility",
    "compatibilidad": "compatibility",
    "Resultado Final": "Final Result",
    "resultado final": "final result",
    "Ejemplos básicos": "Basic examples",
    "ejemplos básicos": "basic examples",
    "Ejemplos avanzados": "Advanced examples",
    "ejemplos avanzados": "advanced examples",
    "Ejemplos Laravel-style": "Laravel-style examples",
    "ejemplos Laravel-style": "Laravel-style examples",
    "Verificar": "Verify",
    "verificar": "verify",
    "Seguridad": "Security",
    "seguridad": "security",
    "Compliance": "Compliance",
    "compliance": "compliance",
    "Publication": "Publication",
    "publication": "publication",
    "FIN DEL": "END OF",
    "fin del": "end of",
    "Batch Processing": "Batch Processing",
    "batch processing": "batch processing",
    "cambios": "changes",
    "sin cambios": "no changes",
    "Backward compatibility": "Backward compatibility",
    "backward compatibility": "backward compatibility",
}

def translate_file(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        original = content
        
        # Apply translations (longest first to avoid partial replacements)
        for spanish, english in sorted(translations.items(), key=lambda x: -len(x[0])):
            content = content.replace(spanish, english)
        
        if content != original:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error: {e}")
        return False

# Process all markdown files
md_files = list(Path('.').glob('*.md'))
print(f"Final pass: translating {len(md_files)} markdown files...")

translated = 0
for md_file in md_files:
    if translate_file(str(md_file)):
        translated += 1
        print(f"✓ {md_file.name}")

# Also do final pass on PHP files
php_files = list(Path('src').rglob('*.php'))
for php_file in php_files:
    if translate_file(str(php_file)):
        translated += 1
        print(f"✓ PHP: {php_file.relative_to('src')}")

php_files = list(Path('examples').rglob('*.php'))
for php_file in php_files:
    if translate_file(str(php_file)):
        translated += 1

print(f"\nFinal translation pass complete! {translated} files updated.")
