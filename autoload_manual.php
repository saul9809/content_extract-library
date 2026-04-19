<?php

/**
 * AUTOLOADER MANUAL (temporal for test sin composer)
 * 
 * En producción, usar the autoloader de Composer generado automáticamente.
 * Este es solo para demostración cuando composer install aún no termina.
 */

spl_autoload_register(function ($class) {
    // Namespace base
    $prefix = 'ContentProcessor\\';

    if (strpos($class, $prefix) !== 0) {
        return;
    }

    // Remueve el prefix del namespace
    $relative_class = substr($class, strlen($prefix));

    // Convierte namespace a ruta
    $file = __DIR__ . '/src/' . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
