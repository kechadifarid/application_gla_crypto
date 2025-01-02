<?php
require_once('src/DatabaseConnection.php');
session_start();

use App\DatabaseConnection;

// Connexion à la base de données
$db = new DatabaseConnection();
$conn = $db->connect();

if (isset($_POST["connecter"])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Rechercher l'utilisateur par nom d'utilisateur
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification du mot de passe
    if ($user != NULL && password_verify($password, $user['password'])) {
        
        $_SESSION["user"] = $username;
        header("Location: index.php");
        
        exit;
    } else {
        // Si l'utilisateur ou le mot de passe est incorrect
        echo "Nom d'utilisateur ou mot de passe incorrect.";
        exit;
    }
}
?>
