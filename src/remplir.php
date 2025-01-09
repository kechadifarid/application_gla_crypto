<?php
require_once 'DatabaseConnection.php';
require_once 'CryptoManager.php';
use App\DatabaseConnection;
use App\CryptoManager;

try {
    $db = new DatabaseConnection();
    $cryptoManager = new CryptoManager();
    $conn = $db->connect();

    $cryptoManager->createCryptoTable();

    // Définir les dates de début et de fin
    $date_debut = strtotime('2024-12-15') * 1000;
    $date_fin = strtotime('2025-01-08') * 1000;

    // Liste des cryptomonnaies à récupérer
    $topCryptos = ['bitcoin', 'ethereum', 'tether','cardano', 'dogecoin', 'polygon', 'solana'];

    foreach ($topCryptos as $crypto) {
        // Récupérer les métadonnées générales
        $meta_url = "https://api.coincap.io/v2/assets/$crypto";
        $meta_response = file_get_contents($meta_url);
        if ($meta_response === FALSE) {
            die("Erreur lors de la récupération des métadonnées pour $crypto");
        }
        $meta_data = json_decode($meta_response, true);
        if (!isset($meta_data['data'])) {
            die("Erreur de décodage JSON ou données manquantes pour les métadonnées de $crypto");
        }
        $name = $meta_data['data']['name'];
        $symbol = $meta_data['data']['symbol'];
        $rank = $meta_data['data']['rank'];

        // Récupérer l'historique des prix
        $api_url = "https://api.coincap.io/v2/assets/$crypto/history?interval=d1&start=$date_debut&end=$date_fin";
        $response = file_get_contents($api_url);
        if ($response === FALSE) {
            die("Erreur lors de la récupération des données historiques pour $crypto");
        }
        $data = json_decode($response, true);
        if ($data === null || !isset($data['data'])) {
            die("Erreur de décodage JSON ou données historiques manquantes pour $crypto");
        }

        // Parcourir les données historiques et insérer dans la table
        foreach ($data['data'] as $entry) {
            $price_usd = $entry['priceUsd'];
            $last_updated = date('Y-m-d H:i:s', $entry['time'] / 1000);

            // Préparer et exécuter la requête d'insertion avec la contrainte UNIQUE (symbol, last_updated)
            $stmt = $conn->prepare("
    INSERT INTO cryptocurrencies (name, symbol, price_usd, rank, last_updated) 
    VALUES (:name, :symbol, :price_usd, :rank, :last_updated)
    ON CONFLICT (symbol, last_updated) DO NOTHING
");

            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':symbol', $symbol);
            $stmt->bindParam(':price_usd', $price_usd);
            $stmt->bindParam(':rank', $rank);
            $stmt->bindParam(':last_updated', $last_updated);
            $stmt->execute();
        }
    }

    echo "Toutes les données des cryptomonnaies ont été insérées avec succès !\n";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
?>
