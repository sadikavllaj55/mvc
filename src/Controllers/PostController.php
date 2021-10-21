<?php

namespace App\Controllers;

use App\Model\Auth;
use App\Model\Image;
use App\Model\Posts;
use App\Validation\Validator;
use App\View\Template;

class PostController extends BaseController
{
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

    public function showNewPost()
    {
        $template = new Template('admin/base');
        $template->view('post/new');
    }

    /**
     * @throws \Exception
     */
    public function newPost()
    {
        $auth = new Auth();
        $author = $auth->getCurrentUser();

        $title = $_POST['title'];
        $description = $_POST['description'];
        $image = $_FILES['image'] ?? [];

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
        $post_added = $post->insertPost($author['id'], $title, $description, $upload_result['id']);

        if ($post_added) {
            $this->redirect('post', 'my_posts');
        } else {
            $this->redirect('post', 'new', ['errors' => ['Could not add the post']]);
        }
    }

    public function showEditPost() {
        $post_id = $_GET['id'] ?? null;
        $model = new Posts();

        $post = $model->getOnePost($post_id);

        if (!$post) {
            $this->redirect('post', 'my_posts', ['error' => 'Post was not found']);
        }

        $template = new Template('admin/base');
        $template->view('post/edit', ['post' => $post]);
    }

    /**
     * @throws \Exception
     */
    public function editPost()
    {
        $auth = new Auth();
        $author = $auth->getCurrentUser();

        $title = $_POST['title'];
        $description = $_POST['description'];
        $image = $_FILES['image'] ?? [];
        $post_id = $_GET['id'] ?? null;
        $has_image = ($image['name'] ?? '') != '';

        $model = new Posts();
        $post = $model->getOnePost($post_id);

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

    public function showAllPosts()
    {
        $model = new Posts();
        $auth = new Auth();
        $posts = $model->getListPosts();
        $current_user = $auth->getCurrentUser();
        $can_delete = $current_user['role'] == 'Admin';

        $view = new Template('admin/base');
        $view->view('post/posts', ['posts' => $posts, 'can_delete' => $can_delete]);
    }

    public function showMyPosts()
    {
        $auth = new Auth();
        $user = $auth->getCurrentUser();

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

        $template = new Template('admin/base');
        $template->view('post/view', ['post' => $post]);
    }

    public function deletePost()
    {
        $confirm = boolval($_POST['confirm'] ?? 0);
        $post_id = $_POST['id'] ?? null;

        $auth = new Auth();
        $user = $auth->getCurrentUser();

        $model = new Posts();
        $post = $model->getOnePost($post_id);

        if (!$post) {
            $this->redirect('post', 'my_posts', ['error' => 'Could not find the post']);
        }

        if (!$confirm) {
            $template = new Template('admin/base');
            $template->view('post/confirm_delete', ['to_delete' => $post]);
        } else {
            $deleted =
                $model->deletePost($post_id, $user['id']);

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
