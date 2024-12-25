<?php 
require_once ('src/databaseConnection.php');
require_once ('src/setData.php');
require_once ('src/setDataAllCrypto.php');
require_once ('login.php');
require_once ('register.php');
require_once ('logout.php');

use App\DatabaseConnection;

// Créer une instance de la classe
$db = new DatabaseConnection();
$conn = $db->connect();

$setData = new CryptoManager();
$setDataAll = new SetDataAllCrypto();

// 1. Vider la table 'cryptocurrencies' avant d'insérer les nouvelles données
$setData->clearCryptoTable();

// 2. Récupérer les données depuis l'API CoinCap
$api_url = "https://api.coincap.io/v2/assets";  // URL de l'API CoinCap
$cryptoData = $setData->fetchCryptoDataFromAPI($api_url);

// 3. Insérer ou mettre à jour les données dans la base de données
$setData->insertOrUpdateCryptos($cryptoData);

// Gestion des filtres de date
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

$query = "SELECT * FROM cryptocurrencies LIMIT 8";
$stmt = $conn->prepare($query);
$stmt->execute();
$cryptos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CryptoTracker - Tableau</title>
    <style>
      body {
    margin: 0;
    font-family: 'Arial', sans-serif;
    background-color: #f5f5f5;
    color: #333;
}

header {
    display: flex;
    justify-content: space-between;
    align-items: center; /* Aligne verticalement tous les éléments dans le header */
    padding: 15px 30px;
    background-color: #333;
    color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.logo {
    display: flex; /* Permet d'aligner le logo et le texte horizontalement */
    align-items: center; /* Centre verticalement le texte par rapport au logo */
}

.logo img {
    height: 40px;
    margin-right: 10px; /* Ajoute un espace entre l'image et le texte */
}

.site-name {
    font-size: 24px;
    font-weight: bold;
}

/* Formulaire de connexion / inscription */
form {
    background-color: white;
    padding: 20px;
    margin: 20px auto;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    width: 90%;
    max-width: 500px;
    box-sizing: border-box;  /* Ajouté pour que le padding ne dépasse pas la largeur définie */
}

form h2 {
    margin-bottom: 20px;
    font-size: 24px;
}

form label {
    font-weight: bold;
    margin-bottom: 8px;
    display: block;
    font-size: 16px; /* Améliorer la lisibilité */
}

form input[type="text"],
form input[type="password"],
form input[type="email"],
form input[type="date"] {
    width: 100%; /* Maintenir la largeur à 100% de la largeur du formulaire */
    max-width: 100%; /* S'assurer que la largeur ne dépasse pas celle du formulaire */
    box-sizing: border-box; /* Cela permet d'inclure le padding dans la largeur totale */
    padding: 12px;
    margin: 8px 0 16px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
    transition: all 0.3s ease-in-out;
}

form input[type="text"]:focus,
form input[type="password"]:focus,
form input[type="email"]:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
}

form button {
    width: 100%;
    padding: 12px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 18px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form button:hover {
    background-color: #45a049;
}

.form-footer {
    text-align: center;
    font-size: 16px;
}

.form-footer a {
    color: #4CAF50;
    text-decoration: none;
}

.form-footer a:hover {
    text-decoration: underline;
}

/* Déconnexion */
#logoutForm button {
    background-color: #f44336;
    padding: 10px 20px;
    font-size: 16px;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

#logoutForm button:hover {
    background-color: #e53935;
}

/* Table des cryptomonnaies */
table {
    width: 80%;
    margin: 20px auto;
    border-collapse: collapse;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

th, td {
    padding: 15px;
    text-align: center;
    border: 1px solid #ddd;
}

th {
    background-color: #4CAF50;
    color: white;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}

tr:hover {
    background-color: #ddd;
}

/* Animation de transition pour les formulaires */
.form-container {
    transition: opacity 0.3s ease;
}

.form-container.hidden {
    opacity: 0;
    pointer-events: none;
}

/* Ajout de style pour la gestion du graphique */
#graphContainer {
    display: none;
    margin: 20px auto;
    width: 80%;
    max-width: 900px;
    text-align: center;
}

#cryptoChart {
    width: 100%;
    height: 400px;
    max-width: 800px;
    margin: 0 auto;
}
    </style>
</head>
<body>
<header>
        <div class="logo">
            <img src="images/crypto.png" alt="Logo du site">
            <div class="site-name">CryptoTracker</div>
        </div>

        <?php if (isset($_SESSION["user"])): ?>
            <form id="logoutForm" method="POST">
                <button type="submit" name="deconnecter">
                    Déconnexion
                </button>
            </form>
        <?php endif; ?>
    </header>


    <?php
    if (!isset($_SESSION["user"]))
    {
    ?>

    <!-- Formulaire de connexion -->
    <h2>Connexion</h2>
    <form id="loginForm" method="post">
        <label for="loginUsername">Nom d'utilisateur :</label>
        <input type="text" id="loginUsername" name="username" required>

        <label for="loginPassword">Mot de passe :</label>
        <input type="password" id="loginPassword" name="password" required>

        <button type="submit" name="connecter">Se connecter</button>

        <p><a href="javascript:void(0);" id="showSignupForm">Pas encore inscrit ? Inscrivez-vous ici</a></p>
    </form>

    <!-- Formulaire d'inscription (initialement masqué) -->
    <h2 id="signupHeading" style="display: none;">Inscription</h2>
    <form id="signupForm" method="post" action="register.php" style="display: none;">
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username" required>

        <label for="email">Email :</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" name="inscrire">S'inscrire</button>

        <p><a href="javascript:void(0);" id="showSignForm">Avez-vous un compte ? Connectez-vous</a></p>
    </form>

    <?php
    }
    ?>

    <h1>Tableau des Cryptomonnaies</h1>

   
    <!-- Formulaire de sélection des dates -->
    <div id="dateFormContainer" style="display: none;">
        <form id="dateForm" method="get" action="">
            <label for="start_date">Date de début :</label>
            <input type="date" id="start_date" name="start_date"?>

            <label for="end_date">Date de fin :</label>
            <input type="date" id="end_date" name="end_date"?>

            <button type="submit" name="filtrer">Filtrer</button>
        </form>
    </div>

    <?php
    if(isset($_SESSION["user"]))
    {
    ?>
    <!-- Tableau des cryptomonnaies -->
    <table id="cryptoTable">
        <caption>Liste des Cryptomonnaies</caption>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Symbol</th>
                <th>Prix (USD)</th>
                <th>+</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($cryptos as $crypto) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($crypto['name']) . "</td>";
                echo "<td>" . htmlspecialchars($crypto['symbol']) . "</td>";
                echo "<td>" . htmlspecialchars($crypto['price_usd']) . "</td>";
                echo "<td><a href='#' class='showGraph' data-crypto='" . htmlspecialchars($crypto['name']) . "'>Plus d'informations</a></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    <?php
    }
    ?>
   <?php
    if(isset($_SESSION["user"]))
    {
    ?>
<form id="alertForm" method="post" action="saveAlert.php">
<h2>Configurer une alerte</h2>
    <label for="cryptoName">Nom de la cryptomonnaie :</label>
    <input type="text" id="cryptoName" name="crypto_name" required>

    <label for="priceThreshold">Seuil de prix (USD) :</label>
    <input type="number" id="priceThreshold" name="price_threshold" step="0.00000001">

    <button type="submit" name="alerter">Enregistrer l'alerte</button>
</form>
<?php
    }
    ?>
    <div id="graphContainer" class="chart-container">
        <canvas id="cryptoChart"></canvas>
   </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
       
       document.addEventListener('DOMContentLoaded', function () {

       
    
    // Variable pour stocker le nom de la cryptomonnaie sélectionnée
    let selectedCryptoName = '';

    // Assurez-vous que chaque lien fonctionne correctement
    document.querySelectorAll('.showGraph').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();  // Empêche le comportement par défaut du lien

            // Récupérer la valeur de l'attribut 'data-crypto' du lien cliqué
            selectedCryptoName = link.getAttribute('data-crypto');
            console.log("Cryptomonnaie sélectionnée :", selectedCryptoName);

            // Masquer le tableau et afficher le graphique
            document.getElementById('cryptoTable').style.display = 'none';
            document.getElementById('graphContainer').style.display = 'block';
            document.getElementById('dateFormContainer').style.display = 'block';
        });
    });

    // Gestion de la soumission du formulaire de dates
    document.getElementById('dateForm').addEventListener('submit', function (event) {
        event.preventDefault();  // Empêche la soumission normale du formulaire

        var startDate = document.getElementById('start_date').value;
        var endDate = document.getElementById('end_date').value;

        // Vérifier que les dates sont remplies
        if (!startDate || !endDate) {
            alert("Veuillez sélectionner les deux dates.");
            return;
        }

        // Vérifier que cryptoName est défini avant de faire la requête
        if (!selectedCryptoName) {
            alert("Aucune cryptomonnaie sélectionnée.");
            return;
        }

        console.log("Nom de la crypto :", selectedCryptoName);
        console.log("Dates sélectionnées :", startDate, endDate);

        // Effectuer la requête fetch avec les paramètres start_date, end_date et crypto
        fetch(`fetchData.php?start_date=${startDate}&end_date=${endDate}&crypto=${selectedCryptoName}`)
            .then(response => response.json())
            .then(data => {
                console.log("Données reçues :", data);
                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Créer le graphique avec les données récupérées
                var ctx = document.getElementById('cryptoChart').getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.dates,
                        datasets: [{
                            label: 'Prix USD',
                            data: data.prices,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Date'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Prix (USD)'
                                }
                            }
                        }
                    }
                });
            })
            .catch(error => console.error("Erreur lors de la récupération des données : ", error));
    });

    document.getElementById('showSignupForm').addEventListener('click', function() {
        // Masquer le formulaire de connexion
        document.getElementById('loginForm').style.display = 'none';

        // Afficher le formulaire d'inscription
        document.getElementById('signupForm').style.display = 'block';
        document.getElementById('signupHeading').style.display = 'block';

    });

    document.getElementById('showSignForm').addEventListener('click', function() {
        // Masquer le formulaire de connexion
        document.getElementById('loginForm').style.display = 'block';

        // Afficher le formulaire d'inscription
        document.getElementById('signupForm').style.display = 'none';
        document.getElementById('signupHeading').style.display = 'none';

    });

});


    </script>
</body>
</html>
