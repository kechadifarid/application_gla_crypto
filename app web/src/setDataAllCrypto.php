<?php

include('Connection.php');

class SetDataAllCrypto {
    private $conn;
    public function __construct() {
        // Établir une connexion via DatabaseConnection
        $db = new DatabaseConnection();
        $this->conn = $db->connect();
    }

    public function GetDataMonthly() {
    
    $stmt = $this->conn->prepare("SELECT * FROM cryptocurrencies LIMIT 9");
    $stmt->execute();
    $cryptos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fonction pour récupérer les données depuis l'API
    foreach ($cryptos as $crypto) {
        // Assurez-vous de nettoyer le nom de la crypto avant de l'utiliser dans le nom de la table
        $tableName = 'info' . preg_replace('/[^A-Za-z0-9_]/', '_', $crypto['name']); // Remplacer les caractères invalides
    
        // Créer la requête de création de la table (si elle n'existe pas déjà)
        $sql = "
        CREATE TABLE IF NOT EXISTS $tableName (
            id SERIAL PRIMARY KEY,
            price_usd NUMERIC,
            date DATE
        );
        ";
    
        // Exécution de la requête
        $this->conn->exec($sql);
        echo "La table '$tableName' a été créée (ou existe déjà).<br>";
    
        // Normaliser le nom de la crypto pour l'API (en minuscules)
        $cryptoName = strtolower($crypto['name']);
    
        // Construire l'URL de l'API pour obtenir les données de la crypto du mois dernier
        $api_url = "https://api.coincap.io/v2/assets/$cryptoName/history?interval=d1&start=" . strtotime("1 month ago") * 1000 . "&end=" . time() * 1000;
        echo "URL API : $api_url<br>";
    
        // Récupérer les données depuis l'API
        $response = file_get_contents($api_url);
        if ($response === FALSE) {
            die("Erreur lors de la récupération des données de l'API pour $cryptoName");
        }
    
        // Décodage du JSON en tableau PHP
        $data = json_decode($response, true);
        if ($data === null) {
            die("Erreur de décodage JSON pour $cryptoName");
        } else {
            echo "Données obtenues avec succès pour $cryptoName!<br>";
        }
    
        // Vérifier si les données existent dans la réponse de l'API
        if (isset($data['data']) && !empty($data['data'])) {
            // Parcourir les données et les insérer dans la base de données
            foreach ($data['data'] as $entry) {
                $price_usd = $entry['priceUsd'];
                $date = date('Y-m-d', $entry['time'] / 1000); // Convertir le timestamp en format date
    
                // Préparer la requête d'insertion
                $insert_sql = "
                INSERT INTO $tableName (price_usd, date)
                VALUES (:price_usd, :date)
                ";
    
                $insert_stmt = $this->conn->prepare($insert_sql);
                $insert_stmt->bindParam(':price_usd', $price_usd, PDO::PARAM_STR);
                $insert_stmt->bindParam(':date', $date, PDO::PARAM_STR);
    
                // Exécuter la requête d'insertion
                $insert_stmt->execute();
                echo "Données insérées pour la date $date (Prix: $price_usd USD)<br>";
            }
        } else {
            echo "Aucune donnée disponible pour $cryptoName.<br>";
        }
    }
}
}

?>
