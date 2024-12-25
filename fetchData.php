<?php
header('Content-Type: application/json');

// Inclure la connexion à la base de données
require_once 'src/databaseConnection.php';
require_once 'src/setDataAllCrypto.php';
use App\DatabaseConnection;

// Récupérer les paramètres de la requête
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$crypto_name = $_GET['crypto'] ?? null;

if (!$start_date || !$end_date || !$crypto_name) {
    echo json_encode(['error' => 'Paramètres manquants']);
    exit;
}

try {
    // Connexion à la base de données
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $setDataAll = new SetDataAllCrypto();
    $setDataAll->getDataCrypto($start_date, $end_date, $crypto_name);

    // Nom de la table de la cryptomonnaie
    $tableName = 'info' . preg_replace('/[^A-Za-z0-9_]/', '_', $crypto_name);

    // Requête SQL pour récupérer les données entre les dates spécifiées
    $query = "SELECT date, price_usd FROM $tableName WHERE date BETWEEN :start_date AND :end_date ORDER BY date ASC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();

    // Récupérer les données
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($data) {
        $dates = [];
        $prices = [];

        foreach ($data as $entry) {
            $dates[] = $entry['date'];
            $prices[] = $entry['price_usd'];
        }

        echo json_encode([
            'dates' => $dates,
            'prices' => $prices
        ]);
    } else {
        echo json_encode(['error' => 'Aucune donnée trouvée']);
    }

} catch (Exception $e) {
    // En cas d'erreur, renvoyer un message d'erreur
    echo json_encode(['error' => 'Erreur lors de la récupération des données : ' . $e->getMessage()]);
}
?>
