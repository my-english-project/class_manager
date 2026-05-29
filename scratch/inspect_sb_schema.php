<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();

echo "=== INSPECTING SUPABASE TABLES ===\n";
try {
    $tables = $db->runSql("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    
    foreach ($tables as $t) {
        $tableName = $t['table_name'];
        echo "Table: $tableName\n";
        
        $columns = $db->runSql("
            SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = '$tableName'
            ORDER BY ordinal_position
        ");
        
        foreach ($columns as $c) {
            echo "  - {$c['column_name']} ({$c['data_type']}) - Nullable: {$c['is_nullable']} - Default: {$c['column_default']}\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
