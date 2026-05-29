<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';

$db = Database::getConnection();

echo "=== ACTIVIDAD ===\n";
$stmt = $db->query("SELECT * FROM actividad LIMIT 10");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "=== EXAMEN ESCRITO ===\n";
$stmt = $db->query("SELECT * FROM examen_escrito LIMIT 10");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
