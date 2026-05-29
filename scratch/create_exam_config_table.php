<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    echo "=== CREANDO TABLA DE CONFIGURACION DE EXAMENES ===\n";
    
    $sql = "
    CREATE TABLE IF NOT EXISTS examen_config (
      id_config INT AUTO_INCREMENT PRIMARY KEY,
      id_grupo INT NOT NULL,
      parcial TINYINT NOT NULL,
      ciclo VARCHAR(10) NOT NULL,
      generado TINYINT(1) NOT NULL DEFAULT 0,
      UNIQUE KEY uq_group_parcial_cycle (id_grupo, parcial, ciclo),
      FOREIGN KEY (id_grupo) REFERENCES grupo(id_grupo) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($sql);
    echo "✓ Tabla 'examen_config' creada o ya existente.\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
