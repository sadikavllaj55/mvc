<?php

namespace App\Model;

use PDO;

class Comments
{
    private $db;
    public $id;
    public $user_id;
    public $post_id;
    public $text;
    public $published_date;

    public function __construct()
    {
        $db = DatabaseConnection::getInstance();
        $db_config = CONFIG['database'];
        $db::connect($db_config['host'], $db_config['dbname'], $db_config['username'], $db_config['password']);
        $this->db = $db->getConnection();
    }

    public function getPostComments($post_id) {
        $query = $this->db->prepare('SELECT comments.id, comments.description,comments.published,comments.post_id,
                users.username as author, comments.user_id AS author_id
            FROM comments 
                LEFT JOIN users ON comments.user_id = users.id
            WHERE comments.post_id = :post
            ORDER BY comments.published DESC');

        $query->bindParam(':post', $post_id, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countComments()
    {
        return $this->db->query('select count(*) from comments')->fetchColumn();
    }

    public function deleteById($id)
    {
        $query = $this->db->prepare('
            DELETE 
            FROM comments
            WHERE id=:id
        ');

        $query->bindParam(':id', $id, PDO::PARAM_INT);

        return $query->execute();
    }

    public function addComment($author, $post_id, $comment) {
        $query = $this->db->prepare('
            INSERT INTO comments (description,user_id,post_id) 
            VALUES (:comment,:user,:post)
        ');

        $query->bindParam(':comment', $comment);
        $query->bindParam(':user', $author, PDO::PARAM_INT);
        $query->bindParam(':post', $post_id, PDO::PARAM_INT);
        return $query->execute();
    }

    public function getById($id) {
        $query = $this->db->prepare('
            SELECT comments.id, comments.description,comments.published,comments.post_id,
                users.username as author, comments.user_id AS author_id
            FROM comments 
                LEFT JOIN users ON comments.user_id = users.id
            WHERE comments.id=:id
        ');

        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }
}
