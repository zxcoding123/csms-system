<?php
class Database
{
    private static $instance = null;


    public static function getConnection()
    {
        if (self::$instance === null) {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? 'csms_system';
            $username = $_ENV['DB_USER'] ?? 'root';
            $password = $_ENV['DB_PASS'] ?? '';

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            self::$instance = new PDO($dsn, $username, $password, $options);
        }

        return self::$instance;
    }
}
