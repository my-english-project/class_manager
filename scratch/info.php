<?php
echo "<h1>Diagnóstico de ClassHub</h1>";
echo "Versión de PHP: " . phpversion() . "<br>";
echo "Sistema Operativo: " . PHP_OS . "<br>";

if (extension_loaded('pdo_pgsql')) {
    echo "<p style='color:green;'>✅ Extensión <strong>pdo_pgsql</strong> está CARGADA.</p>";
} else {
    echo "<p style='color:red;'>❌ Extensión <strong>pdo_pgsql</strong> NO está cargada.</p>";
}

if (extension_loaded('pgsql')) {
    echo "<p style='color:green;'>✅ Extensión <strong>pgsql</strong> está CARGADA.</p>";
} else {
    echo "<p style='color:red;'>❌ Extensión <strong>pgsql</strong> NO está cargada.</p>";
}

echo "<h2>Variables de Entorno Detectadas:</h2>";
$base_path = dirname(__DIR__);
define('BASE_PATH', $base_path);
$env_path = $base_path . '/.env';
if (file_exists($env_path)) {
    echo "✅ Archivo .env encontrado en: $env_path<br>";
    $content = file_get_contents($env_path);
    echo "<h3>Valores leídos del .env:</h3>";
    $lines = explode("\n", $content);
    foreach ($lines as $line) {
        if (str_contains($line, 'DB_HOST') || str_contains($line, 'DB_USER') || str_contains($line, 'DB_PORT')) {
            echo htmlspecialchars($line) . "<br>";
        }
    }
} else {
    echo "❌ Archivo .env NO encontrado en: $env_path<br>";
}

echo "<h2>Prueba de Conexión Real:</h2>";
try {
    require_once $base_path . '/config/database.php';
    $db = Database::getConnection();
    echo "<p style='color:green;'>✅ ¡CONEXIÓN A LA DB EXITOSA DESDE EL NAVEGADOR!</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ FALLO DE CONEXIÓN: " . htmlspecialchars($e->getMessage()) . "</p>";
}
