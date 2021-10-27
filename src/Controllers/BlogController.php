<?php

namespace App\Controllers;

use App\Model\Category;
use App\Model\Comments;
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
        $post_id = $_GET['id'] ?? null;

        $model = new Posts();
        $post = $model->getOnePost($post_id);

        if (!$post) {
            $this->redirect('post', 'my_posts', ['error' => 'Post was not found']);
        }
        $model->addVisit($post_id);

        $comment_model = new Comments();
        $comments = $comment_model->getPostComments($post_id);

        $view = new Template('frontend/base');
        $view->view('frontend/blog/view', ['post' => $post, 'comments' => $comments]);
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
