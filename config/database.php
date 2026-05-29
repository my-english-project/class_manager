<?php
/**
 * Class Manager — Database Layer (Local MySQL Connection)
 *
 * Provides a standardized PDO connection to the local MySQL server.
 * Completely compatible with the existing controllers using standard PDO methods.
 */

// ─────────────────────────────────────────────────────────────────────────────
// Load Environment Variables (.env)
// ─────────────────────────────────────────────────────────────────────────────
if (!function_exists('loadEnvFile')) {
  function loadEnvFile(string $path): array
  {
    if (!file_exists($path))
      return [];

    $env = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
      $line = trim($line);
      if (empty($line) || str_starts_with($line, '#'))
        continue;
      [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
      $env[trim($key)] = trim($value);
    }
    return $env;
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Subclase de PDO para soportar consultas de utilerías (runSql)
// ─────────────────────────────────────────────────────────────────────────────
class ClassManagerPDO extends PDO
{
  /**
   * Helper para ejecutar consultas crudas y obtener array asociativo directo.
   * Mantiene compatibilidad con scripts de utilería anteriores.
   */
  public function runSql(string $sql): array
  {
    $trimmed = trim($sql);
    $upper = strtoupper($trimmed);

    // Si no es un SELECT, simplemente ejecutar sin esperar resultados
    if (!str_starts_with($upper, 'SELECT') && !str_starts_with($upper, 'SHOW')) {
      $this->exec($sql);
      return [];
    }

    $stmt = $this->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// Database Singleton
// ─────────────────────────────────────────────────────────────────────────────
class Database
{
  private static ?ClassManagerPDO $instance = null;

  /**
   * Returns the standardized local MySQL PDO connection.
   * Compatible with: Database::getConnection()
   */
  public static function getConnection(): ClassManagerPDO
  {
    if (self::$instance === null) {
      $isLocal = false;
      if (php_sapi_name() === 'cli') {
        $isLocal = true;
      } else {
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        $httpHost = $_SERVER['HTTP_HOST'] ?? '';
        if ($serverName === 'localhost' || $serverName === '127.0.0.1' || str_contains($httpHost, 'localhost') || str_contains($httpHost, '127.0.0.1')) {
          $isLocal = true;
        }
      }

      if ($isLocal) {
        $envPath = defined('BASE_PATH') ? BASE_PATH . '/.env' : dirname(__DIR__) . '/.env';
        $env = loadEnvFile($envPath);

        $host = $env['DB_HOST'] ?? 'localhost';
        $port = $env['DB_PORT'] ?? '3306';
        $dbName = $env['DB_NAME'] ?? 'uts';
        $user = $env['DB_USER'] ?? 'root';
        $pass = $env['DB_PASS'] ?? 'kjb2a0p';
      } else {
        $host = 'espacioenlinea.com';
        $port = '3306';
        $dbName = 'espacio2_uts';
        $user = 'espacio2_admin';
        $pass = 'Calemis19Uts+';
      }

      try {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
        self::$instance = new ClassManagerPDO($dsn, $user, $pass, [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES => false,
        ]);
      } catch (PDOException $e) {
        $envMessage = $isLocal ? "MySQL local" : "MySQL remoto ($host)";
        throw new RuntimeException("Error al conectar con la base de datos $envMessage: " . $e->getMessage());
      }
    }

    return self::$instance;
  }

  private function __construct()
  {
  }
  private function __clone()
  {
  }
}
