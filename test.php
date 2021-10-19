<?php

use App\Model\Users;

require 'autoload.php';

define('ROOT_DIR', realpath(__DIR__ . '/'));
define('CONTROLLERS', realpath(ROOT_DIR . '/src/Controllers') . '/');
define('TEMPLATES', realpath(ROOT_DIR . '/views') . '/');
define('MODEL', realpath(ROOT_DIR . '/src/Model') . '/');
define('CONFIG', parse_ini_file(ROOT_DIR . '/config/config.ini', true));
define('BASE_URL', CONFIG['web']['url']);

$model = new Users();
$user = $model->getById(6);
$users = $model->getBy();

var_export($user);
echo PHP_EOL;
var_export($users);