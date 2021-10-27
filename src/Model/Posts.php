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
    public $categoryId;

    public function __construct()
    {
        $db = DatabaseConnection::getInstance();
        $db_config = CONFIG['database'];
        $db::connect($db_config['host'], $db_config['dbname'], $db_config['username'], $db_config['password']);
        $this->db = $db->getConnection();
    }

    public function insertPost($author, $title, $description, $image_id, $category_id)
    {
        $query = $this->db->prepare('
            INSERT INTO posts (user_id, title, description, image_id, category_id) 
            VALUES (:author, :title, :description, :image_id, :categoryId)'
        );
        $query->bindParam(':author', $author);
        $query->bindParam(':title', $title);
        $query->bindParam(':description', $description);
        $query->bindParam(':image_id', $image_id);
        $query->bindParam(':categoryId', $category_id);

        return $query->execute();
    }

    public function getListPosts()
    {
        $query = $this->db->query('
            SELECT posts.id , posts.title , posts.created_at, posts.description, posts.visits,
                users.username as author, posts.user_id, images.path as image
            FROM posts    
                LEFT JOIN users ON posts.user_id = users.id
                LEFT JOIN images ON posts.image_id = images.id
            ORDER BY posts.created_at DESC
         '
        );
       /* $query->bindParam(':off', $offset);
        $query->bindParam(':lim', $limit);*/

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOnePost($id)
    {
        $query = $this->db->prepare('SELECT posts.id , posts.title , posts.created_at, posts.description, 
                users.username as author, posts.user_id, posts.image_id, images.path as image, posts.visits
            FROM posts    
                LEFT JOIN users ON posts.user_id = users.id 
                LEFT JOIN images ON posts.image_id = images.id
            WHERE posts.id = :id'
        );
        $query->bindParam(':id', $id);

        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserPosts($userId)
    {
        $query = $this->db->prepare('
            SELECT posts.id , posts.title , posts.created_at, posts.description, users.username as author, 
                   posts.user_id, images.path as image, posts.visits
            FROM posts
                LEFT JOIN users ON posts.user_id = users.id
                LEFT JOIN images ON posts.image_id = images.id
            WHERE users.id = :id'
        );
        $query->bindParam(':id', $userId);
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

    public function updatePost($id, $title, $description, $imageId, $userId, $category)
    {
        $query = $this->db->prepare('
            UPDATE posts 
            SET title = :title, description = :description, image_id = :imageId, category_id = :categoryId
            WHERE id=:id AND user_id = :userId'
        );
        $query->bindParam(':id', $id);
        $query->bindParam(':title', $title);
        $query->bindParam(':description', $description);
        $query->bindParam(':imageId', $imageId);
        $query->bindParam(':userId', $userId);
        $query->bindParam(':categoryId', $category);
        return $query->execute();
    }



    public function addVisit($id)
    {
        $query = $this->db->prepare('
            UPDATE posts 
            SET visits=visits+1
            WHERE id=:id'
        );
        $query->bindParam(':id', $id);
        return $query->execute();
    }

    public function countVisits()
    {
        return $this->db->query('SELECT SUM(visits) FROM posts')->fetchColumn();
    }

    public function getByCategory($id)
    {
        $query = $this->db->prepare('SELECT posts.id , posts.title , posts.created_at, posts.description, 
                users.username as author, posts.user_id, posts.image_id, images.path as image, posts.visits,
                categories.category, posts.category_id
            FROM posts    
                LEFT JOIN users ON posts.user_id = users.id 
                LEFT JOIN images ON posts.image_id = images.id
                LEFT JOIN categories ON posts.category_id = categories.id
            WHERE posts.category_id = :id 
            ORDER BY created_at DESC'
        );
        $query->bindParam(':id', $id);

        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopPost() {
        $query = $this->db->query('SELECT posts.id , posts.title , posts.created_at, posts.description, 
                users.username as author, posts.user_id, posts.image_id, images.path as image, posts.visits,
                categories.category, posts.category_id
            FROM posts    
                LEFT JOIN users ON posts.user_id = users.id 
                LEFT JOIN images ON posts.image_id = images.id
                LEFT JOIN categories ON posts.category_id = categories.id
            ORDER BY visits DESC
            LIMIT 0, 1'
        );

        return $query->fetch(PDO::FETCH_ASSOC);

    }

    public function countPosts()
    {
        return $this->db->query('SELECT COUNT(*) FROM posts')->fetchColumn();
    }

    /*public function getPostsPerPage(int $page = 1, int $size = 3)
    {
        $offset = ($page - 1) * $size;
        $list = $this->getListPosts($size, $offset);
        return $list;
    }

    public function numberOfPages($size)
    {
        $count = $this->countPosts();
        return (int)ceil(($count) / $size);
    }*/

}
