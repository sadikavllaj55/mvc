<?php

namespace App\Controllers;

abstract class BaseController
{
    /**
     * Redirect to url, or controller action
     * Ex. /home/register
     * @param $controller
     * @param $action
     */
    public function redirect($controller, $action)
    {
        $url = '/mvc/public/index.php?page=' . $controller . '&action=' . $action;
        header('Location:' . $url);
    }
}