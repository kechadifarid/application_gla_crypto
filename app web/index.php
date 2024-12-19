<?php 
include('Connection.php'); 

// Créer une instance de la classe
$db = new DatabaseConnection();

// Établir la connexion
$conn = $db->connect();


$stmt = $conn->prepare("SELECT * FROM cryptocurrencies LIMIT 8");
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
        /* Tableau style */
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

        /* Style pour le graphique */
        .chart-container {
            display: none; /* Masquer par défaut */
            width: 80%;
            margin: 30px auto;
        }
    </style>
</head>
<body>
    <!-- Barre de navigation avec logo -->
    <header>
        <div class="logo">
            <img src="images/crypto.png" alt="Logo du site">
            <div class="site-name">CryptoTracker</div>
        </div>
    </header>

    <h1>Tableau des Cryptomonnaies</h1>

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

    <!-- Div pour afficher le graphique -->
    <div id="graphContainer" class="chart-container">
        <canvas id="cryptoChart"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Fonction pour afficher le graphique et cacher le tableau
        document.querySelectorAll('.showGraph').forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();

                // Masquer le tableau
                document.getElementById('cryptoTable').style.display = 'none';

                // Afficher le graphique
                document.getElementById('graphContainer').style.display = 'block';

                // Récupérer le nom de la cryptomonnaie
                const cryptoName = this.getAttribute('data-crypto');

                // Charger les données de prix en fonction de la cryptomonnaie
                fetchGraphData(cryptoName);
            });
        });

        // Fonction pour charger les données du graphique
        function fetchGraphData(cryptoName) {
            // Effectuer la requête AJAX pour récupérer les données de la cryptomonnaie
            fetch('GraphPrice.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'cryptoName=' + encodeURIComponent(cryptoName)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Si la requête a réussi, créez le graphique
                    var dates = data.dates;
                    var prices = data.prices;

                    // Créer le graphique avec Chart.js
                    var ctx = document.getElementById('cryptoChart').getContext('2d');
                    var cryptoChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: dates,  // Dates
                            datasets: [{
                                label: 'Prix de ' + cryptoName + ' (USD)',  // Dynamiser l'étiquette
                                data: prices,  // Prix
                                borderColor: '#FF5733',
                                backgroundColor: 'rgba(255, 87, 51, 0.2)',
                                fill: true,
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Date'
                                    },
                                    ticks: {
                                        autoSkip: true,
                                        maxTicksLimit: 10
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Prix (USD)'
                                    },
                                    ticks: {
                                        beginAtZero: false
                                    }
                                }
                            }
                        }
                    });
                } else {
                    alert("Erreur: " + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        }
    </script>
</body>
</html>
