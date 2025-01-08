<?php 
require_once ('src/DatabaseConnection.php');
require_once ('login.php');
require_once ('register.php');
require_once ('logout.php');

use App\DatabaseConnection;
use App\CryptoManager;


// Créer une instance de la classe
$db = new DatabaseConnection();
$conn = $db->connect();

// Récupérer les dernières données pour chaque cryptomonnaie
$query = "
SELECT DISTINCT ON (symbol) name, symbol, price_usd, last_updated
FROM cryptocurrencies
ORDER BY symbol, last_updated DESC;
";

$stmt = $conn->prepare($query);
$stmt->execute();
$cryptos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion des filtres de date
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

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

/* Header */
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    background-color: #333;
    color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    width: 100%;
}

/* Logo et nom du site */
.logo {
    display: flex;
    align-items: center;
}

.logo img {
    height: 40px;
    margin-right: 10px;
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
#logoutForm {
    margin: 0; /* Supprime toute marge autour du formulaire */
    padding: 0; /* Supprime tout remplissage */
    display: flex; /* Alignement plus naturel */
    align-items: center; /* Centrage vertical du bouton */
    justify-content: flex-end; /* Alignement à droite */
    background: none; /* Supprime tout arrière-plan du formulaire */
    border: none; /* Supprime les bordures éventuelles */
}

#logoutForm button {
    padding: 10px 20px;
    font-size: 16px;
    font-weight: bold;
    background: linear-gradient(90deg, #ff4b2b, #ff416c);
    color: white;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    transition: background 0.3s ease, transform 0.2s ease;
}

#logoutForm button:hover {
    background: linear-gradient(90deg, #ff416c, #ff4b2b);
    transform: scale(1.05);
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
.menu-bar {
    display: flex;
    justify-content: center;
    background-color: #444;
    padding: 10px;
}

.menu-bar a {
    color: white;
    text-decoration: none;
    margin: 0 15px;
    font-size: 18px;
    transition: color 0.3s ease;
}

.menu-bar a:hover {
    color: #6a1b9a;
}
      
    </style>
</head>
<body>

<div class="menu-bar">
    <a href="index.php">Accueil</a>
    <a href="javascript:void(0);" id="alertes">Alertes</a>
</div>
<header>
        <div class="logo">
        <a href="index.php">
  <img src="images/crypto.png" alt="Logo du site">
</a>
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
    
    <form id="loginForm" method="post">
    <h1>Connexion</h1>
        <label for="loginUsername">Nom d'utilisateur :</label>
        <input type="text" id="loginUsername" name="username" required>

        <label for="loginPassword">Mot de passe :</label>
        <input type="password" id="loginPassword" name="password" required>

        <button type="submit" name="connecter">Se connecter</button>

        <p><a href="javascript:void(0);" id="showSignupForm">Pas encore inscrit ? Inscrivez-vous ici</a></p>
    </form>

    <!-- Formulaire d'inscription (initialement masqué) -->
    
    <form id="signupForm" method="post" action="register.php" style="display: none;">
    <h2 id="signupHeading" style="display: none;">Inscription</h2>
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

    

   
    <!-- Formulaire de sélection des dates -->
    <div id="dateFormContainer" style="display: none;">
    
    <form id="dateForm" method="get" action="">
    <label for="start_date">Date de début :</label>
    <input type="date" id="start_date" name="start_date" required>

    <label for="end_date">Date de fin :</label>
    <input type="date" id="end_date" name="end_date" required>

    <label for="chart_type">Type de graphique :</label>
    <select id="chart_type" name="chart_type" required>
        <option value="line">Courbe de prix</option>
        <option value="heatmap">Carte thermique</option>
    </select>

    <button type="submit" name="filtrer">Filtrer</button>
</form>
    </div>

    <?php
    if(isset($_SESSION["user"]))
    {
    ?>
    <!-- Tableau des cryptomonnaies -->
    <table id="cryptoTable">
        <caption>Dernières données des Cryptomonnaies</caption>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Symbole</th>
                <th>Prix (USD)</th>
                <th>Dernière mise à jour</th>
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
                echo "<td>" . htmlspecialchars($crypto['last_updated']) . "</td>";
                echo "<td><a href='#' class='showGraph' data-crypto='" . htmlspecialchars($crypto['symbol']) . "'>Plus d'informations</a></td>";
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
<form id="alertForm" method="post" action="saveAlert.php" style="display: none;">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/heatmap.js/2.0.5/heatmap.min.js"></script>

    <script>
       
       document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        alert("L'alerte a été enregistrée avec succès !");
    }

        document.getElementById('alertes').addEventListener('click', function () {
        

        // Afficher uniquement le formulaire des alertes
        document.getElementById('alertForm').style.display = 'block';
        document.getElementById('cryptoTable').style.display = 'none';
            document.getElementById('graphContainer').style.display = 'none';
            document.getElementById('dateFormContainer').style.display = 'none';
    });

       
    
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
    let chartInstance = null;
    // Gestion de la soumission du formulaire de dates
    document.getElementById('dateForm').addEventListener('submit', function (event) {
        event.preventDefault();

        var startDate = document.getElementById('start_date').value;
    var endDate = document.getElementById('end_date').value;
    var chartType = document.getElementById('chart_type').value;

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
    if (data.error) {
        alert(data.error);
        return;
    }

    // Vérifiez si un graphique existe et détruisez-le
    if (chartInstance) {
        chartInstance.destroy(); 
    }

    var ctx = document.getElementById('cryptoChart').getContext('2d');

    if (chartType === 'line') {
        // Create a new line chart
        chartInstance = new Chart(ctx, {
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
                    x: { title: { display: true, text: 'Date' } },
                    y: { title: { display: true, text: 'Prix (USD)' } }
                }
            }
        });
    } else  if (chartType === 'heatmap') {
        const heatmapContainer = document.createElement('div');
        heatmapContainer.style.width = '100%';
        heatmapContainer.style.height = '400px';
        heatmapContainer.style.position = 'relative';
        document.getElementById('graphContainer').appendChild(heatmapContainer);

        // Crée l'instance heatmap
        const heatmap = h337.create({
            container: heatmapContainer,
            maxOpacity: 0.6,
            radius: 50,
            blur: 0.9,
        });

        // Données de la heatmap
        heatmap.setData({
            max: Math.max(...data.heatmap.map(d => d.value)),
            data: data.heatmap,
        });
    }
})
        .catch(error => console.error('Erreur lors de la récupération des données : ', error));
});

function renderHeatmap(heatmapData) {
    const heatmap = h337.create({
        container: document.querySelector('#graphContainer'),
        radius: 50,
        maxOpacity: 0.6
    });

    heatmap.setData({
        max: Math.max(...heatmapData.map(d => d.value)),
        data: heatmapData
    });
}

    document.getElementById('showSignupForm').addEventListener('click', function() {
        // Masquer le formulaire de connexion
        document.getElementById('loginForm').style.display = 'none';

        // Afficher le formulaire d'inscription
        document.getElementById('signupForm').style.display = 'block';
        document.getElementById('signupHeading').style.display = 'block';

    });

    document.getElementById('showSignForm').addEventListener('click', function() {
        console.log("Alertes cliqué");
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
