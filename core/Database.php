<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh;
    private $stmt;

    public function __construct() {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        $options = [
            PDO::ATTR_PERSISTENT => true, 
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ  // ← This fixes the array issue!
        ];
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            die('Database Connection Error: ' . $e->getMessage());
        }
    }

    // Add connect method for User model compatibility
    public function connect() {
        return $this->dbh;
    }

    public function query($sql) { 
        $this->stmt = $this->dbh->prepare($sql); 
    }

    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            $type = match(true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute() { 
        return $this->stmt->execute(); 
    }

    public function single() { 
        $this->execute(); 
        return $this->stmt->fetch(); // Now returns FETCH_ASSOC by default
    }

    public function resultSet() { 
        $this->execute(); 
        return $this->stmt->fetchAll(); // Now returns FETCH_ASSOC by default
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }

    // Get last insert ID
    public function lastInsertId() {
    return $this->dbh->lastInsertId();
}
}
