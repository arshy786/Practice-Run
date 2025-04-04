<?php

class DB {
    private $host = "213.171.200.37";
    private $dbname = "aaslam";
    private $user = "aaslam";
    private $password = "Password20*";
    private $conn;

    public function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION sql_mode='STRICT_ALL_TABLES'"
            ];
            
            $this->conn = new PDO($dsn, $this->user, $this->password, $options);
            return $this->conn;
            
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("An error occurred. Please try again later.");
        }
    }
}
?>

