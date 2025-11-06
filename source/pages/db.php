<?php
function get_oracle_connection() {
    // Establecer charset para la conexión
    putenv('NLS_LANG=SPANISH_SPAIN.AL32UTF8');

    $host = 'ora12pro-scan.aoc.cat';
    $port = '1521';
    $service = 'ORA12PRO';
    $user = 'vistaOTpro';
    $pass = 'Phuthee6jeec';

    $tns = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SERVICE_NAME=$service)))";
    if (function_exists('oci_connect')) {
        $conn = oci_connect($user, $pass, $tns, 'AL32UTF8');
        echo('oci_connect used with AL32UTF8.');
    } else {
        echo('OCI8 extension is not available: please install/enable the php_oci8 extension or use an alternative driver.');
        $conn = null;
    }

    if (!$conn) {
        $e = (function_exists('oci_error') ? oci_error() : null);
        echo('Error to connect to Oracle database' . ($e ? ': ' . print_r($e, true) : ''));
    } else {
        echo('Connected to Oracle database successfully.');
    }
    return $conn;   
}

?>