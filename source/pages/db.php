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

function get_oracle_connection() {
    // Establecer charset para la conexión
    putenv('NLS_LANG=SPANISH_SPAIN.AL32UTF8');

    // Obtener variables de la base de datos del .env
    $host = $_ENV['DB_HOST'] ?? 'NO DEFINIDO';
    $port = $_ENV['DB_PORT'] ?? 'NO DEFINIDO';
    $service = $_ENV['DB_SERVICE'] ?? 'NO DEFINIDO';
    $user = $_ENV['DB_USER'] ?? 'NO DEFINIDO';
    $pass = $_ENV['DB_PASS'] ?? 'NO DEFINIDO';

    $tns = "(DESCRIPTION=(LOAD_BALANCE=on)(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SERVICE_NAME=$service)))";
    if (function_exists('oci_connect')) {
        $conn = oci_connect($user, $pass, $tns, 'AL32UTF8');
    } else {
        echo('OCI8 extension is not available: please install/enable the php_oci8 extension or use an alternative driver.');
        $conn = null;
    }

    if (!$conn) {
        $e = (function_exists('oci_error') ? oci_error() : null);
        echo('Error to connect to Oracle database' . ($e ? ': ' . print_r($e, true) : ''));
    } 
    return $conn;   
}

?>