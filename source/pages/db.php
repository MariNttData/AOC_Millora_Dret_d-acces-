<?php
function get_oracle_connection() {

    $host = 'ora12pro-scan.aoc.cat';
    $port = '1521';
    $service = 'ORA12PRO';
    $user = 'vistaOTpro';
    $pass = 'Phuthee6jeec';

    $tns = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$port))(CONNECT_DATA=(SERVICE_NAME=$service)))";
    $conn = oci_connect($user, $pass, $tns);
    if (!$conn) {
        $e = oci_error();
        throw new Exception('Oracle connect error: ' . ($e['message'] ?? 'unknown'));
    } else {
         error_log('Connected to Oracle database successfully.');
    }
    return $conn;   
}

?>