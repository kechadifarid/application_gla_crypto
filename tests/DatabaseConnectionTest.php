<?php
namespace Test;

use PHPUnit\Framework\TestCase;
use App\DatabaseConnection;
use PDOException;

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

    public function testConnectionFailure() {
        $reflection = new \ReflectionClass($this->db);

        $servernameProperty = $reflection->getProperty('servername');
        $servernameProperty->setAccessible(true);
        $servernameProperty->setValue($this->db, 'invalid-host');

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('Erreur de connexion');

        $this->db->connect();
    }

    protected function tearDown(): void {
        $this->db->disconnect();
    }
}
