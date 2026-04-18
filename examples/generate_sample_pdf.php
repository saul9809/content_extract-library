<?php

/**
 * Generador de PDF de prueba para los ejemplos.
 * 
 * Este script crea un PDF simple que contiene datos de CV
 * para ser utilizado en las pruebas de PdfTextExtractor.
 * 
 * NOTA: Este archivo es ejecutable. Corre: php examples/generate_sample_pdf.php
 */

// Importar autoload
require_once __DIR__ . '/../vendor/autoload.php';

use Smalot\PdfParser\Parser;

/**
 * Crea un PDF simple con contenido de CV.
 * Utiliza TCPDF si está disponible, o crea uno manualmente.
 */
function generateSamplePdf()
{
    try {
        // Intenta usar TCPDF si está disponible
        if (class_exists('TCPDF')) {
            generateWithTcpdf();
            return;
        }
    } catch (\Exception $e) {
        // TCPDF no disponible, crear manualmente
    }

    // Fallback: crear un PDF válido manual usando PHP
    generateManualPdf();
}

/**
 * Genera un PDF usando TCPDF.
 */
function generateWithTcpdf()
{
    $pdf = new \TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    // Contenido del CV
    $content = <<<'EOT'
JUAN GARCÍA PÉREZ

DATOS PERSONALES
Carnet de Identidad: 1234567890V
Teléfono: +34-912-345-678
Email: juan.garcia@example.com
Ubicación: Madrid, España

EXPERIENCIA PROFESIONAL
Ingeniero de Software Senior (2020 - Presente)
TechCorp Solutions, Madrid
- Desarrollo de aplicaciones Python y PHP
- Liderazgo de equipo de 5 desarrolladores
- Implementación de microservicios en Docker/Kubernetes

Ingeniero de Software (2017 - 2020)
StartupXYZ, Barcelona
- Backend development con Laravel
- Base de datos PostgreSQL
- Integración de APIs REST

EDUCACIÓN
Grado en Ingeniería Informática (2016)
Universidad Politécnica de Madrid

HABILIDADES
PHP, Python, JavaScript, Docker, Kubernetes, Git, Agile/Scrum
EOT;

    $pdf->MultiCell(0, 10, $content, 0, 'L');
    $pdf->Output(__DIR__ . '/sample_cv.pdf', 'F');
}

/**
 * Genera un PDF válido manualmente.
 * Crea un archivo PDF mínimo pero válido que smalot/pdfparser pueda procesar.
 */
function generateManualPdf()
{
    $filename = __DIR__ . '/sample_cv.pdf';

    // Construir objetos PDF
    $obj1 = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $obj2 = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    $obj3 = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";

    $streamContent = "BT\n/F1 12 Tf\n50 750 Td\n(JUAN GARCIA PEREZ) Tj\n0 -20 Td\n(Carnet de Identidad: 1234567890) Tj\n0 -20 Td\n(Especialidad: Ingeniero de Software) Tj\n0 -20 Td\n(Plaza: Desarrollador Senior) Tj\n0 -20 Td\n(Anos de Experiencia: 8) Tj\nET\n";

    $obj4 = "4 0 obj\n<< /Length " . strlen($streamContent) . " >>\nstream\n" . $streamContent . "endstream\nendobj\n";
    $obj5 = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

    // Header y body
    $header = "%PDF-1.1\n";
    $body = $obj1 . $obj2 . $obj3 . $obj4 . $obj5;

    // Calcular offsets correctamente
    $pos = [];
    $pos[0] = 0;
    $pos[1] = strlen($header);
    $pos[2] = $pos[1] + strlen($obj1);
    $pos[3] = $pos[2] + strlen($obj2);
    $pos[4] = $pos[3] + strlen($obj3);
    $pos[5] = $pos[4] + strlen($obj4);

    // Construir xref
    $xrefStart = $pos[5] + strlen($obj5);
    $xref = "xref\n";
    $xref .= "0 6\n";
    $xref .= "0000000000 65535 f \n";
    for ($i = 1; $i <= 5; $i++) {
        $xref .= sprintf("%010d 00000 n \n", $pos[$i]);
    }

    // Trailer y EOF
    $trailer = "trailer\n<< /Size 6 /Root 1 0 R >>\n";
    $trailer .= "startxref\n" . $xrefStart . "\n%%EOF\n";

    // Escribir archivo
    $pdf = $header . $body . $xref . $trailer;
    file_put_contents($filename, $pdf);
    echo "✅ PDF generado correctamente: " . basename($filename) . "\n";
}

// Buscar archivo existente o generar
if (!file_exists(__DIR__ . '/sample_cv.pdf')) {
    generateSamplePdf();
} else {
    echo "📄 sample_cv.pdf ya existe\n";
}
