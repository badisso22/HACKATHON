<?php
function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception(".env file not found at: $path");
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (strpos($value, '"') === 0 || strpos($value, "'") === 0) {
            $value = substr($value, 1, -1);
        }
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}
$envPath = __DIR__ . '/../.env';
try {
    loadEnv($envPath);
} catch (Exception $e) {
    error_log("Error loading .env file: " . $e->getMessage());
}
function getEnvVar($key, $default = null) {
    return isset($_ENV[$key]) ? $_ENV[$key] : (getenv($key) ?: $default);
}
