<?php 
require_once ('src/databaseConnection.php');
require_once ('src/setData.php');
require_once ('src/setDataAllCrypto.php');

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
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            background-color: #1a1a1a;
            color: white;
        }
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
        h1 {
            text-align: center;
            margin-top: 20px;
        }
        form {
            width: 80%;
            margin: 20px auto;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        form label {
            font-weight: bold;
        }
        form input[type="date"] {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        form button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        form button:hover {
            background-color: #45a049;
        }
        table {
            border-collapse: collapse;
            width: 80%;
            margin: 20px auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: white;
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
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        caption {
            font-size: 1.5em;
            margin: 10px;
            color: #333;
        }
        td {
            transition: background-color 0.3s;
        }
        td:hover {
            background-color: #ffeb3b;
        }
        .chart-container {
            display: none; /* Masquer par défaut */
            width: 80%;
            margin: 30px auto;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="images/crypto.png" alt="Logo du site">
            <div class="site-name">CryptoTracker</div>
        </div>
    </header>

    <h1>Tableau des Cryptomonnaies</h1>

    <!-- Formulaire de sélection des dates -->
    <div id="dateFormContainer" style="display: none;">
    <form id="dateForm"  method="get" action="">
        <label for="start_date">Date de début :</label>
        <input type="date" id="start_date" name="start_date"?>

        <label for="end_date">Date de fin :</label>
        <input type="date" id="end_date" name="end_date" ?>

        <button type="submit" name="filtrer">Filtrer</button>
    </form>
</div>



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

    <div id="graphContainer" class="chart-container">
        <canvas id="cryptoChart"></canvas>
        <?php
echo "ccc";

?>
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
});


    </script>
</body>
</html>
