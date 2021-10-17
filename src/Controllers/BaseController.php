<?php

namespace App\Controllers;

abstract class BaseController
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';

    /**
     * Redirect to url, or controller action
     * Ex. /home/register
     * @param $controller
     * @param $action
     * @param $url_params
     */
    public function redirect($controller, $action, $url_params = [])
    {
        $query_string = http_build_query($url_params);
        $url = BASE_URL . 'index.php?page=' . $controller . '&action=' . $action;
        if (!empty($query_string)) {
            $url = BASE_URL . 'index.php?page=' . $controller . '&action=' . $action . '&' . $query_string;
        }
        header('Location:' . $url);
        exit;
    }

    public function getMethod()
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    public function isGet()
    {
        return $this->getMethod() == self::METHOD_GET;
    }

    public function isPost()
    {
        return $this->getMethod() == self::METHOD_POST;
    }

    abstract public function control();
}