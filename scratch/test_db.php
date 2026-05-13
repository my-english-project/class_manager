<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "1. Iniciando script...\n";
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';

echo "2. Cargando configuración...\n";
echo "3. Intentando conectar a Supabase (Pooler IPv4)...\n";

try {
    $db = Database::getConnection();
    $stmt = $db->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "¡Conexión EXITOSA!\n";
    echo "Versión de PostgreSQL: " . $version . "\n";
} catch (Exception $e) {
    echo "ERROR de conexión: " . $e->getMessage() . "\n";
}
