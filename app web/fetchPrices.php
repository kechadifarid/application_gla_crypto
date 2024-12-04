<?php
// Connection.php doit contenir les informations de connexion
include('Connection.php');

try {
    // Requête pour récupérer les noms et prix des cryptomonnaies
    $stmt = $myPDO->query("SELECT name, price_usd FROM cryptocurrencies LIMIT 10");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retourner les données au format JSON
    header('Content-Type: application/json');
    echo json_encode($data);
} catch (Exception $e) {
    // Retourne un message d'erreur si la requête échoue
    echo json_encode(['error' => $e->getMessage()]);
}
?>
