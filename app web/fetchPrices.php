<?php
// Inclure le fichier de connexion à la base de données
include('Connection.php');

try {
    // Connexion à la base de données via le fichier Connection.php
    $conn = getConnection(); // Cette fonction doit retourner la connexion PDO
    
    // Requête pour récupérer les 10 dernières cryptomonnaies
    $sql = "SELECT * FROM cryptocurrencies ORDER BY timestamp DESC LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Récupérer les résultats sous forme de tableau associatif
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Retourner les résultats sous forme de JSON
    echo json_encode($result);
}
catch(PDOException $e) {
    // Gestion des erreurs
    echo json_encode(['error' => 'Erreur lors de la récupération des données : ' . $e->getMessage()]);
}
?>
