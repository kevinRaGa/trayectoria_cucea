<?php

require_once __DIR__ . '/../config/infrastructure/connection.php';

use Config\Infrastructure\Connection\Connection;

echo "=== TEST DE CONEXIÓN A LA BASE DE DATOS ===\n\n";

try {
  echo "Intentando conectar a la base de datos...\n";

  $db = new Connection();
  $connection = $db->getConnection();

  echo "✓ Conexión exitosa!\n";
  echo "✓ Estado del servidor: " . $connection->stat() . "\n";
  echo "✓ Versión del servidor: " . $connection->server_info . "\n";
  echo "✓ Charset actual: " . $connection->character_set_name() . "\n";

  echo "\n=== TEST COMPLETADO EXITOSAMENTE ===\n";
} catch (Exception $e) {
  echo "✗ Error en la conexión: " . $e->getMessage() . "\n";
  echo "\n=== TEST FALLIDO ===\n";
}
