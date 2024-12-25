<?php
require_once 'src/databaseConnection.php';
require 'Exception.php';
require 'PHPMailer.php';
require 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

$db = new App\DatabaseConnection();
$conn = $db->connect();

// Récupérer toutes les alertes actives
$query = "SELECT alerts.*, users.email FROM alerts 
          JOIN users ON alerts.user_id = users.id 
          WHERE email_sent = FALSE";
$stmt = $conn->prepare($query);
$stmt->execute();
$alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($alerts as $alert) {
    try {
        // Récupération des données de la crypto-monnaie
        $cryptoData = fetchCryptoData($alert['crypto_name']);
        $currentPrice = floatval($cryptoData['priceUsd']);

        // Vérification du seuil de prix
        $sendEmail = false;
        if ($currentPrice >= $alert['price_threshold']) {
            $sendEmail = true;
        }

        // Envoi de l'email et mise à jour de la base de données si le seuil est atteint
        if ($sendEmail) {
            sendEmail($alert['email'], $alert['crypto_name'], $alert['price_threshold']);
            $updateQuery = "UPDATE alerts SET email_sent = TRUE WHERE id = :id";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->execute(['id' => $alert['id']]);
        }
    } catch (Exception $e) {
        // Log en cas d'erreur
        error_log("Erreur lors du traitement de l'alerte pour {$alert['crypto_name']}: " . $e->getMessage());
    }
}

// Fonction pour récupérer les données de l'API
function fetchCryptoData($cryptoName) {
    $apiUrl = "https://api.coincap.io/v2/assets/" . strtolower($cryptoName);
    $response = @file_get_contents($apiUrl); // Utilisation de @ pour éviter des warnings si la requête échoue

    if ($response === FALSE) {
        throw new Exception("Erreur lors de la récupération des données de l'API pour $cryptoName");
    }

    $data = json_decode($response, true);

    if (!isset($data['data']) || !isset($data['data']['priceUsd'])) {
        throw new Exception("Données invalides ou manquantes pour $cryptoName");
    }

    return $data['data'];
}

// Fonction pour envoyer un email
function sendEmail($email, $crypto, $price) {
    $mail = new PHPMailer(True);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Paramétrer le Mailer pour utiliser SMTP 
    $mail->SMTPAuth = true; // Activer authentication SMTP
    $mail->Username = 'kechadifarid10@gmail.com'; // Votre adresse email d'envoi
    $mail->Password = 'ttyngeouzlojtgac'; // Le mot de passe de cette adresse email
    
    
    $mail->setFrom('kechadifarid10@gmail.com', 'Mailer'); // Personnaliser l'envoyeur
    $mail->addAddress($email, 'kechadi farid'); // Ajouter le destinataire
    $mail->addReplyTo($email, 'Information'); // L'adresse de réponse
    
    $mail->isHTML(true); // Paramétrer le format des emails en HTML ou non
    
    $mail->Subject = 'Alerte personnalisée';
    $mail->Body = "La crypto-monnaie $crypto a dépassé le seuil de $price USD.";
    $mail->AltBody = "La crypto-monnaie $crypto a dépassé le seuil de $price USD.";
    
    if(!$mail->send()) {
       echo 'Erreur, message non envoyé.';
       echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
       echo 'Le message a bien été envoyé !';
    }
}
?>
