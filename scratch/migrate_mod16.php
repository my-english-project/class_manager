<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getConnection();
    echo "=== MIGRACIÓN MODIFICACIÓN 16 - BASE DE DATOS LOCAL ===\n";

    // 1. Crear tabla examen_oral_tema
    $db->exec("
    CREATE TABLE IF NOT EXISTS examen_oral_tema (
      id_oral_topic INT AUTO_INCREMENT PRIMARY KEY,
      nombre VARCHAR(120) NOT NULL,
      markdown_text TEXT NOT NULL,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Tabla 'examen_oral_tema' creada o ya existente.\n";

    // 2. Crear tabla examen_oral_texto
    $db->exec("
    CREATE TABLE IF NOT EXISTS examen_oral_texto (
      id_oral_text INT AUTO_INCREMENT PRIMARY KEY,
      id_oral_topic INT NOT NULL,
      parrafo_num INT NOT NULL,
      texto TEXT NOT NULL,
      FOREIGN KEY (id_oral_topic) REFERENCES examen_oral_tema(id_oral_topic) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Tabla 'examen_oral_texto' creada o ya existente.\n";

    // 3. Agregar columna id_oral_text a examen_oral
    $stmt = $db->query("SHOW COLUMNS FROM examen_oral LIKE 'id_oral_text'");
    if ($stmt->rowCount() === 0) {
        $db->exec("ALTER TABLE examen_oral ADD COLUMN id_oral_text INT DEFAULT NULL");
        $db->exec("ALTER TABLE examen_oral ADD CONSTRAINT fk_eo_text FOREIGN KEY (id_oral_text) REFERENCES examen_oral_texto(id_oral_text) ON DELETE SET NULL");
        echo "✓ Columna 'id_oral_text' agregada exitosamente a la tabla 'examen_oral'.\n";
    } else {
        echo "✓ Columna 'id_oral_text' ya existe en la tabla 'examen_oral'.\n";
    }

    echo "=== MIGRACIÓN COMPLETADA CON ÉXITO ===\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
