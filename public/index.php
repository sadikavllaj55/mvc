<?php

require_once '../autoload.php';

use App\Controllers\BaseController;
use App\View\Template;

define('ROOT_DIR', realpath(__DIR__ . '/../'));
define('CONTROLLERS', realpath(ROOT_DIR . '/src/Controllers') . '/');
define('TEMPLATES', realpath(ROOT_DIR . '/views') . '/');
define('MODEL', realpath(ROOT_DIR . '/src/Model') . '/');
define('UPLOAD_DIR', ROOT_DIR . '/uploads/');
define('CONFIG', parse_ini_file(ROOT_DIR . '/config/config.ini', true));
define('BASE_URL', CONFIG['web']['url']);
define('WEB_URL', CONFIG['web']['url']);

$page = $_GET['page'] ?? 'main';

$controllerClass = ucfirst($page) . 'Controller';
$controllerFile = CONTROLLERS . $controllerClass . '.php';

try {
    if (file_exists($controllerFile)) {
        $controllerClass = 'App\Controllers\\' . $controllerClass;
        /** @var BaseController $controller */
        $controller = new $controllerClass();
        return $controller->control();
    } else {
        http_response_code(404);
        $view = new Template('error');
        $view->view('error/404');
        exit;
    }
} catch (Throwable $e) {
    http_response_code(500);
    $view = new Template('error');
    $view->view('error/500', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    exit;
}
