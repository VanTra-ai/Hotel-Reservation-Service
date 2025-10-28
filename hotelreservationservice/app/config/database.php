<?php
class Database
{
    private $host = "localhost";
    private $db_name = "hotel_reservation";
    private $username = "root";
    private $password = "";
    public $conn;
    public function getConnection()
{
    $this->conn = null;
    try {
        $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
        $this->conn = new PDO($dsn, $this->username, $this->password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $exception) {
        echo "Connection error: " . $exception->getMessage();
    }
    return $this->conn;
}
}
