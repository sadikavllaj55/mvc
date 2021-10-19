<?php

namespace App\Controllers;

abstract class BaseController
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';

    /**
     * Redirect to url, or controller action
     * Ex. /home/register
     * @param $controller string
     * @param $action string
     * @param $url_params array
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

    /**
     * @return string method used uppercase
     */
    public function getMethod()
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * @return bool
     */
    public function isGet()
    {
        return $this->getMethod() == self::METHOD_GET;
    }

    /**
     * @return bool
     */
    public function isPost()
    {
        return $this->getMethod() == self::METHOD_POST;
    }

    /**
     * @return mixed
     */
    abstract public function control();
}