<?php

spl_autoload_register(function ($class_name) {
    // App\Controllers\BaseController => src/Controllers/BaseController.php
    // App\View\Template => src/Controllers/Template.php
    // App\Model\Database => src/Model/Database.php
    // PhpMailer\Mail
    $prefix = 'App\\';
    $source_dir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class_name, $len) !== 0) {
        return;
    }

    // get the relative class name
    $relative_class = substr($class_name, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $source_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});