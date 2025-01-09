<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use App\CryptoManager;
use App\DatabaseConnection;

class SetDataTest extends TestCase {
    private $db;

    protected function setUp(): void
    {
        $this->db = new DatabaseConnection();
        $conn = $this->db->connect();

        // Créez la table avant de commencer les tests
        $cryptoManager = new CryptoManager();
        $cryptoManager->createCryptoTable();
    }

    public function testFetchCryptoDataFromAPI_Success()
    {
        $mockApiUrl = "https://mock.api/cryptos";

        $mockApiResponse = json_encode([
            'data' => [
                [
                    'name' => 'Bitcoin',
                    'symbol' => 'BTC',
                    'priceUsd' => 29000,
                    'rank' => 1,
                ],
                [
                    'name' => 'Ethereum',
                    'symbol' => 'ETH',
                    'priceUsd' => 1800,
                    'rank' => 2,
                ],
            ],
        ]);

        $cryptoManager = $this->getMockBuilder(CryptoManager::class)
            ->onlyMethods(['getFileContents']) // Utilisez `getFileContents` à la place de `file_get_contents`
            ->getMock();

        $cryptoManager->method('getFileContents')
            ->willReturn($mockApiResponse);

        $result = $cryptoManager->fetchCryptoDataFromAPI($mockApiUrl);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Bitcoin', $result[0]['name']);
        $this->assertEquals('ETH', $result[1]['symbol']);
    }

    public function testFetchCryptoDataFromAPI_Failure()
    {
        $mockApiUrl = "https://mock.api/failure";

        $cryptoManager = $this->getMockBuilder(CryptoManager::class)
            ->onlyMethods(['getFileContents'])
            ->getMock();

        $cryptoManager->method('getFileContents')
            ->willReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Erreur lors de la récupération des données de l'API");

        $cryptoManager->fetchCryptoDataFromAPI($mockApiUrl);
    }

    public function testFetchCryptoDataFromAPI_InvalidJson()
    {
        $mockApiUrl = "https://mock.api/invalid-json";

        $cryptoManager = $this->getMockBuilder(CryptoManager::class)
            ->onlyMethods(['getFileContents'])
            ->getMock();

        $cryptoManager->method('getFileContents')
            ->willReturn('invalid json');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Erreur de décodage JSON");

        $cryptoManager->fetchCryptoDataFromAPI($mockApiUrl);
    }

    public function testCreateCryptoTable()
    {
        $cryptoManager = new CryptoManager();

        // Appeler la méthode createCryptoTable
        $result = $cryptoManager->createCryptoTable();

        // Vérifiez que la méthode retourne le message attendu
        $this->assertSame("La table 'cryptocurrencies' a été créée (ou existe déjà).<br>", $result);
    }

    public function testInsertOrUpdateCryptos()
    {
        $cryptoManager = new CryptoManager();

        // Données de test
        $cryptos = [
            [
                'name' => 'Bitcoin',
                'symbol' => 'BTC',
                'priceUsd' => 29000,
                'rank' => 1,
            ],
            [
                'name' => 'Ethereum',
                'symbol' => 'ETH',
                'priceUsd' => 1800,
                'rank' => 2,
            ],
        ];

        // Insérer ou mettre à jour les données
        $cryptoManager->insertOrUpdateCryptos($cryptos);

        // Vérifiez que les données ont été insérées
        $conn = $this->db->connect();

        foreach ($cryptos as $crypto) {
            $stmt = $conn->prepare("SELECT * FROM cryptocurrencies WHERE symbol = :symbol");
            $stmt->bindParam(':symbol', $crypto['symbol']);
            $stmt->execute();
            $result = $stmt->fetch();

            $this->assertNotEmpty($result, "Les données pour {$crypto['symbol']} n'ont pas été insérées.");
            $this->assertEquals($crypto['name'], $result['name']);
            $this->assertEquals($crypto['priceUsd'], $result['price_usd']);
            $this->assertEquals($crypto['rank'], $result['rank']);
        }
    }

    public function testGetFileContents() {
        // Chemin vers un fichier temporaire pour tester
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'Test content');
    
        $cryptoManager = new class extends \App\CryptoManager {
            public function callGetFileContents($url) {
                return $this->getFileContents($url);
            }
        };
    
        // Appel de la méthode protégée via une méthode publique
        $result = $cryptoManager->callGetFileContents($tempFile);
    
        // Vérifiez que le contenu est correctement lu
        $this->assertEquals('Test content', $result);
    
        // Supprimez le fichier temporaire
        unlink($tempFile);
    }
    

    protected function tearDown(): void
    {
        // Nettoyez la base de données après le test
        $conn = $this->db->connect();
        $conn->exec("DROP TABLE IF EXISTS cryptocurrencies");
    }
}
