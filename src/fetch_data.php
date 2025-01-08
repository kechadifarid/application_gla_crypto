<?php
require_once 'CryptoManager.php';
require_once 'DatabaseConnection.php';

use App\CryptoManager;
use App\DatabaseConnection;

try {
    // Liste des cryptomonnaies à récupérer périodiquement
    $topCryptos = ['bitcoin', 'ethereum', 'tether', 'cardano', 'dogecoin', 'polygon', 'solana'];

    // Initialiser la connexion à la base de données
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Initialiser le gestionnaire de cryptomonnaies
    $cryptoManager = new CryptoManager();

    // Créer la table si elle n'existe pas
    echo $cryptoManager->createCryptoTable();

    while (true) { // Boucle infinie pour exécuter périodiquement
        foreach ($topCryptos as $crypto) {
            // Récupérer les métadonnées générales
            $meta_url = "https://api.coincap.io/v2/assets/$crypto";
            $meta_response = file_get_contents($meta_url);
            if ($meta_response === FALSE) {
                echo "Erreur lors de la récupération des métadonnées pour $crypto\n";
                continue;
            }
            $meta_data = json_decode($meta_response, true);
            if (!isset($meta_data['data'])) {
                echo "Erreur de décodage JSON ou données manquantes pour les métadonnées de $crypto\n";
                continue;
            }
            $name = $meta_data['data']['name'];
            $symbol = $meta_data['data']['symbol'];
            $rank = $meta_data['data']['rank'];

            // Récupérer le prix actuel
            $api_url = "https://api.coincap.io/v2/assets/$crypto";
            $response = file_get_contents($api_url);
            if ($response === FALSE) {
                echo "Erreur lors de la récupération des données actuelles pour $crypto\n";
                continue;
            }
            $data = json_decode($response, true);
            if (!isset($data['data'])) {
                echo "Erreur de décodage JSON ou données actuelles manquantes pour $crypto\n";
                continue;
            }

            $price_usd = $data['data']['priceUsd'];
            $last_updated = date('Y-m-d H:i:s');

            // Insérer les données sans mise à jour (enregistrement unique par période)
            $stmt = $conn->prepare(
                "INSERT INTO cryptocurrencies (name, symbol, price_usd, rank, last_updated) 
                VALUES (:name, :symbol, :price_usd, :rank, :last_updated) 
                ON CONFLICT (symbol, last_updated) DO NOTHING"
            );
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':symbol', $symbol);
            $stmt->bindParam(':price_usd', $price_usd);
            $stmt->bindParam(':rank', $rank);
            $stmt->bindParam(':last_updated', $last_updated);
            $stmt->execute();

            echo "Données insérées pour $crypto à $last_updated\n";
        }

        echo "Attente de 2 minutes avant la prochaine exécution...\n";
        sleep(120); // Attendre 2 minutes
    }
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
?>
