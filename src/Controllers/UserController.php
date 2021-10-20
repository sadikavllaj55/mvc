<?php

namespace App\Controllers;

use App\Model\Auth;
use App\View\Template;
use App\Model\Users;
use App\Validation\Validator;

class UserController extends BaseController
{
    public function control()
    {
        $auth = new Auth();

        if ($auth->getCurrentUser()['role_id'] != 1) {
            $this->redirect('dashboard', 'index');
        }

        $action = $_GET['action'] ?? 'index';

        if ($action == 'users') {
            $this->showAllUsers();
        }

        if ($action == 'edit') {
            if ($this->isGet()) {
                $this->showEdit();
            } else {
                $this->editUser();
            }
        }

        if ($action == 'delete') {
            $this->deleteUser();
        }
    }

    public function showEdit()
    {
        $user_id = $_GET['id'] ?? null;
        $auth = new Auth();
        $current_user = $auth->getCurrentUser();
        if ($user_id == $current_user['id']) {
            $this->redirect('user', 'users', ['error' => 'You can\'t edit yourself. Go to your <a href="index.php?page=dashboard&action=profile">profile</a> to edit your data.']);
        }
        $view = new Template('admin/base');
        $model = new Users();
        $user = $model->getById($user_id);
        if (!$user) {
            $this->redirect('user', 'users', ['error' => 'User does not exist!']);
        }

        $view->view('user/edit_user', ['user' => $current_user, 'to_edit' => $user]);
    }

    public function showAllUsers()
    {
        $auth = new Auth();
        $user = $auth->getCurrentUser();
        $model = new Users();
        $users = $model->getList();
        $view = new Template('admin/base');
        $view->view('user/users', ['users' => $users, 'user' => $user]);
    }

    public function editUser()
    {
        $auth = new Auth();
        $model = new Users();

        $user_id = $_GET['id'];
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
            $this->redirect('user', 'users');
        } else {
            $this->redirect('user', 'users', ['error' => 'Could not edit the user']);
        }
    }

    public function deleteUser()
    {
        $confirm = boolval($_POST['confirm'] ?? 0);
        $user_id = $_POST['id'] ?? null;
        $auth = new Auth();
        $current_user = $auth->getCurrentUser();
        $model = new Users();
        if (!$confirm) {
            $template = new Template('admin/base');
            $user = $model->getById($user_id);

            if ($user == false) {

                $this->redirect('user', 'users', ['error' => 'Could not find the user']);
            }
            $template->view('user/confirm_delete', ['user' => $current_user, 'to_delete' => $user]);
        } else {
            $deleted = $model->deleteById($user_id);

            if ($deleted) {
                $this->redirect('user', 'users');
            } else {
                $this->redirect('user', 'users', ['error' => 'Could not delete the user']);
            }
        }
    }
}
