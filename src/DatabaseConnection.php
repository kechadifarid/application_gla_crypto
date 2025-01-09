<?php
namespace App;

use PDO;
use PDOException;

class DatabaseConnection {
    
    private $servername = "pedago01c.univ-avignon.fr";
    private $username = "uapv2200995";
    private $password = "xm4Quj";
    private $dbname = "etd";
    private $conn = null;

    public function connect() {
        if ($this->conn === null) {
            try {
                $this->conn = new PDO(
                    "pgsql:host={$this->servername};dbname={$this->dbname}",
                    $this->username,
                    $this->password
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new PDOException("Erreur de connexion : " . $e->getMessage(), $e->getCode(), $e);
            }
        }
        return $this->conn;
    }

    public function disconnect() {
        $this->conn = null;
    }
}

