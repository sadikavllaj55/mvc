<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Model\Auth;
use App\Model\Posts;
use App\Model\Users;
use App\Validation\Validator;
use App\View\Template;

class DashboardController extends BaseController
{
    /**
     * control all actions
     * @return void
     */
    public function control()
    {
        $action = $_GET['action'] ?? 'index';

        $auth = new Auth();
        if (!$auth->isLoggedIn()) {
            $this->redirect('home', 'login');
        }

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
        $users = new Users();
        $totalUsers = $users->countUsers();

        $posts = new Posts();
        $totalPosts = $posts->countPosts();
        $totalVisits = $posts->countVisits();

        $view = new Template('admin/base');
        $view->view('admin/dashboard/index', ['stats' => [
            'users' => $totalUsers,
            'posts' => $totalPosts,
            'visits' => $totalVisits
        ]]);
    }

    public function showProfile()
    {
        $auth = new Auth();
        $user = $auth->getCurrentUser();
        $view = new Template('admin/base');
        $view->view('admin/user/profile', ['to_edit' => $user]);
    }

    /**
     * edit current profile
     */
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
            $this->redirect('dashboard', 'profile', ['id' => $user_id, 'errors' => $validation->getErrors()]);
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
