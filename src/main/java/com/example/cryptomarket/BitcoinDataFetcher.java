package com.example.cryptomarket;

import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;
import java.time.Instant;
import java.time.temporal.ChronoUnit;

public class BitcoinDataFetcher {

    // URL de l'API pour récupérer les données historiques de Bitcoin
    private static final String API_URL = "https://api.coincap.io/v2/assets/bitcoin/history";

    public static void main(String[] args) {
        // Récupérer les données historiques de Bitcoin
        getBitcoinHistoricalData();
    }

    // Méthode pour récupérer les données historiques de Bitcoin depuis l'API CoinCap
    public static void getBitcoinHistoricalData() {
        HttpClient httpClient = HttpClient.newHttpClient();

        // Calculer les timestamps UNIX pour le dernier mois
        long endTimestamp = Instant.now().toEpochMilli();
        long startTimestamp = Instant.now().minus(30, ChronoUnit.DAYS).toEpochMilli();

        // Créez l'URL pour obtenir les données historiques de Bitcoin
        String url = String.format("%s?interval=d1&start=%d&end=%d", API_URL, startTimestamp, endTimestamp);

        // Créez la requête GET
        HttpRequest request = HttpRequest.newBuilder()
                .uri(URI.create(url))
                .GET()
                .header("Accept", "application/json")
                .build();

        try {
            // Envoyer la requête et récupérer la réponse
            HttpResponse<String> response = httpClient.send(request, HttpResponse.BodyHandlers.ofString());

            // Vérifier le statut de la réponse
            if (response.statusCode() == 200) {
                String jsonResponse = response.body();
                System.out.println("Réponse JSON récupérée avec succès pour Bitcoin.");

                // Appeler la méthode pour traiter les données
                processHistoricalData(jsonResponse);
            } else {
                System.err.println("Erreur lors de la récupération des données pour Bitcoin : Code HTTP " + response.statusCode());
            }
        } catch (Exception e) {
            System.err.println("Erreur lors de la récupération des données pour Bitcoin : " + e.getMessage());
        }
    }

    // Méthode pour traiter les données historiques de Bitcoin
    public static void processHistoricalData(String jsonResponse) {
        try {
            // Analyse du JSON
            org.json.JSONObject jsonObject = new org.json.JSONObject(jsonResponse);
            org.json.JSONArray data = jsonObject.getJSONArray("data");

            System.out.println("Données historiques pour Bitcoin (Prix USD par jour) :");
            for (int i = 0; i < data.length(); i++) {
                org.json.JSONObject record = data.getJSONObject(i);
                String timestamp = record.getString("time");
                double priceUsd = record.getDouble("priceUsd");

                // Afficher les données ou les enregistrer
                System.out.println("Date: " + timestamp + ", Prix USD: " + priceUsd);
            }

        } catch (Exception e) {
            System.err.println("Erreur lors du traitement des données historiques pour Bitcoin : " + e.getMessage());
        }
    }
}
