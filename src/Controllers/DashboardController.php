<?php

namespace App\Controllers;

use App\Model\Auth;
use App\View\Template;

class DashboardController extends BaseController
{
    public function control() {
        $action = $_GET['action'] ?? 'index';

        if ($action == 'index') {
            $this->showIndex();
        }
    }

    private function showIndex() {
        $auth = new Auth();
        if (!$auth->isLoggedIn()) {
            $this->redirect('home', 'login');
        }

        $user = $auth->getCurrentUser();

        $is_admin = $user['role_id'] == 1;

        if ($is_admin) {
            $view = new Template('admin');
        } else {
            $view = new Template('user');
        }

        $view->view('dashboard/index', ['username' => $user['username']]);
    }
}