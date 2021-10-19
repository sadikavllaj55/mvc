<?php

namespace App\Model;

use PDO;

class Auth
{
    private $db;

    public function __construct()
    {
        $db = DatabaseConnection::getInstance();
        $db_config = CONFIG['database'];
        $db::connect($db_config['host'], $db_config['dbname'], $db_config['username'], $db_config['password']);
        $this->db = $db->getConnection();
    }

    /**
     * @param $username
     * @param $email
     * @param $password
     * @param $role
     * @return bool true if user is added, false otherwise
     */
    public function register($username, $email, $password, $role) {
        $password = password_hash($password,PASSWORD_BCRYPT,["cost" => 12]);
        $query = $this->db->prepare('
            INSERT INTO users (username, email, password, role_id) 
            VALUES (:username, :email, :password, :role)');
        $query->bindParam(':username', $username);
        $query->bindParam(':email', $email);
        $query->bindParam(':password', $password);
        $query->bindParam(':role', $role);

        return $query->execute();
    }

    /**
     * @param $username
     * @param $password
     * @return bool
     */
    public function login($username, $password) {

        $query = $this->db->prepare('
            SELECT users.*, roles.type as role 
            FROM users, roles 
            WHERE username = :user AND users.role_id=roles.id');
        $query->bindValue(':user', $username);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);

        if($row !== false) {
            if (password_verify($password, $row['password'])) {
                session_start();
                unset($row['password']); // delete the password column
                $_SESSION['user'] = $row;

                return true;
            }
        }

        return false;
    }

    public function getRoleList() {

        if(!$this->hasAdmin()) {
            $query = $this->db->query('
                SELECT id, type
                FROM roles
            ');
        } else {
            $query = $this->db->query('
                SELECT id, type
                FROM roles WHERE id != 1;
            ');
        }

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isLoggedIn() {
        if(!isset($_SESSION)) {
            session_start();
        }

        return isset($_SESSION['user']) && is_array($_SESSION['user']);
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return $_SESSION['user'];
        }

        return null;
    }

    public function checkEmailUsername($username, $email, $exception_id = 0){
        $query = $this->db->prepare('
           SELECT * 
           FROM users 
           WHERE (email=:email OR username=:username) AND id<>:exception
        ');
        $query->bindParam(':username', $username);
        $query->bindParam(':email', $email);
        $query->bindParam(':exception', $exception_id, PDO::PARAM_INT);
        $query->execute();
        return count($query->fetchAll(PDO::FETCH_ASSOC)) > 0;
    }


    public function hasAdmin() {
        $query = $this->db->prepare('
           SELECT username FROM users WHERE role_id = 1
        ');

        $query->execute();
        return count($query->fetchAll()) > 0;
    }

    public function checkUsername($username){
        $query = $this->db->prepare('
           SELECT username FROM users WHERE username=:username
        ');
        $query->bindParam(':username', $username);;
        $query->execute();
        return count($query->fetchAll(PDO::FETCH_ASSOC)) > 0;
    }

    public function logout(){
        session_start();
        session_unset();
        session_destroy();
    }
}
