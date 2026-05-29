<?php
/**
 * Class Manager — Application Entry Point
 * 
 * Single entry point that bootstraps the session, includes the router,
 * and dispatches the request to the appropriate controller + view.
 */

session_start();

// Ensure proper UTF-8 encoding across the application
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');

// Base path for includes
define('BASE_PATH', __DIR__);
define('APP_NAME', 'Class Manager');
define('APP_VERSION', '1.0.0');

// Include core dependencies
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/router.php';

// Auto-load the teacher for v1.0 prototype (RF-01: single-button access)
if (!isset($_SESSION['docente']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] && ($_SESSION['usuario']['rol'] ?? '') !== 'alumno') {
    try {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM docente WHERE matricula = 'ADMIN01' LIMIT 1");
        $_SESSION['docente'] = $stmt->fetch();
    } catch (Exception $e) {
        // Database not ready yet — will show login
    }
}

// Compute active cycle if not set (latest registered cycle)
if (empty($_SESSION['ciclo_activo'])) {
    try {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT codigo FROM ciclo WHERE activo = 1 LIMIT 1");
        $lastCycle = $stmt->fetchColumn();
        if ($lastCycle) {
            $_SESSION['ciclo_activo'] = $lastCycle;
        } else {
            // Fallback to latest registered cycle if no cycle is active
            $stmtLatest = $db->query("SELECT codigo FROM ciclo ORDER BY id_ciclo DESC LIMIT 1");
            $lastCycleLatest = $stmtLatest->fetchColumn();
            if ($lastCycleLatest) {
                $_SESSION['ciclo_activo'] = $lastCycleLatest;
            } else {
                // Fallback to date-based if table is empty
                $year = date('Y');
                $formatYear = substr($year, 2);
                $month = (int)date('n');
                if ($month >= 1 && $month <= 4) {
                    $_SESSION['ciclo_activo'] = $formatYear . 'C1';
                } elseif ($month >= 5 && $month <= 8) {
                    $_SESSION['ciclo_activo'] = $formatYear . 'C2';
                } else {
                    $_SESSION['ciclo_activo'] = $formatYear . 'C3';
                }
            }
        }
    } catch (Exception $e) {
        // Database not ready yet
        $year = date('Y');
        $formatYear = substr($year, 2);
        $_SESSION['ciclo_activo'] = $formatYear . 'C1';
    }
}

// Dispatch the request
$page = $_GET['page'] ?? 'login';
$action = $_GET['action'] ?? null;

// Handle API actions (AJAX calls)
if ($action) {
    handleAction($action);
    exit;
}

// Handle page routing
routePage($page);
