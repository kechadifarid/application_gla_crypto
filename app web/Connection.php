<?php
// Définir les informations de connexion à la base de données
$servername = "pedago01c.univ-avignon.fr";
$username = "uapv2200995";
$password = "xm4Quj";
$dbname = "etd";

// Créer et retourner la connexion
function getConnection() {
    global $servername, $username, $password, $dbname;

    try {
        // Créer et retourner une connexion PDO
        $conn = new PDO("pgsql:host=$servername;dbname=$dbname", $username, $password);
        // Définir le mode d'erreur PDO pour lever les exceptions
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        // Gérer l'erreur en cas d'échec de connexion
        die("Erreur de connexion : " . $e->getMessage());
    }

    
}
?>
