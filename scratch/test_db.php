<?php
/**
 * test_db.php — Prueba de conexión a la base de datos via Supabase REST API
 *
 * Usa cURL + PostgREST (mismo mecanismo que test_sb.php) porque Supabase
 * bloquea conexiones directas a PostgreSQL (5432 / 6543) desde redes externas.
 *
 * Para diagnósticos que requieren permisos elevados usa SUPABASE_SERVICE_ROLE_KEY.
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// ──────────────────────────────────────────────
// 1. Cargar credenciales desde .env
// ──────────────────────────────────────────────
$envPath = dirname(__DIR__) . '/.env';
$env = [];

if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) continue;
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $env[trim($key)] = trim($value);
    }
}

$supabase_url = $env['SUPABASE_URL']         ?? '';
$service_key  = $env['SUPABASE_SERVICE_ROLE_KEY'] ?? '';
$anon_key     = $env['SUPABASE_ANON_KEY']    ?? '';

// Usar service_role para diagnóstico si está configurada correctamente (JWT real)
$is_valid_jwt = static fn(string $k): bool => str_starts_with($k, 'eyJ');
$auth_key = ($is_valid_jwt($service_key)) ? $service_key : $anon_key;

if (empty($supabase_url) || empty($auth_key)) {
    die("<p style='color:red'><strong>ERROR:</strong> Faltan SUPABASE_URL o claves en el archivo .env</p>");
}

// ──────────────────────────────────────────────
// 2. Función auxiliar de petición cURL
// ──────────────────────────────────────────────
function supabaseQuery(string $url, string $authKey): array
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'GET',
        CURLOPT_HTTPHEADER     => [
            'apikey: '        . $authKey,
            'Authorization: Bearer ' . $authKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    return [
        'data'      => json_decode($response, true),
        'http_code' => $httpCode,
        'error'     => $curlError,
        'raw'       => $response,
    ];
}

// ──────────────────────────────────────────────
// 3. Ejecutar diagnóstico
// ──────────────────────────────────────────────
$tests = [
    'docente' => $supabase_url . '/rest/v1/docente?select=matricula,nombre&limit=3',
    'alumno'  => $supabase_url . '/rest/v1/alumno?select=matricula,nombre&limit=3',
    'grupo'   => $supabase_url . '/rest/v1/grupo?select=*&limit=3',
];

$results = [];
foreach ($tests as $table => $url) {
    $results[$table] = supabaseQuery($url, $auth_key);
}

// ──────────────────────────────────────────────
// 4. Renderizar resultado
// ──────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico DB — ClassHub</title>
    <style>
        body { font-family: monospace; background: #0f0f0f; color: #e0e0e0; padding: 2rem; }
        h1   { color: #7dd3fc; }
        h2   { color: #a5f3fc; margin-top: 2rem; }
        .ok  { color: #4ade80; }
        .err { color: #f87171; }
        pre  { background: #1e1e1e; padding: 1rem; border-radius: 6px; overflow: auto; }
        .tag { display: inline-block; padding: 2px 8px; border-radius: 4px; font-weight: bold; }
        .tag.ok  { background: #166534; color: #4ade80; }
        .tag.err { background: #7f1d1d; color: #f87171; }
    </style>
</head>
<body>
    <h1>🔬 Diagnóstico de Base de Datos — ClassHub</h1>
    <p>Método de conexión: <strong>Supabase REST API (PostgREST)</strong></p>
    <p>Endpoint base: <code><?= htmlspecialchars($supabase_url) ?></code></p>

    <?php foreach ($results as $table => $result): ?>
        <?php $ok = $result['http_code'] >= 200 && $result['http_code'] < 300 && !$result['error']; ?>
        <h2>Tabla: <code><?= htmlspecialchars($table) ?></code>
            <span class="tag <?= $ok ? 'ok' : 'err' ?>">
                HTTP <?= $result['http_code'] ?> — <?= $ok ? '✓ OK' : '✗ FALLO' ?>
            </span>
        </h2>

        <?php if ($result['error']): ?>
            <p class="err">Error cURL: <?= htmlspecialchars($result['error']) ?></p>
        <?php elseif ($ok): ?>
            <pre><?= htmlspecialchars(json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        <?php else: ?>
            <p class="err">Respuesta inesperada:</p>
            <pre><?= htmlspecialchars($result['raw']) ?></pre>
        <?php endif; ?>
    <?php endforeach; ?>
</body>
</html>
