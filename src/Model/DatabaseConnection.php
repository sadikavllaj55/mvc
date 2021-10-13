<?php

namespace App\Model;

use PDO;

final class DatabaseConnection {
    private static $instance = null;
    private static $connection;

    public static function getInstance() {
        if (is_null(self::$instance)){
            self::$instance = new DatabaseConnection();
        }

        return self::$instance;
    }

    public static function connect($host, $dbName, $user, $password){
        self::$connection = new PDO("mysql:dbname=$dbName;host=$host", $user, $password);
    }

    /**
     * @return PDO
     */
    public function getConnection() {
        return self::$connection;
    }
}