<?php
namespace App;
require_once ('DatabaseConnection.php');
use App\DatabaseConnection;
class SetDataAllCrypto {
    private $conn;
    public function __construct() {
        // Établir une connexion via DatabaseConnection
        $db = new DatabaseConnection();
        $this->conn = $db->connect();
    }

   

public function truncateTable($crypto)
{
    $tableName = 'info' . preg_replace('/[^A-Za-z0-9_]/', '_', $crypto);
    $sql_mod = "
        TRUNCATE TABLE $tableName RESTART IDENTITY;
        ";
    
        // Exécution de la requête
        $this->conn->exec($sql_mod);
        return "Table 'infobitcoin' videe.";

}

// recuperation donnée avec filtrage 

public function getDataCrypto($date_debut,$date_fin,$crypto) {
    $tableName = 'info' . preg_replace('/[^A-Za-z0-9_]/', '_', $crypto);

    $date_debut = strtotime($date_debut)*1000;
    $date_fin = strtotime($date_fin)*1000;
    $crypto = strtolower($crypto);

    $api_url = "https://api.coincap.io/v2/assets/$crypto/history?interval=d1&start=$date_debut&end=$date_fin";

     // Récupérer les données depuis l'API
     $response = file_get_contents($api_url);
     if ($response === FALSE) {
         die("Erreur lors de la récupération des données de l'API pour $crypto");
     }
 
     // Décodage du JSON en tableau PHP
     $data = json_decode($response, true);
     if ($data === null) {
         die("Erreur de décodage JSON pour $crypto");
     }

     $this->truncateTable($crypto);

     if (isset($data['data']) && !empty($data['data'])) {
        // Parcourir les données et les insérer dans la base de données
        foreach ($data['data'] as $entry) {
            $price_usd = $entry['priceUsd'];
            $date = date('Y-m-d', $entry['time'] / 1000); // Convertir le timestamp en format date

            // Préparer la requête d'insertion
            $insert_sql = "
            INSERT INTO $tableName (price_usd, date)
            VALUES (:price_usd, :date)
            ";

            $insert_stmt = $this->conn->prepare($insert_sql);
            $insert_stmt->bindParam(':price_usd', $price_usd, \PDO::PARAM_STR);
            $insert_stmt->bindParam(':date', $date, \PDO::PARAM_STR);

            // Exécuter la requête d'insertion
            $insert_stmt->execute();
           
        }
    } else {
        echo "Aucune donnée disponible pour $crypto.<br>";
    }





}
}

?>