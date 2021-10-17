<?php

namespace App\Controllers;

use App\Model\Auth;
use App\Model\DatabaseConnection;
use App\Validation\Validator;
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
            if ($this->isGet()) {
                $this->showLogin();
            } else {
                $this->login();
            }
        }

        if ($action == 'register') {
            $this->register();
        }

        if ($action == 'logout') {
            $this->logout();
        }
    }

    public function showIndex()
    {
        $view = new Template();
        $auth = new Auth();
        $view->view('home/index', ['roles' => $auth->getRoleList()]);
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
        $email = $data['email'];
        $password = $data['password'];
        $password_repeat = $data['password_repeat'];
        $account_type = $data['account_type'];

        $auth = new Auth();
        $validation = new Validator();

        $validation->notEmpty($username, 'Username should not be empty');
        $validation->notEmpty($email, 'Email should not be empty');
        $validation->isEmail($email, 'The specified email is not valid');
        $validation->customValidation($username, function () {

        });

        $validation->notEmpty($password, 'Password should not be empty');
        $validation->containsCapitalLetters($password, 'Password should contain a capital letter');
        $validation->containsNumbers($password, 'Password should contain a number');
        $validation->shouldMatch($password, $password_repeat, 'Passwords don\'t match');

        $non_unique_id = $auth->checkEmailUsername($username, $email);
        $admin_allowed = $auth->hasAdmin() && $account_type == 1;

        if ($non_unique_id) {
            $validation->addError('Username or email is taken.');
        }

        if ($admin_allowed) {
            $validation->addError('You can\'t register as admin.');
        }

        if (!$validation->isValid()) {
            $this->redirect('home', 'index', ['errors' => $validation->getErrors()]);
        }

        if ($auth->register($username, $email, $password, $account_type)) {
            $login = $auth->login($username, $password);

            if ($login) {
                $this->redirect('home', 'login');
            }
        }

        $this->redirect('home', 'login');
    }

    private function login()
    {
        $identity = $_POST['username'];
        $password = $_POST['password'];

        $auth = new Auth();

        $success = $auth->login($identity, $password);

        if ($success) {
            $this->redirect('dashboard', 'index');
        } else {
            $this->redirect('home', 'login');
        }
    }

    public function logout(){
        session_start();
        session_unset();
        session_destroy();

        $this->redirect('home','login');
    }
}
