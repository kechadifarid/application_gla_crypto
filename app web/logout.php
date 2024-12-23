<?php
if(isset($_POST["deconnecter"]))
{
    
// D�truire toutes les donn�es de session
session_unset();

// D�truire la session
session_destroy();

// Rediriger l'utilisateur vers la page d'accueil ou une autre page
header("Location: index.php");
exit();
}
?>