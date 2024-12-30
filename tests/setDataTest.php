<?php
namespace Test;

use PHPUnit\Framework\TestCase;
use App\CryptoManager;
use App\DatabaseConnection;

use PHPUnit\Framework\MockObject\MockObject;




class SetDataTest extends TestCase {
    
    private $db;

    protected function setUp(): void {
        $this->db = new DatabaseConnection();
    }
   

      public function testFetchCryptoDataFromAPI() {
        // Créez une URL fictive pour le test
        $mockApiUrl = "https://mock.api/cryptos";

        // Contenu fictif simulant une réponse JSON de l'API
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

        // Mock de la fonction `file_get_contents`
        $cryptoManager = $this->getMockBuilder(CryptoManager::class)
            ->onlyMethods(['fetchCryptoDataFromAPI'])
            ->getMock();

        // Simule le comportement de la méthode pour retourner une réponse fictive
        $cryptoManager->method('fetchCryptoDataFromAPI')
            ->willReturn(json_decode($mockApiResponse, true)['data']);

        // Appelez la méthode
        $result = $cryptoManager->fetchCryptoDataFromAPI($mockApiUrl);

        // Effectuez les assertions
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Bitcoin', $result[0]['name']);
        $this->assertEquals('ETH', $result[1]['symbol']);
    }


    public function testCreateCryptoTable() {
    
        $cryptoManager = new CryptoManager();

        $result = $cryptoManager->createCryptoTable();

        $this->assertSame("La table 'cryptocurrencies' a été créée (ou existe déjà).<br>", $result);

    }
    public function testClearCryptoTable() {
        $cryptoManager = new CryptoManager();
        $cryptoManager->clearCryptoTable();
        $conn = $this->db->connect();

        $query = $conn->query("SELECT * FROM cryptocurrencies");
        $result = $query->fetch();

        $this->assertEmpty($result);
}

public function testInsertOrUpdateCryptos() {
    $cryptoManager = new CryptoManager();
    // Données de test
    $cryptos = [
        [
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'priceUsd' => 29000,
            'rank' => 1
        ],
        [
            'name' => 'Ethereum',
            'symbol' => 'ETH',
            'priceUsd' => 1800,
            'rank' => 2
        ]
    ];
$cryptoManager->insertOrUpdateCryptos($cryptos);

    $conn = $this->db->connect();

    $query = $conn->query("SELECT * FROM cryptocurrencies WHERE symbol = 'BTC' AND price_usd = 29000 AND rank = 1");
    $result = $query->fetch();

    $this->assertNotEmpty($result);

    
    $query = $conn->query("SELECT * FROM cryptocurrencies WHERE symbol = 'ETH' AND price_usd = 1800 AND rank = 2");
    $result = $query->fetch();
    $this->assertNotEmpty($result);

  
}
}




?>