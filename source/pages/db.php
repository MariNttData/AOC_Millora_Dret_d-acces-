<?php
require_once __DIR__ . '/../variables.env';

function get_oracle_connection() {
    global $DB_HOST, $DB_PORT, $DB_SERVICE, $DB_USER, $DB_PASS;
    // Establecer charset para la conexión
    putenv('NLS_LANG=SPANISH_SPAIN.AL32UTF8');

    // Leer variables.env
    $host = $DB_HOST;
    $port = $DB_PORT;
    $service = $DB_SERVICE;
    $user = $DB_USER;
    $pass = $DB_PASS;

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