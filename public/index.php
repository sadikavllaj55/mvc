<?php

require_once '../autoload.php';

use App\Controllers\BaseController;
use App\Controllers\HomeController;
use App\View\Template;

define('ROOT_DIR', realpath(__DIR__ . '/../'));
define('CONTROLLERS', realpath(__DIR__ . '/../src/Controllers') . '/');
define('TEMPLATES', realpath(__DIR__ . '/../views') . '/');
define('MODEL', realpath(__DIR__ . '/../src/Model') . '/');
define('CONFIG', parse_ini_file(ROOT_DIR . '/config/config.ini', true));

$page = $_GET['page'] ?? 'home';

$controllerClass = ucfirst($page) . 'Controller';
$controllerFile = CONTROLLERS . $controllerClass . '.php';

if (file_exists($controllerFile)) {
    $controllerClass = 'App\Controllers\\' . $controllerClass;
    /** @var BaseController $controllerClass */
    $controller = new $controllerClass();
    return $controller->control();
} else {
    http_response_code(404);
    $view = new Template();
    $view->view('error/404');
    exit;
}