<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'src/DatabaseConnection.php';

use App\DatabaseConnection;

// Connexion à la base de données
$db = new DatabaseConnection();
$conn = $db->connect();

echo $_SESSION["user"];


if(isset($_POST["alerter"]) && isset($_SESSION["user"]))
{
    $query = "SELECT id FROM users WHERE username = :username";
    echo $_SESSION["user"];
$stmt = $conn->prepare($query);
$stmt->execute(['username' => $_SESSION["user"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Utilisateur non trouvé.");
}

$user_id = $user['id'];
$crypto_name = $_POST['crypto_name'];
$price_threshold = $_POST['price_threshold'] ?? null;

$query = "INSERT INTO alerts (user_id, crypto_name, price_threshold) 
          VALUES (:user_id, :crypto_name, :price_threshold)";
$stmt = $conn->prepare($query);
$stmt->execute([
    'user_id' => $user_id,
    'crypto_name' => $crypto_name,
    'price_threshold' => $price_threshold
]);

}
?>