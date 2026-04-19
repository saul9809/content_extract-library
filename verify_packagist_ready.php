<?php

/**
 * Script de verificación: ¿Está el paquete listo para Packagist?
 */

echo "\n========== PACKAGIST READINESS CHECK ==========\n\n";

$errors = [];
$warnings = [];
$passed = [];

// 1. Verificar composer.json existe
if (!file_exists('composer.json')) {
    $errors[] = "❌ composer.json does not exist";
} else {
    $passed[] = "✅ composer.json existe";
}

// 2. Verificar JSON válido
$composerContent = file_get_contents('composer.json');
$json = json_decode($composerContent, true);
if ($json === null) {
    $errors[] = "❌ composer.json no es JSON válido";
} else {
    $passed[] = "✅ composer.json es JSON válido";
}

// 3. Verificar nombre del paquete
if (!isset($json['name']) || strpos($json['name'], '/') === false) {
    $errors[] = "❌ Nombre de paquete inválido (debe ser vendor/package)";
} elseif ($json['name'] !== 'content-extract/content-processor') {
    $warnings[] = "⚠️  Nombre diferente al esperado: " . $json['name'];
} else {
    $passed[] = "✅ Nombre de paquete correcto: " . $json['name'];
}

// 4. Verificar tipo
if ($json['type'] !== 'library') {
    $warnings[] = "⚠️  Type debe ser 'library', actualmente es: " . $json['type'];
} else {
    $passed[] = "✅ Type correcto: library";
}

// 5. Verificar licencia
if (!isset($json['license']) || $json['license'] !== 'MIT') {
    $warnings[] = "⚠️  License debe ser MIT";
} else {
    $passed[] = "✅ License: MIT";
}

// 6. Verificar descripción
if (!isset($json['description']) || strlen($json['description']) < 10) {
    $errors[] = "❌ Description falta o es muy corta";
} else {
    $passed[] = "✅ Description presente (length: " . strlen($json['description']) . ")";
}

// 7. Verificar PHP requirement
if (!isset($json['require']['php'])) {
    $errors[] = "❌ PHP requirement falta";
} elseif (strpos($json['require']['php'], '^8') === false && strpos($json['require']['php'], '>=8') === false) {
    $warnings[] = "⚠️  PHP requirement puede ser demasiado restrictivo: " . $json['require']['php'];
} else {
    $passed[] = "✅ PHP requirement: " . $json['require']['php'];
}

// 8. Verify that no hay Laravel/framework en require
$frameworks = ['laravel/framework', 'symfony/console', 'yii2', 'zend-framework'];
$hasFramework = false;
foreach ($frameworks as $fw) {
    if (isset($json['require'][$fw])) {
        $hasFramework = true;
        $errors[] = "❌ Framework '" . $fw . "' en require (debe ser framework-agnostic)";
    }
}
if (!$hasFramework) {
    $passed[] = "✅ Sin frameworks en require (framework-agnostic)";
}

// 9. Verificar autoload PSR-4
if (!isset($json['autoload']['psr-4'])) {
    $errors[] = "❌ PSR-4 autoload no configurado";
} else {
    $psr4 = $json['autoload']['psr-4'];
    $found = false;
    foreach ($psr4 as $namespace => $path) {
        if ($namespace === 'ContentProcessor\\' && $path === 'src/') {
            $found = true;
            $passed[] = "✅ PSR-4 autoload correcto: ContentProcessor\\ -> src/";
        }
    }
    if (!$found) {
        $warnings[] = "⚠️  PSR-4 mapping no es el esperado: " . json_encode($psr4);
    }
}

// 10. Verificar archivos obligatorios
$requiredFiles = ['README.md', 'LICENSE', 'SECURITY.md', '.gitignore'];
foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        $errors[] = "❌ Archivo requerido falta: " . $file;
    } else {
        $passed[] = "✅ Archivo presente: " . $file;
    }
}

// 11. Verificar git
if (!is_dir('.git')) {
    $errors[] = "❌ No es un repositorio Git";
} else {
    $passed[] = "✅ Es un repositorio Git";

    // Verificar tag v1.3.0
    $tags = shell_exec('git tag --list v1.3.0 2>&1');
    if (strpos($tags, 'v1.3.0') !== false) {
        $passed[] = "✅ Git tag v1.3.0 existe";
    } else {
        $warnings[] = "⚠️  Git tag v1.3.0 no encontrado";
    }
}

// Resultados
echo "PASSED (" . count($passed) . "):\n";
foreach ($passed as $msg) {
    echo "  " . $msg . "\n";
}

if (count($warnings) > 0) {
    echo "\nWARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $msg) {
        echo "  " . $msg . "\n";
    }
}

if (count($errors) > 0) {
    echo "\nERRORS (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "  " . $msg . "\n";
    }
    echo "\n❌ PACKAGIST NOT READY\n";
    exit(1);
} else {
    echo "\n🟢 PACKAGIST READY! Package is ready to submit.\n";
    echo "   Command: composer require content-extract/content-processor\n";
    exit(0);
}
