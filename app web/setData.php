<?php
include('Connection.php');
try {
    $conn = getConnection();
    // URL de l'API
    $api_url = "https://api.coincap.io/v2/assets"; // Remplacez par l'URL de l'API

    // Récupération des données de l'API
    $response = file_get_contents($api_url);
    if ($response === FALSE) {
        die("Erreur lors de la récupération des données de l'API");
    }

    // Décodage du JSON en tableau PHP
    $data = json_decode($response, true);
    if ($data === null) {
        die("Erreur de décodage JSON");
    }
    else {
        echo "Données obtenues avec succès!";
    }


    $sql = "
    CREATE TABLE IF NOT EXISTS cryptocurrencies (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100),
        symbol VARCHAR(10) UNIQUE,
        price_usd NUMERIC,
        rank INT
    );
    ";

    // Exécution de la requête
    $conn->exec($sql);
    echo "La table 'cryptocurrencies' a été créée (ou existe déjà).";

    // Préparation de la requête d'insertion
    $stmt = $conn->prepare("
        INSERT INTO cryptocurrencies (name, symbol, price_usd, rank) 
        VALUES (:name, :symbol, :price_usd, :rank)
        ON CONFLICT (symbol) DO UPDATE 
        SET price_usd = EXCLUDED.price_usd, rank = EXCLUDED.rank
    ");

    // Parcourir les données et insérer dans la base de données
    foreach ($data['data'] as $crypto) {
        $stmt->bindParam(':name', $crypto['name']);
        $stmt->bindParam(':symbol', $crypto['symbol']);
        $stmt->bindParam(':price_usd', $crypto['priceUsd']);
        $stmt->bindParam(':rank', $crypto['rank']);
        $stmt->execute();
    }

    echo "Données insérées ou mises à jour avec succès!<br>";
} catch (Exception $e) {
    // Gestion des erreurs générales
    echo "Erreur générale : " . $e->getMessage();
}

?>