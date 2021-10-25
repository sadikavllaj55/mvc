<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Model\Auth;
use App\Model\Comments;
use App\Model\Image;
use App\Model\Posts;
use App\Validation\Validator;
use App\View\Template;
use App\Model\Category;

class PostController extends BaseController
{
    public Auth $auth;

    public function __construct() {
        $this->auth = new Auth();
    }

    /**
     * @throws \Exception
     */
    public function control()
    {
        $action = $_GET['action'] ?? 'index';

        if ($action == 'posts') {
            $this->showAllPosts();
        }

        if ($action == 'edit') {
            if ($this->isGet()) {
                $this->showEditPost();
            } else {
                $this->editPost();
            }
        }

        if ($action == 'my_posts') {
            $this->showMyPosts();
        }

        if ($action == 'view') {
            $this->viewPost();
        }

        if ($action == 'new') {
            if ($this->isGet()) {
                $this->showNewPost();
            } else {
                $this->newPost();
            }
        }

        if ($action == 'delete') {
            $this->deletePost();
        }
    }

    public function showAllPosts()
    {
        $model = new Posts();
        $posts = $model->getListPosts();

        $view = new Template('admin/base');
        $view->view('post/posts', ['posts' => $posts]);
    }

    public function showEditPost()
    {
        $user = $this->auth->getCurrentUser();
        $post_id = $_GET['id'] ?? null;
        $model = new Posts();

        $post = $model->getOnePost($post_id);

        if (!$post) {
            $this->redirect('post', 'posts', ['error' => 'Post was not found']);
        }

        if ($post['user_id'] != $user['id'] && $user['role_id'] != 1) { // If not admin and not your post
            $this->redirect('post', 'posts', ['error' => 'You don\'t have access for this action']);
        }

        $template = new Template('admin/base');
        $template->view('post/edit', ['post' => $post]);
    }

    /**
     * @throws \Exception
     */
    public function editPost()
    {
        $author = $this->auth->getCurrentUser();

        $title = $_POST['title'];
        $description = $_POST['description'];
        $image = $_FILES['image'] ?? [];
        $post_id = $_GET['id'] ?? null;
        $has_image = ($image['name'] ?? '') != '';

        $model = new Posts();
        $post = $model->getOnePost($post_id);

        if ($post['user_id'] != $author['id'] && $author['role_id'] != 1) {
            $this->redirect('post', 'posts', ['error' => 'You don\'t have access for this action']);
        }

        $validation = new Validator();

        $validation->notEmpty($title, 'Title should not be empty.');
        $validation->minLength($title, 10, 'Title should be something interesting.');
        $validation->notEmpty($description, 'Post body should not be empty.');
        $validation->minLength($description, 10, 'The blog post should have some content in it. Try adding more text');

        if ($has_image) {
            $validation->notEmpty($image['name'] ?? '', 'The post should have an image. Please select one.');
            $validation->max($image['size'] ?? 0, Image::MAX_FILE_SIZE * 1024 * 1024,
                'The selected image is more than ' . Image::MAX_FILE_SIZE . 'MB.');
        }

        if (!$validation->isValid()) {
            $this->redirect('post', 'edit', ['errors' => $validation->getErrors()]);
        }

        $image_id = $post['image_id'];

        if ($has_image) {
            $image = new Image();
            $replace_result = $image->replaceImage($post['image_id'], $author['id'], UPLOAD_DIR . 'images/', 'image');

            if (!$replace_result['success']) {
                $validation->addError($replace_result['message']);
                $this->redirect('post', 'edit', ['errors' => $validation->getErrors()]);
            }

            $image_id = $replace_result['id'];
        }

        $result = $model->updatePost($post_id, $title, $description, $image_id, $author['id']);

        if ($result) {
            $this->redirect('post', 'my_posts');
        }
    }

    public function showMyPosts()
    {
        $user = $this->auth->getCurrentUser();

        $model = new Posts();
        $posts = $model->getUserPosts($user['id']);

        $view = new Template('admin/base');
        $view->view('post/my_posts', ['posts' => $posts]);
    }

    public function viewPost()
    {
        $post_id = $_GET['id'] ?? null;

        $model = new Posts();
        $post = $model->getOnePost($post_id);

        if (!$post) {
            $this->redirect('post', 'my_posts', ['error' => 'Post was not found']);
        }
        $model->addVisit($post_id);

        $comment_model = new Comments();
        $comments = $comment_model->getPostComments($post_id);

        $template = new Template('admin/base');
        $template->view('post/view', ['post' => $post, 'comments' => $comments]);
    }

    public function showNewPost()
    {
        $template = new Template('admin/base');
        $categories = new Category();
        $template->view('post/new', ['categories' => $categories->getCategoryList()]);
    }

    /**
     * @throws \Exception
     */
    public function newPost()
    {
        $author = $this->auth->getCurrentUser();

        $title = $_POST['title'];
        $description = $_POST['description'];
        $image = $_FILES['image'] ?? [];
        $category = $_POST['category'];

        $validation = new Validator();

        $validation->notEmpty($title, 'Title should not be empty.');
        $validation->minLength($title, 10, 'Title should be something interesting.');
        $validation->notEmpty($description, 'Post body should not be empty.');
        $validation->minLength($description, 10, 'The blog post should have some content in it. Try adding more text');
        $validation->notEmpty($image['name'] ?? '', 'The post should have an image. Please select one.');
        $validation->max($image['size'] ?? 0, Image::MAX_FILE_SIZE * 1024 * 1024,
            'The selected image is more than ' . Image::MAX_FILE_SIZE . 'MB.');

        if (!$validation->isValid()) {
            $this->redirect('post', 'new', ['errors' => $validation->getErrors()]);
        }

        $image = new Image();

        $upload_result = $image->uploadImage(UPLOAD_DIR . 'images/', 'image');

        if (!$upload_result['success']) {
            $validation->addError($upload_result['message']);
            $this->redirect('post', 'new', ['errors' => $validation->getErrors()]);
        }

        $post = new Posts();
        $post_added = $post->insertPost($author['id'], $title, $description, $upload_result['id'],$category);

        if ($post_added) {
            $this->redirect('post', 'my_posts');
        } else {
            $this->redirect('post', 'new', ['errors' => ['Could not add the post']]);
        }
    }

    public function deletePost()
    {
        $confirm = boolval($_POST['confirm'] ?? 0);
        $post_id = $_POST['id'] ?? null;

        $user = $this->auth->getCurrentUser();

        $model = new Posts();
        $post = $model->getOnePost($post_id);

        if (!$post) {
            $this->redirect('post', 'posts', ['error' => 'Could not find the post']);
        }

        if ($post['user_id'] != $user['id'] && $user['role_id'] != 1) {
            $this->redirect('post', 'posts', ['error' => 'You don\'t have access for this action']);
        }

        if (!$confirm) {
            $template = new Template('admin/base');
            $template->view('post/confirm_delete', ['to_delete' => $post]);
        } else {
            $deleted = $model->deletePost($post_id, $user['id']);

            if ($deleted) {
                $deleted_image = new Image();
                $deleted_image->deleteImage($post['image_id'], $user['id']);
                $this->redirect('post', 'my_posts');
            } else {
                $this->redirect('post', 'my_posts', ['error' => 'Could not delete the post']);
            }
        }
    }
}
