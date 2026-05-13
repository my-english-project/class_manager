<?php
/**
 * ClassHub — Simple Router
 * 
 * Maps page slugs to views and action slugs to controller methods.
 * Keeps routing logic centralized and separate from business logic.
 */

/**
 * Route a page request to the appropriate view.
 * Validates against a whitelist to prevent directory traversal.
 */
function routePage(string $page): void
{
    $publicPages = ['login', 'consulta'];
    $protectedPages = [
        'home', 'alumnos', 'attendance',
        'write_exam', 'oral_exam', 'portfolio', 'homework',
        'exam', 'sito', 'sesiones'
    ];

    $requiresGroup = [
        'alumnos', 'attendance', 'write_exam', 'oral_exam', 
        'portfolio', 'homework', 'exam', 'sito', 'sesiones'
    ];

    // Redirect to login if not authenticated and trying to access a protected page
    if (in_array($page, $protectedPages) && empty($_SESSION['logged_in'])) {
        header('Location: index.php?page=login');
        exit;
    }

    // Require group for module views
    if (in_array($page, $requiresGroup) && empty($_SESSION['grupo_activo'])) {
        header('Location: index.php?page=home');
        exit;
    }

    // Determine which view to load
    if (in_array($page, $publicPages) || in_array($page, $protectedPages)) {
        $viewFile = BASE_PATH . '/views/' . $page . '.php';
    } else {
        $viewFile = BASE_PATH . '/views/login.php';
    }

    if (file_exists($viewFile)) {
        // Load the appropriate controller data before rendering
        $controllerData = loadControllerData($page);
        extract($controllerData);

        // Render with layout for protected pages
        if (in_array($page, $protectedPages)) {
            $currentPage = $page;
            include BASE_PATH . '/views/layout/header.php';
            include $viewFile;
            include BASE_PATH . '/views/layout/footer.php';
        } else {
            include $viewFile;
        }
    } else {
        http_response_code(404);
        echo '<h1>404 — Página no encontrada</h1>';
    }
}

/**
 * Load controller data for a given page.
 * Returns an associative array that will be extracted in the view scope.
 */
function loadControllerData(string $page): array
{
    $data = [];

    $controllerMap = [
        'home'       => 'HomeController',
        'alumnos'    => 'AlumnoController',
        'sesiones'   => 'SesionController',
        'attendance' => 'AsistenciaController',
        'write_exam' => 'CalificacionController',
        'oral_exam'  => 'CalificacionController',
        'portfolio'  => 'CalificacionController',
        'homework'   => 'CalificacionController',
        'exam'       => 'CalificacionController',
        'sito'       => 'CalificacionController',
        'consulta'   => 'ConsultaController',
    ];

    if (isset($controllerMap[$page])) {
        $controllerFile = BASE_PATH . '/controllers/' . $controllerMap[$page] . '.php';
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controllerClass = $controllerMap[$page];
            if (class_exists($controllerClass) && method_exists($controllerClass, 'index')) {
                $controller = new $controllerClass();
                $data = $controller->index($page);
            }
        }
    }

    return $data;
}

/**
 * Handle AJAX action requests (POST/GET with ?action=...).
 * Sets JSON content type and returns response.
 */
function handleAction(string $action): void
{
    header('Content-Type: application/json; charset=utf-8');

    $actionMap = [
        'login'              => ['AuthController', 'login'],
        'register'           => ['AuthController', 'register'],
        'update_password'    => ['AuthController', 'updatePassword'],
        'logout'             => ['AuthController', 'logout'],
        'save_grupo'         => ['GrupoController', 'save'],
        'promote_grupo'      => ['GrupoController', 'promote'],
        'set_active_grupo'   => ['GrupoController', 'setActive'],
        'set_active_ciclo'   => ['GrupoController', 'setActiveCiclo'],
        // Alumnos
        'save_alumno'        => ['AlumnoController', 'save'],
        'delete_alumno'      => ['AlumnoController', 'delete'],
        // Sesiones
        'save_sesion'        => ['SesionController', 'save'],
        'delete_sesion'      => ['SesionController', 'delete'],
        // Asistencia
        'save_attendance'    => ['AsistenciaController', 'save'],
        'init_attendance'    => ['AsistenciaController', 'initAttendance'],
        // Calificaciones
        'save_grades'        => ['CalificacionController', 'saveGrades'],
        'save_activity'      => ['CalificacionController', 'saveActivity'],
        'delete_activity'    => ['CalificacionController', 'deleteActivity'],
        // Consulta
        'consulta_search'    => ['ConsultaController', 'search'],
    ];

    if (!isset($actionMap[$action])) {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        return;
    }

    [$controllerClass, $method] = $actionMap[$action];
    $controllerFile = BASE_PATH . '/controllers/' . $controllerClass . '.php';

    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        if (class_exists($controllerClass)) {
            $controller = new $controllerClass();
            $result = $controller->$method();
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Controller no encontrado']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Archivo de controller no encontrado']);
    }
}
