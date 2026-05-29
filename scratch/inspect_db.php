<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';

$db = Database::getConnection();

echo "=== CICLO SCHEMA ===\n";
$stmt = $db->query("DESCRIBE ciclo");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "=== ALL CICLOS ===\n";
$stmt2 = $db->query("SELECT * FROM ciclo");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
