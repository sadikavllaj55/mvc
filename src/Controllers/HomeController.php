<?php

namespace App\Controllers;

use App\Model\DatabaseConnection;
use App\View\Template;

class HomeController extends BaseController
{
    public $db;

    public function __construct()
    {
        $db = DatabaseConnection::getInstance();
        $db_config = CONFIG['database'];
        $db::connect($db_config['host'], $db_config['dbname'], $db_config['username'], $db_config['password']);
        $this->db = $db->getConnection();
    }

    public function control()
    {
        $action = $_GET['action'] ?? 'index';

        if ($action == 'index') {
            $this->showIndex();
        }
        if ($action == 'login') {
            $this->showLogin();
        }

        if ($action == 'register') {
            $this->register();
        }
    }

    public function showIndex()
    {
        $view = new Template();
        $view->view('home/index', []);
    }

    public function showLogin()
    {
        $view = new Template();
        $view->view('home/login', []);
    }

    private function register()
    {
        $data = $_POST;
        $username = $data['username'];
        $password = $data['password'];
        $password_repeat = $data['password_repeat'];
        $account_type = $data['account_type'];
        /*$validator = new ValidatorClass();
        $validator->validation_list = [];
        $validator->notEmpty($username, 'Username should not be empty');
        $validator->length($username, 6);
        $validator->email($email);
        Session::get('post_length', 25)
        Session::clean()
        Session::add('name', $value)
        $validator->isValid();*/

        $this->redirect('home', 'login');
        //header('Location: index.php?page=home');
    }
}