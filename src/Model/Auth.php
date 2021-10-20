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
     * @return bool true if user exist in db
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

    /**
     * return array from table roles
     * @return array|false
     */
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

    /**
     * check if is logged in
     * @return bool
     */
    public function isLoggedIn() {
        if(!isset($_SESSION)) {
            session_start();
        }

        return isset($_SESSION['user']) && is_array($_SESSION['user']);
    }

    /**
     * get array if user is logged in with current data puted in session
     * @return mixed|null
     */
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return $_SESSION['user'];
        }

        return null;
    }

    /**
     * check if email or username are used by another user
     * @param string $username
     * @param string $email
     * @param int $exception_id
     * @return bool
     */
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

    /**
     * check if user exist with role admin or not(role_id = 1)
     * @return bool
     */
    public function hasAdmin() {
        $query = $this->db->prepare('
           SELECT username FROM users WHERE role_id = 1
        ');

        $query->execute();
        return count($query->fetchAll()) > 0;
    }

    /**
     * check if username exist
     * @param $username string
     * @return bool
     */
    public function checkUsername($username){
        $query = $this->db->prepare('
           SELECT username FROM users WHERE username=:username
        ');
        $query->bindParam(':username', $username);;
        $query->execute();
        return count($query->fetchAll(PDO::FETCH_ASSOC)) > 0;
    }

    /**
     * we use this method when we edit profile
     * update current user sessions
     */
    public function updateUserSession() {
        $current_user = $this->getCurrentUser();
        $query = $this->db->prepare('
            SELECT users.*, roles.type as role 
            FROM users, roles 
            WHERE users.id = :user AND users.role_id=roles.id');
        $query->bindValue(':user', $current_user['id']);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);

        if($row !== false) {
            session_start();
            unset($row['password']); // delete the password column
            $_SESSION['user'] = $row;
        }
    }

    /**
     *logout
     */
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
    }
}
