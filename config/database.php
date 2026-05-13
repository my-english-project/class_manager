<?php
/**
 * ClassHub — Database Connection (PDO Singleton)
 * 
 * Configurada para conectar a Supabase (PostgreSQL).
 */

class Database
{
    private static ?PDO $instance = null;

    /**
     * Retorna una instancia PDO única (singleton).
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            // Cargar variables de entorno desde .env manualmente para v1.0
            $env = self::loadEnv(BASE_PATH . '/.env');
            
            $host = $env['DB_HOST'] ?? 'localhost';
            $port = $env['DB_PORT'] ?? '5432';
            $db   = $env['DB_NAME'] ?? 'postgres';
            $user = $env['DB_USER'] ?? 'postgres';
            $pass = $env['DB_PASS'] ?? '';

            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $host,
                $port,
                $db
            );

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => 5, // 5 segundos de timeout
                ]);
            } catch (PDOException $e) {
                throw new Exception("Error de conexión a Supabase: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Carga básica de archivo .env
     */
    private static function loadEnv(string $path): array
    {
        if (!file_exists($path)) return [];
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $env = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $env[trim($parts[0])] = trim($parts[1]);
            }
        }
        return $env;
    }

    /** Prevenir clonación e instanciación directa */
    private function __construct() {}
    private function __clone() {}
}
