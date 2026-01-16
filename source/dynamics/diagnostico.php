<?php
/**
 * Diagnóstico de extensiones PHP y cómo habilitarlas
 */

echo "<h1>Diagnóstico de Extensiones PHP</h1>";
echo "<hr>";

// Mostrar versión de PHP
echo "<h3>Información del Sistema:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Sistema Operativo: " . php_uname() . "<br>";
echo "SAPI: " . php_sapi_name() . "<br>";

// Archivo de configuración
echo "<h3>Archivo php.ini:</h3>";
$iniPath = php_ini_loaded_file();
if ($iniPath) {
    echo "Ubicación: <strong>" . $iniPath . "</strong><br>";
} else {
    echo "No se encontró archivo php.ini<br>";
}

echo "<hr>";

// Verificar extensiones importantes
echo "<h3>Estado de Extensiones Importantes:</h3>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Extensión</th><th>Habilitada</th><th>Acciones</th></tr>";

$extensions = [
    'curl' => 'php_curl.dll',
    'openssl' => 'php_openssl.dll',
    'sockets' => 'php_sockets.dll'
];

foreach ($extensions as $ext => $dll) {
    $enabled = extension_loaded($ext);
    $status = $enabled ? '<span style="color: green;">✓ SI</span>' : '<span style="color: red;">✗ NO</span>';
    
    echo "<tr>";
    echo "<td><strong>" . strtoupper($ext) . "</strong></td>";
    echo "<td>" . $status . "</td>";
    echo "<td>";
    if (!$enabled) {
        echo "Habilitar: <code>extension=" . $dll . "</code>";
    } else {
        echo "Ya habilitada";
    }
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h3>Pasos para habilitar CURL:</h3>";
echo "<ol>";
echo "<li><strong>Abre el archivo php.ini</strong> ubicado en: <br><code>" . ($iniPath ? $iniPath : 'C:\\xampp\\php\\php.ini (o similar)') . "</code></li>";
echo "<li><strong>Busca estas líneas</strong> (pueden estar comentadas con ; al inicio):<br>";
echo "<pre>";
echo ";extension=curl\n";
echo ";extension=openssl\n";
echo "</pre>";
echo "</li>";
echo "<li><strong>Descomenta las líneas</strong> quitando el ; al inicio:<br>";
echo "<pre>";
echo "extension=curl\n";
echo "extension=openssl\n";
echo "</pre>";
echo "</li>";
echo "<li><strong>Guarda el archivo php.ini</strong></li>";
echo "<li><strong>Reinicia el servidor PHP/Apache</strong>";
echo "<ul>";
echo "<li>Si usas XAMPP: Para los servicios en el panel de control</li>";
echo "<li>Si usas IIS: Reinicia IIS</li>";
echo "<li>Si usas servidor built-in: Reinicia PHP</li>";
echo "</ul>";
echo "</li>";
echo "<li><strong>Recarga esta página</strong> para verificar que CURL está habilitado</li>";
echo "</ol>";

echo "<hr>";
echo "<h3>Ubicaciones comunes de php.ini:</h3>";
echo "<ul>";
echo "<li>XAMPP: <code>C:\\xampp\\php\\php.ini</code></li>";
echo "<li>WAMP: <code>C:\\wamp\\bin\\php\\php[versión]\\php.ini</code></li>";
echo "<li>LAMP: <code>/etc/php/php.ini</code> o <code>/usr/local/etc/php.ini</code></li>";
echo "<li>Servidor Web: Depende de tu instalación</li>";
echo "</ul>";

echo "<hr>";
echo "<a href='test-dynamics.php'>Volver al test de Dynamics</a>";
?>
