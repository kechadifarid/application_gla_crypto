<?php
namespace App;
include('databaseConnection.php');

class CryptoManager {
    private $conn;

    public function __construct() {
        // Établir une connexion via DatabaseConnection
        $db = new DatabaseConnection();
        $this->conn = $db->connect();
    }

    // Méthode pour récupérer les données de l'API
    public function fetchCryptoDataFromAPI($api_url) {
        return $this->fetchData($api_url);  // Utilise la méthode interne fetchData
    }

    // Méthode pour récupérer les données de l'API via file_get_contents (à mocker dans les tests)
    protected function fetchData($url) {
        $response = file_get_contents($url);
        
        if ($response === FALSE) {
            throw new Exception("Erreur lors de la récupération des données de l'API");
        }

        $data = json_decode($response, true);

        if ($data === null) {
            throw new Exception("Erreur de décodage JSON");
        }

        return $data['data'];
    }

    // Méthode pour créer la table si elle n'existe pas
    public function createCryptoTable() {
        $sql = "
        CREATE TABLE IF NOT EXISTS cryptocurrencies (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100),
            symbol VARCHAR(10) UNIQUE,
            price_usd NUMERIC,
            rank INT
        );
        ";

        $this->conn->exec($sql);
        echo "La table 'cryptocurrencies' a été créée (ou existe déjà).<br>";
    }

    // Méthode pour insérer ou mettre à jour les données
    public function insertOrUpdateCryptos($cryptos) {
        $stmt = $this->conn->prepare("
            INSERT INTO cryptocurrencies (name, symbol, price_usd, rank) 
            VALUES (:name, :symbol, :price_usd, :rank)
            ON CONFLICT (symbol) DO UPDATE 
            SET price_usd = EXCLUDED.price_usd, rank = EXCLUDED.rank
        ");

        foreach ($cryptos as $crypto) {
            $stmt->bindParam(':name', $crypto['name']);
            $stmt->bindParam(':symbol', $crypto['symbol']);
            $stmt->bindParam(':price_usd', $crypto['priceUsd']);
            $stmt->bindParam(':rank', $crypto['rank']);
            $stmt->execute();
        }

        echo "Données insérées ou mises à jour avec succès!<br>";
    }
}
?>