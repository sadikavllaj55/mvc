<?php

namespace App\Model;

use PDO;

class Users
{
    private $db;
    public $username;
    public $email;
    public $password;
    public $type;

    public function __construct()
    {
        $db = DatabaseConnection::getInstance();
        $db_config = CONFIG['database'];
        $db::connect($db_config['host'], $db_config['dbname'], $db_config['username'], $db_config['password']);
        $this->db = $db->getConnection();
    }

    public function getList()
    {
        $query = $this->db->query('
            SELECT users.*, roles.type as role 
            FROM users, roles
            WHERE users.role_id=roles.id
        ');

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBy()
    {
        $query = $this->db->query('
            SELECT users.*, roles.type as role 
            FROM users, roles
            WHERE users.role_id=roles.id AND users.role_id=7
        ');

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = $this->db->prepare('
            SELECT *
            FROM users 
            WHERE id=:user
        ');

        $query->bindParam(':user', $id, PDO::PARAM_INT);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function editUser($id, $username, $email, $password)
    {
        $password_set = strlen($password) > 0;
        $fields = ['username = :username', 'email = :email'];
        if ($password_set) {
            $password_hash = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);
            $fields[] = 'password = :password';
        }

        $query = $this->db->prepare('
            UPDATE users 
            SET ' . implode(', ', $fields) .'
            WHERE id = :id
        ');

        $query->bindParam(':id', $id);
        $query->bindParam(':username', $username);
        $query->bindParam(':email', $email);

        if ($password_set) {
            $query->bindParam(':password', $password_hash);
        }

        return $query->execute();
    }

    public function countUsers()
    {
        return $this->db->query('select count(*) from users')->fetchColumn();

    }

    public function deleteById($id)
    {
        $query = $this->db->prepare('
            DELETE 
            FROM users
            WHERE id=:id
        ');

        $query->bindParam(':id', $id, PDO::PARAM_INT);

        return $query->execute();
    }
}