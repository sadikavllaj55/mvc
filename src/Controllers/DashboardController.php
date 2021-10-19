<?php

namespace App\Controllers;

use App\Model\Auth;
use App\Model\Users;
use App\Validation\Validator;
use App\View\Template;

class DashboardController extends BaseController
{
    /**
     * @return void
     */
    public function control()
    {
        $action = $_GET['action'] ?? 'index';

        if ($action == 'index') {
            $this->showIndex();
        }

        if ($action == 'profile') {
            if ($this->isGet()) {
                $this->showProfile();
            } else {
                $this->editProfile();
            }
        }
    }

    /**
     *show index page depending on isLoggedIn and isAdmin
     */
    private function showIndex()
    {
        $auth = new Auth();
        if (!$auth->isLoggedIn()) {
            $this->redirect('home', 'login');
        }

        $user = $auth->getCurrentUser();

        $view = new Template('admin/base');
        $view->view('dashboard/index', ['user' => $user]);
    }

    public function showProfile()
    {
        $auth = new Auth();
        $user = $auth->getCurrentUser();
        $view = new Template('admin/base');
        $view->view('user/profile', ['user' => $user, 'to_edit' => $user]);
    }

    public function editProfile()
    {
        $auth = new Auth();
        $model = new Users();
        $current_user = $auth->getCurrentUser();

        $user_id = $current_user['id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $password_repeat = $_POST['password_repeat'];

        $validation = new Validator();

        $validation->notEmpty($username, 'Username should not be empty');
        $validation->notEmpty($email, 'Email should not be empty');
        $validation->isEmail($email, 'The specified email is not valid');
        $validation->shouldMatch($password, $password_repeat, 'Passwords don\'t match');

        if ($password != '') {
            $validation->notEmpty($password, 'Password should not be empty');
            $validation->containsCapitalLetters($password, 'Password should contain a capital letter');
            $validation->containsNumbers($password, 'Password should contain a number');
        }

        $non_unique_id = $auth->checkEmailUsername($username, $email, $user_id);

        if ($non_unique_id) {
            $validation->addError('Username or email is taken.');
        }

        if (!$validation->isValid()) {
            $this->redirect('user', 'edit', ['id' => $user_id, 'errors' => $validation->getErrors()]);
        }

        $edit = $model->editUser($user_id, $username, $email, $password);

        if ($edit) {
            $auth->updateUserSession();
            $this->redirect('dashboard', 'index');
        } else {
            $this->redirect('dashboard', 'profile', ['error' => 'Could not edit your profile']);
        }
    }
}
