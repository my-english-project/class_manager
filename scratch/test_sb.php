<?php

// 1. Configuración de credenciales
$supabase_url = 'https://auxkjebmtojjfkodeqdm.supabase.co';
$supabase_key = 'sb_publishable_1KGcLRppqFdXySiiErYPCg_uAr3PAHa';
$table_name = 'alumno';

// 2. Endpoint para consultar la tabla (limite 1 para la prueba)
$url = $supabase_url . "/rest/v1/" . $table_name . "?select=*&limit=1";

// 3. Inicializar cURL
$ch = curl_init();

curl_setopt_array($ch, [
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => [
    "apikey: " . $supabase_key,
    "Authorization: Bearer " . $supabase_key,
    "Content-Type: application/json"
  ],
]);

// 4. Ejecutar la petición
$response = curl_exec($ch);
$err = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// curl_close($ch); // Deprecated in PHP 8.5+

// 5. Mostrar resultados
echo "<h2>Resultado de Conexión a Supabase</h2>";

if ($err) {
  echo "<p style='color:red;'><strong>Error de cURL:</strong> " . $err . "</p>";
} else {
  if ($http_code >= 200 && $http_code < 300) {
    echo "<p style='color:green;'><strong>¡Conexión Exitosa!</strong> Código HTTP: $http_code</p>";
    echo "<pre>Datos recibidos: " . print_r(json_decode($response, true), true) . "</pre>";
  } else {
    echo "<p style='color:orange;'><strong>Error de API:</strong> Código HTTP $http_code</p>";
    echo "<pre>Respuesta de Supabase: $response</pre>";
  }
}
?>