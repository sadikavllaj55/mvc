<?php

namespace App\Model;

use PDO;

class Roles
{
    /** @var null|PDO $db  */
    private static $db = null;

    private static function connect() {
        if (!is_null(self::$db)) {
            return;
        }
        $db = DatabaseConnection::getInstance();
        $db_config = CONFIG['database'];
        $db::connect($db_config['host'], $db_config['dbname'], $db_config['username'], $db_config['password']);
        self::$db = $db->getConnection();
    }

    public static function getAll()
    {
        self::connect();
        $query = self::$db->query('
            SELECT * 
            FROM roles
        ');

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        self::connect();
        $query = self::$db->prepare('
            SELECT *
            FROM roles 
            WHERE id=:role
        ');

        $query->bindParam(':role', $id, PDO::PARAM_INT);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }
}
