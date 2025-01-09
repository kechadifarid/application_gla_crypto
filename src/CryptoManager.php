<?php
namespace App;
require_once ('DatabaseConnection.php');
use App\DatabaseConnection;

class CryptoManager {
    private $conn;

    public function __construct() {
        // Établir une connexion via DatabaseConnection
        $db = new DatabaseConnection();
        $this->conn = $db->connect();
    }

    // Méthode pour récupérer les données de l'API
    public function fetchCryptoDataFromAPI($api_url) {
        $response = $this->getFileContents($api_url);

        if ($response === FALSE) {
            throw new \Exception("Erreur lors de la récupération des données de l'API");
        }

        $data = json_decode($response, true);

        if ($data === null) {
            throw new \Exception("Erreur de décodage JSON");
        }

        return $data['data'];
    }

    // Méthode pour créer la table si elle n'existe pas
    public function createCryptoTable() {
        $sql = "
CREATE TABLE IF NOT EXISTS cryptocurrencies (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100),
    symbol VARCHAR(10) UNIQUE, -- Ajout de la contrainte UNIQUE
    price_usd NUMERIC,
    rank INT,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";


        $this->conn->exec($sql);
        return "La table 'cryptocurrencies' a été créée (ou existe déjà).<br>";
    }

    // Méthode pour insérer ou mettre à jour les données
    public function insertOrUpdateCryptos($cryptos) {
        $stmt = $this->conn->prepare("
            INSERT INTO cryptocurrencies (name, symbol, price_usd, rank, last_updated) 
            VALUES (:name, :symbol, :price_usd, :rank, :last_updated)
            ON CONFLICT (symbol) DO UPDATE 
            SET price_usd = EXCLUDED.price_usd, 
                rank = EXCLUDED.rank, 
                last_updated = EXCLUDED.last_updated
        ");

        $currentTime = date('Y-m-d H:i:s'); // Récupérer le moment actuel

        foreach ($cryptos as $crypto) {
            $stmt->bindParam(':name', $crypto['name']);
            $stmt->bindParam(':symbol', $crypto['symbol']);
            $stmt->bindParam(':price_usd', $crypto['priceUsd']);
            $stmt->bindParam(':rank', $crypto['rank']);
            $stmt->bindParam(':last_updated', $currentTime);
            $stmt->execute();
        }
    }
    protected function getFileContents($url) {
        return file_get_contents($url);
    }
}
?>
