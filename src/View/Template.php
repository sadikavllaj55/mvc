<?php

namespace App\View;

class Template
{
    private $layout;
    private $variables;

    public function __construct($layout = 'guest') {
        $this->layout = $layout;
        $this->variables = [];
    }

    function view($template, $variables = []) {
        extract($variables);
        include TEMPLATES . 'layout/' . $this->layout .  '.phtml';
    }

    function viewAnother($template, $variables = []) {
        extract($variables);
        include TEMPLATES . 'afterlogin/' . $this->layout .  '.phtml';
    }
}