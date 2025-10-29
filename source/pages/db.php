<?php
// db.php - helper to create DB connections
// Update the DSNs, users and passwords below to match your environment.

function get_oracle_connection() {
    // Example for Oracle using oci8 (if available)
    // put your host, port and service name
    $host = 'ora12pro-scan.aoc.cat';
    $port = '1521';
    $service = 'ORA12PRO';
    $user = 'vistaOTpro';
    $pass = 'Phuthee6jeec';

    // Guard: ensure OCI8 extension is available before calling oci_connect
    if (!function_exists('oci_connect')) {
        // Provide actionable instructions instead of letting PHP throw a fatal error
        $msg  = "OCI8 extension is not available in this PHP installation. ";
        $msg .= "Enable the OCI8 extension (php_oci8) or install Oracle Instant Client. \n";
        $msg .= "On Windows typically: install Oracle Instant Client, place the DLLs on PATH, enable the appropriate php_oci8_*.dll in php.ini and restart the webserver.\n";
        $msg .= "Alternatively use PDO_OCI if available or run this code on a server with OCI8.\n";
        throw new Exception($msg);
    }

    $tns = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SERVICE_NAME=$service)))";
    $conn = oci_connect($user, $pass, $tns);
    if (!$conn) {
        $e = oci_error();
        throw new Exception('Oracle connect error: ' . ($e['message'] ?? 'unknown'));
    }
    return $conn;   
    /*test*/
}

?>