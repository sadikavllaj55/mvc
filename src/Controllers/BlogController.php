<?php

namespace App\Controllers;

use App\Model\Category;
use App\Model\DatabaseConnection;
use App\Model\Posts;
use App\View\Template;

class BlogController extends BaseController {
    public $db;

    public function __construct() {
        $db = DatabaseConnection::getInstance();
        $db_config = CONFIG['database'];
        $db::connect($db_config['host'], $db_config['dbname'], $db_config['username'], $db_config['password']);
        $this->db = $db->getConnection();
    }

    /**
     * @return void
     */
    public function control() {
        $action = $_GET['action'] ?? 'index';

        if ($action == 'view') {
            $this->viewPost();
        }

        if ($action == 'category') {
            $this->showCategoryPosts();
        }
    }

    /**
     *
     */
    public function viewPost() {
        $view = new Template('frontend/base');
        $view->view('frontend/blog/view');
    }

    public function showCategoryPosts() {
        $category_id = $_GET['id'] ?? null;
        $category = Category::getById($category_id);
        $post_model = new Posts();
        $posts = $post_model->getByCategory($category_id);

        $view = new Template('frontend/base');
        $view->view('frontend/blog/category', ['category' => $category, 'posts' => $posts]);
    }
}
