<?php
include('Connection.php'); 

if (isset($_POST['cryptoName'])) {
    $cryptoName = $_POST['cryptoName'];

    // Connexion à la base de données
    $conn = getConnection();

    // Exemple pour récupérer les données basées sur le nom de la cryptomonnaie
    // Vous pouvez adapter cette requête selon vos besoins (ici, c'est pour Bitcoin, mais vous pouvez le faire pour chaque crypto)
    $stmt = $conn->prepare("SELECT * FROM info". $cryptoName ." ORDER BY date DESC LIMIT 30");
    $stmt->execute();

    // Récupérer les résultats
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dates = [];
    $prices = [];

    foreach ($data as $item) {
        $dates[] = $item['date'];
        $prices[] = $item['price_usd'];
    }

    // Renvoi des données sous forme JSON
    echo json_encode([
        'success' => true,
        'dates' => $dates,
        'prices' => $prices
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Aucune cryptomonnaie sélectionnée']);
}
?>
