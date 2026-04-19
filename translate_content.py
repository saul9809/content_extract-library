#!/usr/bin/env python3
"""Comprehensive Spanish to English translation for documentation"""

import os
import re
from pathlib import Path

translations = {
    # Common keywords - Spanish to English
    "Bloque ": "Block ",
    "Completado": "Completed",
    "completado": "completed",
    "COMPLETADO": "COMPLETED",
    "Entrega": "Delivery",
    "entrega": "delivery",
    "ENTREGA": "DELIVERY",
    "Verificación": "Verification",
    "verificación": "verification",
    "VERIFICACIÓN": "VERIFICATION",
    "Cierre": "Closure",
    "cierre": "closure",
    "CIERRE": "CLOSURE",
    "Documentación": "Documentation",
    "documentación": "documentation",
    "DOCUMENTACIÓN": "DOCUMENTATION",
    "Arquitectura": "Architecture",
    "arquitectura": "architecture",
    "ARQUITECTURA": "ARCHITECTURE",
    "Resumen": "Summary",
    "resumen": "summary",
    "RESUMEN": "SUMMARY",
    "Ejecutivo": "Executive",
    "ejecutivo": "executive",
    "EJECUTIVO": "EXECUTIVE",
    "Proyecto": "Project",
    "proyecto": "project",
    "PROYECTO": "PROJECT",
    "Guía": "Guide",
    "guía": "guide",
    "GUÍA": "GUIDE",
    "Rápida": "Quick",
    "rápida": "quick",
    "RÁPIDA": "QUICK",
    "Estado": "Status",
    "estado": "status",
    "ESTADO": "STATUS",
    "Checklist": "Checklist",
    "checklist": "checklist",
    "CHECKLIST": "CHECKLIST",
    "Publicación": "Publication",
    "publicación": "publication",
    "PUBLICACIÓN": "PUBLICATION",
    "Packagist": "Packagist",
    "packagist": "packagist",
    "PACKAGIST": "PACKAGIST",
}

def translate_file(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original = content
        
        for spanish, english in translations.items():
            content = content.replace(spanish, english)
        
        if content != original:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error translating {filepath}: {e}")
        return False

# Get all .md files
md_files = list(Path('.').glob('*.md'))
print(f"Translating content in {len(md_files)} markdown files...")

translated = 0
for md_file in md_files:
    if translate_file(md_file):
        print(f"✓ {md_file.name}")
        translated += 1

print(f"\nTranslation complete! {translated} files updated.")
