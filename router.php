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
        'home', 'alumnos', 'attendance', 'maestros',
        'write_exam', 'oral_exam', 'portfolio', 'homework',
        'exam', 'sito', 'sesiones', 'take_written_exam',
        'periodos', 'grupos_admin', 'materias', 'take_oral_exam', 'oral_exam_review',
        'quiz_lab', 'take_homework'
    ];

    $requiresGroup = [
        'attendance', 'write_exam', 'oral_exam', 
        'portfolio', 'homework', 'exam', 'sito', 'sesiones', 'oral_exam_review',
        'quiz_lab'
    ];

    // Redirect to login if not authenticated and trying to access a protected page
    if (in_array($page, $protectedPages) && empty($_SESSION['logged_in'])) {
        header('Location: index.php?page=login');
        exit;
    }

    // Role Guard: Students cannot access protected pages (only teachers/admin), except home and take_written_exam
    $studentAllowed = ['home', 'take_written_exam', 'take_oral_exam', 'take_homework'];
    if (in_array($page, $protectedPages) && !in_array($page, $studentAllowed) && ($_SESSION['usuario']['rol'] ?? '') === 'alumno') {
        header('Location: index.php?page=home');
        exit;
    }

    // Role Guard: Only admins can access admin modules
    $adminPages = ['periodos', 'grupos_admin', 'maestros', 'alumnos', 'materias'];
    if (in_array($page, $adminPages) && ($_SESSION['usuario']['rol'] ?? '') !== 'admin') {
        header('Location: index.php?page=home');
        exit;
    }

    // Role Guard: Only docentes can access evaluation/docencia modules
    $docentePages = [
        'attendance', 'write_exam', 'oral_exam', 
        'portfolio', 'homework', 'exam', 'sito', 'sesiones', 'oral_exam_review'
    ];
    if (in_array($page, $docentePages) && ($_SESSION['usuario']['rol'] ?? '') !== 'docente') {
        header('Location: index.php?page=home');
        exit;
    }

    // Role Guard: Teacher/Admin cannot access consulta (only students)
    if ($page === 'consulta' && !empty($_SESSION['logged_in']) && $_SESSION['usuario']['rol'] !== 'alumno') {
        header('Location: index.php?page=home');
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
        'home'            => 'HomeController',
        'alumnos'         => 'AlumnoController',
        'sesiones'        => 'SesionController',
        'attendance'      => 'AsistenciaController',
        'write_exam'      => 'CalificacionController',
        'oral_exam'       => 'CalificacionController',
        'take_oral_exam'  => 'CalificacionController',
        'oral_exam_review'=> 'CalificacionController',
        'portfolio'       => 'CalificacionController',
        'homework'        => 'CalificacionController',
        'exam'            => 'CalificacionController',
        'sito'            => 'CalificacionController',
        'consulta'        => 'ConsultaController',
        'maestros'        => 'DocenteController',
        'materias'        => 'MateriaController',
        'quiz_lab'        => 'CalificacionController',
        'take_homework'   => 'CalificacionController',
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
        'save_ciclo'         => ['GrupoController', 'saveCiclo'],
        // Materias
        'save_materia'       => ['MateriaController', 'save'],
        'delete_materia'     => ['MateriaController', 'delete'],
        // Maestros
        'save_maestro'       => ['DocenteController', 'save'],
        'maestro_history'    => ['DocenteController', 'history'],
        // Alumnos
        'save_alumno'        => ['AlumnoController', 'save'],
        'delete_alumno'      => ['AlumnoController', 'delete'],
        'reset_alumno_password' => ['AlumnoController', 'resetPassword'],
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
        'submit_written_exam'=> ['CalificacionController', 'submitWrittenExam'],
        'toggle_written_exam'=> ['CalificacionController', 'toggleWrittenExam'],
        // Examen Oral
        'save_oral_topic'    => ['CalificacionController', 'saveOralTopic'],
        'assign_oral_exam'   => ['CalificacionController', 'assignOralExam'],
        'save_oral_grade'    => ['CalificacionController', 'saveOralGrade'],
        // Consulta
        'consulta_search'    => ['ConsultaController', 'search'],
        // Quiz Lab & Homeworks
        'get_topic_questions' => ['CalificacionController', 'getTopicQuestions'],
        'save_quiz_lab_topic' => ['CalificacionController', 'saveQuizLabTopic'],
        'save_quiz_homework'  => ['CalificacionController', 'saveQuizHomework'],
        'submit_homework'     => ['CalificacionController', 'submitHomework'],
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
