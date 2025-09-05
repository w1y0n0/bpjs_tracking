<?php
require_once 'config.php';

class Database {
    private $host = DB_HOST;
    private $username = DB_USERNAME;
    private $password = DB_PASSWORD;
    private $database = DB_NAME;
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset to UTF-8
            $this->connection->set_charset("utf8");
            
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql) {
        $result = $this->connection->query($sql);
        if (!$result) {
            throw new Exception("Query error: " . $this->connection->error);
        }
        return $result;
    }
    
    public function prepare($sql) {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare error: " . $this->connection->error);
        }
        return $stmt;
    }
    
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }
    
    public function lastInsertId() {
        return $this->connection->insert_id;
    }
    
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    public function __destruct() {
        $this->close();
    }
}

// Create global database instance
$db = new Database();
?>

