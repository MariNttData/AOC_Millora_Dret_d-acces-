<?php
// Cargar variables del archivo .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '/*') === 0 || strpos(trim($line), '*/') === 0) {
            continue;
        }
        // Parsear variables en formato KEY=VALUE
        if (strpos($line, '=') !== false && strpos(trim($line), '=') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Endpoints y credenciales de Microsoft/Dynamics (Leer .env)
define('MICROSOFT_ENDPOINT', $_ENV['MICROSOFT_ENDPOINT'] ?? 'NO DEFINIDO');
define('MICROSOFT_TENANT_ID', $_ENV['MICROSOFT_TENANT_ID'] ?? 'NO DEFINIDO');
define('MICROSOFT_CLIENT_ID', $_ENV['MICROSOFT_CLIENT_ID'] ?? 'NO DEFINIDO');
define('MICROSOFT_CLIENT_SECRET', $_ENV['MICROSOFT_CLIENT_SECRET'] ?? 'NO DEFINIDO');
define('MICROSOFT_SCOPE', $_ENV['MICROSOFT_SCOPE'] ?? 'NO DEFINIDO');
define('DYNAMICS_ENDPOINT', $_ENV['DYNAMICS_ENDPOINT'] ?? 'NO DEFINIDO');
?>