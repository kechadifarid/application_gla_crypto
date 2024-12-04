package com.example.cryptomarket;

import org.junit.jupiter.api.Test;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;

import static org.mockito.Mockito.*;

public class CryptoApiClientTest {

    @Test
    public void testInsertDataIntoDatabase() throws SQLException {
        // Créer un mock de la connexion à la base de données
        Connection mockConnection = mock(Connection.class);

        // Créer un mock du PreparedStatement
        PreparedStatement mockStatement = mock(PreparedStatement.class);

        // Créer l'objet CryptoApiClient
        CryptoApiClient client = new CryptoApiClient() {
            // On surcharge la méthode createConnection pour retourner notre mock
            @Override
            public Connection createConnection() throws SQLException {
                return mockConnection;
            }
        };

        // Définir le comportement du mock
        when(mockConnection.prepareStatement(anyString())).thenReturn(mockStatement);

        // Simuler la réponse JSON de l'API
        String jsonResponse = "{ \"data\": [ { \"name\": \"Bitcoin\", \"symbol\": \"BTC\", \"priceUsd\": 60000, \"marketCapUsd\": 1000000000, \"volumeUsd24Hr\": 30000000 } ] }";

        // Appeler la méthode pour insérer les données dans la base de données
        client.insertDataIntoDatabase(jsonResponse);

        // Vérifier que les méthodes de batch ont bien été appelées
        verify(mockStatement, times(1)).addBatch();
        verify(mockStatement, times(1)).executeBatch();
        verify(mockConnection, times(1)).commit();
    }
}
