<?php
require_once('src/DatabaseConnection.php');

use App\DatabaseConnection;

$db = new DatabaseConnection();
$conn = $db->connect();

if (isset($_POST["inscrire"])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
    $stmt->execute(['username' => $username, 'email' => $email]);
    if ($stmt->rowCount() > 0) {
        echo '<script>
                alert("Nom d\'utilisateur ou email déjà utilisé.");
                setTimeout(function() {
                    window.location.href = "index.php";
                }, 2000); 
              </script>';
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
    $stmt->execute(['username' => $username, 'email' => $email, 'password' => $password]);

    $_SESSION["user"] = $username;

    echo '<script>
            alert("Inscription réussie !");
            setTimeout(function() {
                window.location.href = "index.php";
            }, 2000);
          </script>';
    exit;
}
?>
