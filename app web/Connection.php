<?php
// Informations de connexion à la base de données PostgreSQL
$servername = "pedago01c.univ-avignon.fr"; // Nom d'hôte du serveur de la base de données
$username = "uapv2200995";                 // Nom d'utilisateur de la base de données
$password = "xm4Quj";                      // Mot de passe de la base de données
$dbname = "etd";                           // Nom de la base de données

// DSN (Data Source Name) pour PostgreSQL
$dsn = "pgsql:host=$servername;dbname=$dbname";

// Tentative de connexion à la base de données avec PDO
try {
    // Création de l'instance PDO
    $myPDO = new PDO($dsn, $username, $password);
    
    // Définir le mode d'erreur de PDO sur exception
    $myPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion à la base de données réussie!<br>";

    // Exécution de la requête pour récupérer toutes les cryptomonnaies
    $stmt = $myPDO->query("SELECT * FROM cryptocurrencies");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Utilisation de foreach pour itérer sur les résultats
    foreach ($rows as $row) {
        echo "Nom: " . $row["name"] . "<br>"; // Affichage du nom de chaque cryptomonnaie
    }

} catch (PDOException $e) {
    // Si une erreur se produit lors de la connexion
    echo "Erreur de connexion : " . $e->getMessage();
}
?>
