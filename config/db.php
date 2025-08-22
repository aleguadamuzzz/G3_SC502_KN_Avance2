<?php
class Database
{
    private static $host = 'localhost';
    private static $username = 'root';
    private static $password = '';
    private static $database = 'red_alimentos';

    public static function connect()
    {
        $conn = new mysqli(self::$host, self::$username, self::$password, self::$database);

        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }

        $conn->set_charset("utf8");
        return $conn;
    }
}