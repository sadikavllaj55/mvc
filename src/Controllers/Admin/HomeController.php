<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Model\Auth;
use App\Model\DatabaseConnection;
use App\Model\Roles;
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

    /**
     * @return void
     */
    public function control()
    {
        $action = $_GET['action'] ?? 'register';

        if ($action == 'logout') {
            $this->logout();
        }

        $auth = new Auth();
        if ($auth->isLoggedIn()) {
            $this->redirect('dashboard', 'index');
        }

        if ($action == 'register') {
            if ($this->isGet()) {
                $this->showRegister();
            } else {
                $this->register();
            }
        }

        if ($action == 'login') {
            if ($this->isGet()) {
                $this->showLogin();
            } else {
                $this->login();
            }
        }
    }

    /**
     *
     */
    public function showRegister()
    {
        $view = new Template();
        $auth = new Auth();
        $view->view('admin/home/register', ['roles' => $auth->getRoleList()]);
    }

    /**
     *
     */
    public function showLogin()
    {
        $view = new Template();
        $view->view('admin/home/login');
    }

    /**
     *
     */
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

        $validation->notEmpty($password, 'Password should not be empty');
        $validation->containsCapitalLetters($password, 'Password should contain a capital letter');
        $validation->containsNumbers($password, 'Password should contain a number');
        $validation->shouldMatch($password, $password_repeat, 'Passwords don\'t match');

        $role_exists = Roles::getById($account_type) !== false;

        $non_unique_id = $auth->checkEmailUsername($username, $email);
        $admin_allowed = $auth->hasAdmin() && $account_type == 1;

        if ($non_unique_id) {
            $validation->addError('Username or email is taken.');
        }

        if ($admin_allowed) {
            $validation->addError('You can\'t register as admin.');
        }

        if (!$role_exists) {
            $validation->addError('The selected role is not valid.');
        }

        if (!$validation->isValid()) {
            $this->frontendRedirect('main', 'index', ['errors' => $validation->getErrors()]);
        }

        if ($auth->register($username, $email, $password, $account_type)) {
            $login = $auth->login($username, $password);

            if ($login) {
                $this->frontendRedirect('main', 'login');
            }
        }

        $this->redirect('home', 'login');
    }

    /**
     * if true redirect index else home login
     */
    private function login()
    {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $auth = new Auth();
        $validation = new Validator();

        $validation->notEmpty($username,'Username should be not empty');
        $validation->notEmpty($password,'Password should be not empty');

        $username_exists = $auth->checkUsername($username);

        if(!$username_exists) {
            $validation->addError('The specified account does not exist!');
        }

        if (!$validation->isValid()) {
            $this->frontendRedirect('main', 'login', ['errors' => $validation->getErrors()]);
        }

        $success = $auth->login($username, $password);

        if ($success) {
            $this->frontendRedirect('main', 'index');
        } else {
            $validation->addError('Wrong password!');
            $this->frontendRedirect('main', 'login', ['errors' => $validation->getErrors()]);
        }
    }

    /**
     * Logout
     */
    public function logout() {
        $auth = new Auth();
        $auth->logout();

        $this->frontendRedirect('main','login');
    }
}
