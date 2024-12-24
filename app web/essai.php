<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);
try {
    // Serveur SMTP de Gmail
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'kechadifarid10@gmail.com';
    $mail->Password = 'ttyngeouzlojtgac';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Destinataire
    $mail->setFrom('farid.kechadi@alumni.univ-avignon.fr', 'Nom de l\'expéditeur');
    $mail->addAddress('farid.kechadi@alumni.univ-avignon.fr');

    // Contenu de l'e-mail
    $mail->isHTML(true);
    $mail->Subject = 'Sujet de l\'e-mail';
    $mail->Body    = 'Contenu de l\'e-mail';

    $mail->send();
    echo 'L\'e-mail a été envoyé avec succès.';
} catch (Exception $e) {
    echo "Erreur de l'envoi de l'e-mail : {$mail->ErrorInfo}";
}
?>