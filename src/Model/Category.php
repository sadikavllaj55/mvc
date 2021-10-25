<?php

namespace App\Model;

use PDO;

class Category
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

    public static function getCategoryList()
    {
        self::connect();
        $query = self::$db->query('
            SELECT * 
            FROM categories
        ');

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        self::connect();
        $query = self::$db->prepare('
            SELECT *
            FROM categories 
            WHERE id=:category
        ');

        $query->bindParam(':category', $id, PDO::PARAM_INT);
        $query->execute();

        return $query->fetch(PDO::FETCH_ASSOC);
    }
}
