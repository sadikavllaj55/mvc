<?php

namespace App\Model;

use PDO;

class Posts
{
    private $db;
    public $id;
    public $userId;
    public $title;
    public $description;
    public $imageId;

    public function __construct()
    {
        $db = DatabaseConnection::getInstance();
        $db_config = CONFIG['database'];
        $db::connect($db_config['host'], $db_config['dbname'], $db_config['username'], $db_config['password']);
        $this->db = $db->getConnection();
    }

    public function insertPost($author, $title, $description, $image_id)
    {
        $query = $this->db->prepare('
            INSERT INTO posts (user_id,title,description,image_id) 
            VALUES (:author, :title, :description, :image_id)'
        );
        $query->bindParam(':author', $author);
        $query->bindParam(':title', $title);
        $query->bindParam(':description', $description);
        $query->bindParam(':image_id', $image_id);

        return $query->execute();
    }

    public function getListPosts()
    {
        $query = $this->db->query('
            SELECT posts.id , posts.title , posts.created_at, posts.description, 
                users.username as author, posts.user_id, images.path as image
            FROM posts    
                LEFT JOIN users ON posts.user_id = users.id
                LEFT JOIN images ON posts.image_id = images.id
            ORDER BY posts.created_at DESC '
        );

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOnePost($id)
    {
        $query = $this->db->prepare('SELECT posts.id , posts.title , posts.created_at, posts.description, 
                users.username as author, posts.user_id, posts.image_id, images.path as image
            FROM posts    
                LEFT JOIN users ON posts.user_id = users.id 
                LEFT JOIN images ON posts.image_id = images.id
            WHERE posts.id = :id'
        );
        $query->bindParam(':id', $id);

        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserPosts($user_id)
    {
        $query = $this->db->prepare('
            SELECT posts.id , posts.title , posts.created_at, posts.description, users.username as author, 
                   posts.user_id, images.path as image
            FROM posts
                LEFT JOIN users ON posts.user_id = users.id
                LEFT JOIN images ON posts.image_id = images.id
            WHERE users.id = :id'
        );
        $query->bindParam(':id', $user_id);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deletePost($id, $author)
    {
        $query = $this->db->prepare('
            DELETE 
            FROM posts 
            WHERE id = :id AND user_id = :user_id'
        );
        $query->bindParam(':id', $id);
        $query->bindParam(':user_id', $author);
        return $query->execute();
    }

    public function updatePost($id, $title, $description, $imageId, $userId)
    {
        $query = $this->db->prepare('
            UPDATE posts 
            SET title = :title, description = :description, image_id = :imageId
            WHERE id=:id AND user_id = :userId'
        );
        $query->bindParam(':id', $id);
        $query->bindParam(':title', $title);
        $query->bindParam(':description', $description);
        $query->bindParam(':imageId', $imageId);
        $query->bindParam(':userId', $userId);
        return $query->execute();
    }
}