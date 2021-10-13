<?php

namespace App\View;

class Template
{
    private $layout;

    public function __construct($layout = 'guest') {
        $this->layout = $layout;
    }

    function view($template, $variables = []){
        extract($variables);
        include TEMPLATES . 'layout/' . $this->layout .  '.phtml';
    }
}