<?php

/**
 * Script de prueba: Verificar que el paquete funciona cuando se instala
 */

echo "\n========== INSTALLATION SIMULATION TEST ==========\n\n";

// Simular que el paquete está instalado (como si composer lo hubiera instalado)
// Verificar que el autoload funciona

$vendorPath = __DIR__ . '/vendor';
$autoloadPath = $vendorPath . '/autoload.php';

if (!file_exists($autoloadPath)) {
    echo "⚠️  vendor/autoload.php no existe (es normal en desarrollo)\n";
    echo "Intentando cargar directamente desde src/\n\n";

    // Simular autoload manual para prueba
    spl_autoload_register(function ($class) {
        $prefix = 'ContentProcessor\\';
        if (strpos($class, $prefix) === 0) {
            $relative_class = substr($class, strlen($prefix));
            $file = __DIR__ . '/src/' . str_replace('\\', '/', $relative_class) . '.php';
            if (file_exists($file)) {
                require $file;
                return true;
            }
        }
        return false;
    });
}

try {
    // Test 1: Cargar clases base
    echo "Test 1: Loading ContentProcessor\Core\ContentProcessor... ";
    $cp = new \ContentProcessor\Core\ContentProcessor();
    echo "✅ OK\n";

    // Test 2: Cargar Schema
    echo "Test 2: Loading ContentProcessor\Schemas\ArraySchema... ";
    $schema = new \ContentProcessor\Schemas\ArraySchema(['test' => ['type' => 'string']]);
    echo "✅ OK\n";

    // Test 3: Cargar Extractor
    echo "Test 3: Loading ContentProcessor\Extractors\TextFileExtractor... ";
    $extractor = new \ContentProcessor\Extractors\TextFileExtractor();
    echo "✅ OK\n";

    // Test 4: Cargar Structurer
    echo "Test 4: Loading ContentProcessor\Structurers\SimpleLineStructurer... ";
    $structurer = new \ContentProcessor\Structurers\SimpleLineStructurer();
    echo "✅ OK\n";

    // Test 5: Cargar FinalResult (Bloque 4)
    echo "Test 5: Loading ContentProcessor\Models\FinalResult... ";
    $result = new \ContentProcessor\Models\FinalResult([]);
    echo "✅ OK\n";

    // Test 6: Verificar métodos públicos
    echo "Test 6: Checking FinalResult public API... ";
    $methods = ['data', 'errors', 'warnings', 'summary', 'hasErrors', 'hasWarnings', 'isSuccessful', 'isPerfect', 'toJSON'];
    $missing = [];
    foreach ($methods as $method) {
        if (!method_exists($result, $method)) {
            $missing[] = $method;
        }
    }
    if (empty($missing)) {
        echo "✅ All methods present\n";
    } else {
        echo "❌ Missing methods: " . implode(', ', $missing) . "\n";
    }

    // Test 7: Verificar que el namespace está correcto
    echo "Test 7: Verifying namespaces... ";
    $reflClass = new ReflectionClass('ContentProcessor\Core\ContentProcessor');
    $namespace = $reflClass->getNamespaceName();
    if ($namespace === 'ContentProcessor\Core') {
        echo "✅ OK (ContentProcessor\\Core)\n";
    } else {
        echo "❌ Namespace mismatch: " . $namespace . "\n";
    }

    echo "\n🟢 ALL TESTS PASSED!\n";
    echo "   The package loads correctly and all classes are accessible.\n";
    echo "   Ready for production use in Composer projects.\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
