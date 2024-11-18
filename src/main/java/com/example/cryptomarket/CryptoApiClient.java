package com.example.cryptomarket;

import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.StandardOpenOption;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.Statement;

import org.json.JSONArray;
import org.json.JSONObject;

public class CryptoApiClient {

    private static final String API_URL = "https://api.coincap.io/v2/assets";  // URL de l'API
    private static final String FILE_PATH = "cryptocurrencies.json";  // Le fichier où les données seront stockées
    private static final String DB_URL = "jdbc:sqlite:mydb.db";  // URL de la base de données SQLite

    public static void main(String[] args) {
        HttpClient httpClient = HttpClient.newHttpClient();

        // Créez la requête GET vers l'API
        HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(API_URL))
                .GET()
                .header("Accept", "application/json")  // On demande une réponse JSON
                .build();

        try {
            // Envoyer la requête et récupérer la réponse
            HttpResponse<String> response = httpClient.send(request, HttpResponse.BodyHandlers.ofString());

            // Vérifier le statut de la réponse
            if (response.statusCode() == 200) {
                // Si la réponse est OK (status 200), récupérer la réponse JSON
                String jsonResponse = response.body();
                System.out.println("Réponse JSON récupérée avec succès.");


                // Insérer les données dans la base de données SQLite
                insertDataIntoDatabase(jsonResponse);

            } else {
                System.err.println("Erreur : Code HTTP " + response.statusCode());
            }
        } catch (Exception e) {
            System.err.println("Erreur lors de la récupération des données : " + e.getMessage());
        }
    }

    // Méthode pour écrire dans le fichier JSON
    private static void writeToFile(String jsonResponse) {
        try {
            Path filePath = Path.of(FILE_PATH);
            Files.write(filePath, jsonResponse.getBytes(), StandardOpenOption.CREATE, StandardOpenOption.TRUNCATE_EXISTING);
            System.out.println("Données sauvegardées dans " + FILE_PATH);
        } catch (Exception e) {
            System.err.println("Erreur lors de l'écriture dans le fichier : " + e.getMessage());
        }
    }

    // Méthode pour insérer les données dans la base de données SQLite
    public static void insertDataIntoDatabase(String jsonResponse) {
        try (Connection connection = DriverManager.getConnection(DB_URL)) {
            connection.setAutoCommit(false); // Désactive l'auto-commit pour contrôler explicitement les transactions

            String deleteQuery = "DELETE FROM cryptocurrencies";
            try (Statement stmt = connection.createStatement()) {
                stmt.executeUpdate(deleteQuery);
            }

            // Créer la table si elle n'existe pas
            String createTableQuery = "CREATE TABLE IF NOT EXISTS cryptocurrencies (" +
                    "id INTEGER PRIMARY KEY AUTOINCREMENT, " +
                    "name TEXT NOT NULL, " +
                    "symbol TEXT NOT NULL, " +
                    "price_usd REAL, " +
                    "market_cap REAL, " +
                    "volume_24h REAL)";
            try (PreparedStatement createTableStmt = connection.prepareStatement(createTableQuery)) {
                createTableStmt.executeUpdate();
            }

            // Analyse du JSON
            JSONObject jsonObject = new JSONObject(jsonResponse);
            JSONArray data = jsonObject.getJSONArray("data");

            // Préparation de la requête SQL pour insérer les données
            String query = "INSERT INTO cryptocurrencies (name, symbol, price_usd, market_cap, volume_24h) VALUES (?, ?, ?, ?, ?)";

            try (PreparedStatement stmt = connection.prepareStatement(query)) {
                // Insérer chaque cryptomonnaie
                for (int i = 0; i < data.length(); i++) {
                    JSONObject crypto = data.getJSONObject(i);
                    stmt.setString(1, crypto.getString("name"));
                    stmt.setString(2, crypto.getString("symbol"));
                    stmt.setDouble(3, crypto.getDouble("priceUsd"));
                    stmt.setDouble(4, crypto.getDouble("marketCapUsd"));
                    stmt.setDouble(5, crypto.getDouble("volumeUsd24Hr"));
                    stmt.addBatch();
                }

                // Exécuter les insertions par lot
                stmt.executeBatch();
                connection.commit(); // Valide la transaction
                System.out.println("Données insérées dans la base de données SQLite.");
            }

            // Vérification du nombre de lignes
            String checkQuery = "SELECT count(*) FROM cryptocurrencies";
            try (PreparedStatement stmt = connection.prepareStatement(checkQuery)) {
                var resultSet = stmt.executeQuery();
                System.out.println("le nombre de ligne est " + resultSet.getInt(1));
            }

        } catch (Exception e) {
            System.err.println("Erreur lors de l'insertion dans la base de données : " + e.getMessage());
        }
    }

}
