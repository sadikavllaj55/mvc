<?php

namespace App\Controllers;

use App\Model\Auth;
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
}
