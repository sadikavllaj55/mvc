<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Model\Auth;
use App\Model\DatabaseConnection;
use App\Model\Roles;
use App\Validation\Validator;
use App\View\Template;

class BlogController extends BaseController
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
        $action = $_GET['action'] ?? 'index';

        if ($action == 'view') {
            $this->viewPost();
        }
    }

    /**
     *
     */
    public function viewPost()
    {
        $view = new Template('frontend/base');
        $view->view('frontend/blog/view');
    }
}
