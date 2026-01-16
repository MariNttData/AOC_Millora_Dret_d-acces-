<?php
/**
 * Script de prueba de conexión a Dynamics CRM
 */

require_once 'config.php';

echo "<h2>Test de Conexión a Dynamics CRM</h2>";
echo "<hr>";

// 1. Verificar que las constantes están definidas
echo "<h3>1. Verificando constantes de configuración:</h3>";
echo "<pre>";
echo "MICROSOFT_ENDPOINT: " . MICROSOFT_ENDPOINT . "\n";
echo "MICROSOFT_TENANT_ID: " . MICROSOFT_TENANT_ID . "\n";
echo "MICROSOFT_CLIENT_ID: " . MICROSOFT_CLIENT_ID . "\n";
echo "MICROSOFT_SCOPE: " . MICROSOFT_SCOPE . "\n";
echo "DYNAMICS_ENDPOINT: " . DYNAMICS_ENDPOINT . "\n";
echo "</pre>";
echo "✓ Constantes cargadas correctamente\n<br><hr>";

// 2. Intenta obtener un token de acceso
echo "<h3>2. Intentando obtener token de acceso de Microsoft:</h3>";

$tokenUrl = MICROSOFT_ENDPOINT . "/" . MICROSOFT_TENANT_ID . "/oauth2/v2.0/token";
echo "URL: " . $tokenUrl . "<br>";

$postData = [
    'client_id' => MICROSOFT_CLIENT_ID,
    'client_secret' => MICROSOFT_CLIENT_SECRET,
    'scope' => MICROSOFT_SCOPE,
    'grant_type' => 'client_credentials'
];

echo "POST Data: <pre>";
echo "client_id: " . MICROSOFT_CLIENT_ID . "\n";
echo "client_secret: [OCULTO]\n";
echo "scope: " . MICROSOFT_SCOPE . "\n";
echo "grant_type: client_credentials\n";
echo "</pre>";

$accessToken = null;

// Intentar con CURL si está disponible
if (function_exists('curl_init')) {
    echo "<span style='color: blue;'>ℹ Usando CURL para la autenticación</span><br>";
    
    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    echo "HTTP Code: " . $httpCode . "<br>";
    echo "Response Raw: <pre>" . htmlspecialchars($response) . "</pre>";
    
    if ($curlError) {
        echo "<span style='color: red;'>✗ Error CURL: " . htmlspecialchars($curlError) . "</span><br>";
    } else if ($httpCode == 200 && $response) {
        $tokenData = json_decode($response, true);
        if (isset($tokenData['access_token'])) {
            $accessToken = $tokenData['access_token'];
        }
    }
} else {
    // Alternativa sin CURL - usando stream o sockets
    echo "<span style='color: orange;'>⚠ CURL no disponible. Intentando alternativas...</span><br>";
    echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? 'Habilitado' : 'Deshabilitado') . "<br>";
    
    if (!ini_get('allow_url_fopen')) {
        echo "<span style='color: red;'>✗ allow_url_fopen está deshabilitado en php.ini</span><br>";
        echo "Habilita allow_url_fopen = On en tu php.ini<br>";
    } else {
        $postDataString = http_build_query($postData);
        
        // Usar fsockopen para conexión directa por socket
        echo "Intentando conexión directa por socket...<br>";
        
        $host = 'login.microsoftonline.com';
        $port = 443;
        $path = '/37a8a0b9-1874-4e5d-b1f5-11040c1c07fc/oauth2/v2.0/token';
        
        // Crear socket seguro
        $errno = 0;
        $errstr = '';
        
        $fp = @fsockopen('ssl://' . $host, $port, $errno, $errstr, 10);
        
        if (!$fp) {
            echo "<span style='color: red;'>✗ fsockopen falló: $errstr ($errno)</span><br>";
            echo "Esto significa que:<br>";
            echo "1. OpenSSL no está habilitado en PHP<br>";
            echo "2. O hay un problema de conectividad/firewall<br>";
            echo "<span style='color: blue;'>ℹ Solución: Habilita CURL en tu servidor PHP</span><br>";
        } else {
            // Conexión exitosa
            echo "✓ Conexión al servidor establecida<br>";
            
            // Construir la solicitud HTTP/1.1
            $out = "POST $path HTTP/1.1\r\n";
            $out .= "Host: $host\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "Content-Length: " . strlen($postDataString) . "\r\n";
            $out .= "Connection: Close\r\n\r\n";
            $out .= $postDataString;
            
            fwrite($fp, $out);
            
            // Leer la respuesta
            $response = '';
            $headers_done = false;
            $httpCode = 0;
            
            while (!feof($fp)) {
                $line = fgets($fp, 128);
                if (!$headers_done) {
                    if ($line === "\r\n" || $line === "\n") {
                        $headers_done = true;
                    } else if (strpos($line, 'HTTP/') === 0) {
                        preg_match('/\d{3}/', $line, $matches);
                        if (!empty($matches)) {
                            $httpCode = intval($matches[0]);
                        }
                    }
                } else {
                    $response .= $line;
                }
            }
            fclose($fp);
            
            echo "HTTP Code: " . $httpCode . "<br>";
            echo "Response Raw: <pre>" . htmlspecialchars(substr($response, 0, 1000)) . "</pre>";
            
            if ($httpCode == 200 && $response) {
                $tokenData = json_decode($response, true);
                if (isset($tokenData['access_token'])) {
                    $accessToken = $tokenData['access_token'];
                    echo "<span style='color: green;'>✓ Token obtenido correctamente</span><br>";
                } else {
                    echo "<span style='color: red;'>✗ Error: No hay access_token en la respuesta</span><br>";
                    if (isset($tokenData['error'])) {
                        echo "Error del servidor: " . $tokenData['error'] . " - " . $tokenData['error_description'] . "<br>";
                    }
                }
            } else {
                echo "<span style='color: red;'>✗ Error HTTP " . $httpCode . "</span><br>";
            }
        }
    }
}

// 3. Si obtuvimos el token, probar WhoAmI
if ($accessToken) {
    echo "<span style='color: green;'>✓ Token obtenido exitosamente</span><br>";
    echo "Tipo de token: " . (isset($tokenData['token_type']) ? $tokenData['token_type'] : 'Bearer') . "<br>";
    echo "Expires in: " . (isset($tokenData['expires_in']) ? $tokenData['expires_in'] : 'N/A') . " segundos<br>";
    
    echo "<hr>";
    echo "<h3>3. Realizando consulta WhoAmI a Dynamics:</h3>";
    
    $whoAmIUrl = DYNAMICS_ENDPOINT . "/api/data/v9.2/WhoAmI";
    echo "URL: " . $whoAmIUrl . "<br>";
    
    $response = null;
    $httpCode = 0;
    
    // Intentar con CURL si está disponible
    if (function_exists('curl_init')) {
        $ch = curl_init($whoAmIUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } else {
        // Alternativa sin CURL
        $options = [
            'http' => [
                'header'  => "Authorization: Bearer " . $accessToken . "\r\nAccept: application/json\r\n",
                'method'  => 'GET',
                'timeout' => 10
            ]
        ];
        
        $context = stream_context_create($options);
        $response = @file_get_contents($whoAmIUrl, false, $context);
        
        if (isset($http_response_header)) {
            $httpLine = $http_response_header[0];
            preg_match('/\d{3}/', $httpLine, $matches);
            if (!empty($matches)) {
                $httpCode = intval($matches[0]);
            }
        }
    }
    
    if ($response === false) {
        echo "<span style='color: red;'>✗ Error al conectar a Dynamics WhoAmI</span><br>";
    } else {
        echo "HTTP Code: " . $httpCode . "<br>";
        echo "Response Raw: <pre>" . htmlspecialchars($response) . "</pre>";
        
        if ($httpCode == 200) {
            $whoAmIData = json_decode($response, true);
            if ($whoAmIData) {
                echo "<span style='color: green;'>✓ Conexión a Dynamics CRM exitosa</span><br>";
                echo "<pre>";
                echo "UserId: " . (isset($whoAmIData['UserId']) ? $whoAmIData['UserId'] : 'N/A') . "\n";
                echo "BusinessUnitId: " . (isset($whoAmIData['BusinessUnitId']) ? $whoAmIData['BusinessUnitId'] : 'N/A') . "\n";
                echo "OrganizationId: " . (isset($whoAmIData['OrganizationId']) ? $whoAmIData['OrganizationId'] : 'N/A') . "\n";
                echo "</pre>";
            } else {
                echo "<span style='color: red;'>✗ Error al decodificar respuesta JSON</span><br>";
            }
        } else {
            echo "<span style='color: red;'>✗ Error en WhoAmI. Código: " . $httpCode . "</span><br>";
        }
    }
} else {
    echo "<span style='color: red;'>✗ No se pudo obtener el token. Verifica las credenciales en config.php</span><br>";
}

echo "<hr>";
echo "<a href='index.php'>Volver al inicio</a>";
?>