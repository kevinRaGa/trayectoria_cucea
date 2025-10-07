<?php

require_once __DIR__ . '/../config/infrastructure/database.php';

use Config\Infrastructure\Database\Database;

echo "=== TEST DE LA CLASE DATABASE ===\n\n";

try {
   $db = new Database();
   echo "✓ Conexión establecida\n\n";

   // ============================================
   // FUNCIONES BÁSICAS
   // ============================================

   echo "--- 1. getOne() - Obtener valor escalar ---\n";
   $dbName = $db->getOne("SELECT DATABASE()");
   echo "Base de datos: $dbName\n\n";

   echo "--- 2. count() - Contar registros ---\n";
   $totalTables = $db->count("information_schema.TABLES", "TABLE_SCHEMA = ?s", $dbName);
   echo "Total de tablas: $totalTables\n\n";

   echo "--- 3. exists() - Verificar existencia ---\n";
   $exists = $db->exists("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?s LIMIT 1", $dbName);
   echo "¿Existen tablas?: " . ($exists ? "Sí" : "No") . "\n\n";

   // ============================================
   // PLACEHOLDERS
   // ============================================

   echo "--- 4. Placeholder ?n para nombres ---\n";
   $parsed = $db->parse("SELECT * FROM ?n WHERE id = ?i", "users", 1);
   echo "Query: $parsed\n\n";

   echo "--- 5. Placeholder ?a para IN() ---\n";
   $ids = [1, 2, 3, 4, 5];
   $parsed = $db->parse("SELECT * FROM users WHERE id IN (?a)", $ids);
   echo "Query: $parsed\n\n";

   echo "--- 6. Placeholder ?u para SET ---\n";
   $data = ['nombre' => 'Juan', 'email' => 'juan@example.com', 'edad' => 25];
   $parsed = $db->parse("UPDATE users SET ?u WHERE id = ?i", $data, 1);
   echo "Query: $parsed\n\n";

   // ============================================
   // FUNCIONES DE SEGURIDAD
   // ============================================

   echo "--- 7. filterArray() - Filtrar campos ---\n";
   $post = ['nombre' => 'Test', 'email' => 'test@test.com', 'password' => '123', 'role' => 'admin'];
   $allowed = ['nombre', 'email'];
   $filtered = $db->filterArray($post, $allowed);
   echo "Original: " . json_encode($post) . "\n";
   echo "Filtrado: " . json_encode($filtered) . "\n\n";

   echo "--- 8. whiteList() - Validar valores ---\n";
   $order = $db->whiteList('price', ['name', 'price', 'date'], 'name');
   echo "Valor válido: $order\n";
   $order = $db->whiteList('malicious', ['name', 'price', 'date'], 'name');
   echo "Valor inválido (default): $order\n\n";

   // ============================================
   // FUNCIONES DE CONSULTA
   // ============================================

   echo "--- 9. getCol() - Obtener columna ---\n";
   $tables = $db->getCol("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?s LIMIT 5", $dbName);
   echo "Tablas (máximo 5):\n";
   foreach ($tables as $t) {
      echo "  • $t\n";
   }
   echo "\n";

   echo "--- 10. getAssoc() - Array clave=>valor ---\n";
   $result = $db->getAssoc("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?s LIMIT 3", $dbName);
   echo "Tablas y sus engines:\n";
   foreach ($result as $table => $engine) {
      echo "  • $table => $engine\n";
   }
   echo "\n";

   // ============================================
   // OPERACIONES CRUD SIMPLIFICADAS
   // ============================================

   echo "--- 11. Ejemplo de insert() ---\n";
   $insertData = ['nombre' => 'Test', 'email' => 'test@example.com'];
   $parsed = $db->parse("INSERT INTO ?n SET ?u", "users", $insertData);
   echo "Query INSERT: $parsed\n\n";

   echo "--- 12. Ejemplo de update() ---\n";
   $updateData = ['status' => 'inactive'];
   $parsed = $db->parse("UPDATE ?n SET ?u WHERE id = ?i", "users", $updateData, 5);
   echo "Query UPDATE: $parsed\n\n";

   echo "--- 13. Ejemplo de delete() ---\n";
   $parsed = $db->parse("DELETE FROM ?n WHERE id = ?i", "users", 5);
   echo "Query DELETE: $parsed\n\n";

   echo "--- 14. Ejemplo de upsert() ---\n";
   $upsertData = ['id' => 1, 'nombre' => 'Juan', 'email' => 'juan@example.com'];
   $parsed = $db->parse("INSERT INTO ?n SET ?u ON DUPLICATE KEY UPDATE ?u", "users", $upsertData, $upsertData);
   echo "Query UPSERT: $parsed\n\n";

   echo "--- 15. Ejemplo de insertBatch() ---\n";
   $batchData = [
      ['nombre' => 'Juan', 'email' => 'juan@example.com'],
      ['nombre' => 'Maria', 'email' => 'maria@example.com']
   ];
   echo "Insertaría " . count($batchData) . " registros en una sola query\n\n";

   // ============================================
   // BÚSQUEDA SIMPLIFICADA
   // ============================================

   echo "--- 16. Ejemplo de findById() ---\n";
   $parsed = $db->parse("SELECT * FROM ?n WHERE ?n = ?i", "users", "id", 5);
   echo "Query: $parsed\n\n";

   echo "--- 17. Ejemplo de findBy() ---\n";
   $parsed = $db->parse("SELECT * FROM ?n WHERE ?n = ?s", "users", "email", "test@example.com");
   echo "Query: $parsed\n\n";

   // ============================================
   // TRANSACCIONES
   // ============================================

   echo "--- 18. Ejemplo de transacción ---\n";
   echo "Código de ejemplo:\n";
   echo "  \$db->transactional(function(\$db) {\n";
   echo "     \$db->insert('users', ['nombre' => 'Juan']);\n";
   echo "     \$db->insert('logs', ['action' => 'user_created']);\n";
   echo "  });\n\n";

   // ============================================
   // ESTADÍSTICAS
   // ============================================

   echo "--- 19. Estadísticas ---\n";
   $stats = $db->getStats();
   echo "Total de queries: " . count($stats) . "\n";
   echo "Última query: " . $db->lastQuery() . "\n";
   
   if (!empty($stats)) {
      $totalTime = array_sum(array_column($stats, 'timer'));
      echo "Tiempo total: " . number_format($totalTime * 1000, 2) . " ms\n";
   }
   echo "\n";

   echo "=== ✓ TODOS LOS TESTS COMPLETADOS ===\n";

} catch (Exception $e) {
   echo "✗ Error: " . $e->getMessage() . "\n";
   echo "Trace: " . $e->getTraceAsString() . "\n";
}
