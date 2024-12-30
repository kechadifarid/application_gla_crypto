<?php
namespace Test;

use PHPUnit\Framework\TestCase;
use App\DatabaseConnection; 

class DatabaseConnectionTest extends TestCase {
    
    private $db;

    protected function setUp(): void {
        $this->db = new DatabaseConnection();
    }

    public function testConnection() {
        $conn = $this->db->connect();
        $this->assertNotNull($conn, "La connexion ne doit pas être nulle.");

        $query = $conn->query("SELECT 1");
        $result = $query->fetch();
        $this->assertNotEmpty($result, "La requête test n'a retourné aucun résultat.");
    }

    protected function tearDown(): void {
        $this->db->disconnect();
    }
}
