package com.example.cryptomarket;

import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import org.json.JSONArray;
import org.json.JSONObject;

public class CryptoApiClient {

    // URL de l'API pour récupérer les données des cryptomonnaies
    private static final String API_URL = "https://api.coincap.io/v2/assets";
    // URL de connexion à la base de données PostgreSQL
    private static final String DB_URL = "jdbc:postgresql://pedago01c.univ-avignon.fr:5432/etd";
    private static final String DB_USERNAME = "uapv2200995";  // Utilisateur PostgreSQL
    private static final String DB_PASSWORD = "xm4Quj";      // Mot de passe PostgreSQL

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

                // Insérer les données dans la base de données PostgreSQL
                insertDataIntoDatabase(jsonResponse);

            } else {
                System.err.println("Erreur : Code HTTP " + response.statusCode());
            }
        } catch (Exception e) {
            System.err.println("Erreur lors de la récupération des données : " + e.getMessage());
        }
    }

    // Méthode pour insérer les données dans la base de données PostgreSQL
    public static void insertDataIntoDatabase(String jsonResponse) {
        try (Connection connection = DriverManager.getConnection(DB_URL, DB_USERNAME, DB_PASSWORD)) {
            connection.setAutoCommit(false); // Désactive l'auto-commit pour contrôler explicitement les transactions

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
                System.out.println("Données insérées dans la base de données PostgreSQL.");
            }

        } catch (Exception e) {
            System.err.println("Erreur lors de l'insertion dans la base de données : " + e.getMessage());
        }
    }


}