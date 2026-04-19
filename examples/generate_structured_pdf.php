<?php

/**
 * Generador de PDF con content structuredo (field: valor)
 * 
 * Crea un PDF simple con content en formato que el RuleBasedStructurer
 * can parsear fácilmente: lines con "field_name: value"
 * 
 * Este generador crea un PDF válido.
 * 
 * Uso:
 *     php examples/generate_structured_pdf.php
 */

// PDF válido con  do
function createStructuredPdf($filename)
{
    $content = "name: Juan García López\nemail: juan.garcia@example.com\nphone: +34 912 345 678\nexperience_years: 8\nskills: PHP, Laravel, MySQL, Docker, Git\neducation: Licenciatura en Informática\n";

    // Construir PDF byte-by-byte
    $pdf = "%PDF-1.1\n";

    // Objeto 1: Catalog
    $obj1 = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

    // Objeto 2: Pages
    $obj2 = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";

    // Objeto 4: Font
    $obj4 = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

    // Objeto 5: Stream (content)
    $streamCmd = "BT\n/F1 12 Tf\n50 750 Td\n(" . addslashes($content) . ") Tj\nET\n";
    $streamLen = strlen($streamCmd);
    $obj5 = "5 0 obj\n<< /Length $streamLen >>\nstream\n" . $streamCmd . "endstream\nendobj\n";

    // Objeto 3: Page
    $obj3 = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 5 0 R /Resources << /Font << /F1 4 0 R >> >> >>\nendobj\n";

    // Calcular offsets
    $pos = strlen($pdf);
    $offset1 = $pos;
    $pdf .= $obj1;

    $pos = strlen($pdf);
    $offset2 = $pos;
    $pdf .= $obj2;

    $pos = strlen($pdf);
    $offset3 = $pos;
    $pdf .= $obj3;

    $pos = strlen($pdf);
    $offset4 = $pos;
    $pdf .= $obj4;

    $pos = strlen($pdf);
    $offset5 = $pos;
    $pdf .= $obj5;

    // Tabla de referencias (xref)
    $xref_pos = strlen($pdf);
    $pdf .= "xref\n";
    $pdf .= "0 6\n";
    $pdf .= "0000000000 65535 f \n";
    $pdf .= sprintf("%010d 00000 n \n", $offset1);
    $pdf .= sprintf("%010d 00000 n \n", $offset2);
    $pdf .= sprintf("%010d 00000 n \n", $offset3);
    $pdf .= sprintf("%010d 00000 n \n", $offset4);
    $pdf .= sprintf("%010d 00000 n \n", $offset5);

    // Trailer
    $pdf .= "trailer\n";
    $pdf .= "<< /Size 6 /Root 1 0 R >>\n";
    $pdf .= "startxref\n";
    $pdf .= "$xref_pos\n";
    $pdf .= "%%EOF\n";

    file_put_contents($filename, $pdf);
}

$filename = __DIR__ . '/sample_cv.pdf';
createStructuredPdf($filename);

echo "✅ PDF creado: $filename\n";
echo "   Content: CV structuredo con formato 'field: value'\n";
echo "   Listo para RuleBasedStructurer\n";
