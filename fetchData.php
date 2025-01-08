<?php
header('Content-Type: application/json');

// Inclure la connexion à la base de données
require_once 'src/DatabaseConnection.php';
use App\DatabaseConnection;

// Récupérer les paramètres de la requête
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;
$crypto_symbol = $_GET['crypto'] ?? null;

if (!$start_date || !$end_date || !$crypto_symbol) {
    echo json_encode(['error' => 'Paramètres manquants']);
    exit;
}

try {
    // Connexion à la base de données
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Requête SQL pour récupérer les données entre les dates spécifiées
    $query = "
        SELECT last_updated AS date, price_usd 
        FROM cryptocurrencies 
        WHERE symbol = :symbol 
        AND last_updated BETWEEN :start_date AND :end_date 
        ORDER BY last_updated ASC
    ";
    $stmt = $conn->prepare($query);

    // Formater les dates au format attendu par la base de données
    $formatted_start_date = date('Y-m-d 00:00:00', strtotime($start_date));
    $formatted_end_date = date('Y-m-d 23:59:59', strtotime($end_date));
    

    $stmt->bindParam(':symbol', $crypto_symbol);
    $stmt->bindParam(':start_date', $formatted_start_date);
    $stmt->bindParam(':end_date', $formatted_end_date);
    $stmt->execute();

    // Récupérer les données
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($data) {
        $dates = [];
        $prices = [];
        $heatmap = [];

        foreach ($data as $entry) {
            $dates[] = $entry['date'];
            $prices[] = $entry['price_usd'];
            $heatmap[] = [
                'x' => strtotime($entry['date']),
                'y' => rand(0, 10), // Exemple de rangée arbitraire pour la heatmap
                'value' => $entry['price_usd']
            ];
        }

        echo json_encode([
            'dates' => $dates,
            'prices' => $prices,
            'heatmap' => $heatmap
        ]);
    } else {
        echo json_encode(['error' => 'Aucune donnée trouvée']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Erreur : ' . $e->getMessage()]);
}
?>
