<?php
header('Content-Type: text/plain');

// Exemple de métriques
echo "# HELP app_requests_total Nombre total de requêtes\n";
echo "# TYPE app_requests_total counter\n";
echo "app_requests_total " . rand(100, 1000) . "\n";

echo "# HELP app_memory_usage Mémoire utilisée en octets\n";
echo "# TYPE app_memory_usage gauge\n";
echo "app_memory_usage " . memory_get_usage() . "\n";
?>
