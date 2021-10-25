<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Model\Auth;
use App\Model\Comments;
use App\View\Template;
use App\Validation\Validator;

class CommentController extends BaseController
{
    public Auth $auth;

    public function __construct() {
        $this->auth = new Auth();
    }

    public function control()
    {
        $action = $_GET['action'] ?? null;

        if ($action == 'add') {
            $this->comment();
        }

        if ($action == 'delete') {
            $this->deleteComment();
        }
    }

    public function deleteComment()
    {
        $confirm = boolval($_POST['confirm'] ?? 0);
        $comment_id = $_POST['id'] ?? null;
        $post_id = $_POST['post_id'] ?? null;
        $model = new Comments();
        if (!$confirm) {
            $template = new Template('admin/base');
            $comment = $model->getById($comment_id);

            if ($comment == false) {
                $this->redirect('post', 'view', ['id' => $post_id, 'error' => 'Could not find the user']);
            }

            $template->view('admin/comment/confirm_delete', ['comment' => $comment]);
        } else {
            $deleted = $model->deleteById($comment_id);

            if ($deleted) {
                $this->redirect('post', 'view', ['id' => $post_id, 'success' => 'Comment was deleted.']);
            } else {
                $this->redirect('post', 'view', ['id' => $post_id, 'error' => 'Could not delete the user']);
            }
        }
    }

    public function comment() {
        $author = $this->auth->getCurrentUser();
        $comment = $_POST['description'];
        $post_id = $_POST['post_id'] ?? null;

        $validation = new Validator();
        $validation->minLength($comment, 3, 'Short comments are not allowed. Type something longer than 3 characters.');

        if (!$validation->isValid()) {
            $this->redirect('post', 'view', ['id' => $post_id, 'errors' => $validation->getErrors()]);
        }

        $model = new Comments();

        $added = $model->addComment($author['id'], $post_id, $comment);

        if ($added) {
            $this->redirect('post', 'view', ['id' => $post_id, 'success' => 'Your comment was added.']);
        } else {
            $this->redirect('post', 'view', ['id' => $post_id, 'errors' => ['Could not add comment']]);
        }
    }
}
