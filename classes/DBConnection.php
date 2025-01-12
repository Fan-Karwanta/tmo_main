<?php
// Check if the constant is defined to prevent multiple inclusions
if (!defined('DB_SERVER')) {
    require_once("../initialize.php"); // Assuming initialize.php contains relevant configuration
}

class DBConnection {
    // Database credentials
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "tmo";
    
    public $conn;

    // Constructor to initialize database connection
    public function __construct() {
        if (!isset($this->conn)) {
            // Attempt to connect using mysqli
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
            
            // Check for connection errors
            if ($this->conn->connect_error) {
                die("Database connection failed: " . $this->conn->connect_error);
            }
        }
    }

    // Destructor to close the database connection
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
