<?php
class Database {
    private $host = "sql100.infinityfree.com";
    private $username = "if0_40600774";
    private $password = "Gxsm0iUq0kbA";
    private $database = "if0_40600774_pos_system";
    public $conn;
    
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
            $this->conn->set_charset("utf8mb4");
        } catch(Exception $e) {
            echo "Connection error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>