<?php

namespace Config\Infrastructure\Database;

require_once __DIR__ . '/connection.php';

use Config\Infrastructure\Connection\Connection;
use Exception;

/**
 * Database wrapper con placeholders tipados para queries seguras
 * 
 * Esta clase proporciona una interfaz segura para interactuar con MySQL,
 * eliminando completamente el riesgo de SQL Injection mediante el uso de
 * placeholders tipados que validan y escapan automáticamente los valores.
 * 
 * PLACEHOLDERS SOPORTADOS:
 * -------------------------
 * ?s (string)  - Strings, DATE, FLOAT, DECIMAL
 *                Ejemplo: "WHERE nombre = ?s", "Juan"
 *                Resultado: WHERE nombre = 'Juan'
 * 
 * ?i (integer) - Números enteros
 *                Ejemplo: "WHERE id = ?i", 123
 *                Resultado: WHERE id = 123
 * 
 * ?n (name)    - Identificadores (nombres de tablas y columnas)
 *                Ejemplo: "SELECT * FROM ?n", "users"
 *                Resultado: SELECT * FROM `users`
 * 
 * ?a (array)   - Arrays para operador IN()
 *                Ejemplo: "WHERE id IN (?a)", [1,2,3]
 *                Resultado: WHERE id IN ('1','2','3')
 * 
 * ?u (update)  - Arrays asociativos para SET
 *                Ejemplo: "SET ?u", ['nombre' => 'Juan', 'edad' => 25]
 *                Resultado: SET `nombre`='Juan',`edad`='25'
 * 
 * ?p (parsed)  - Statements ya parseados (usar con precaución)
 *                Ejemplo: "WHERE ?p", "status = 'active'"
 *                Resultado: WHERE status = 'active'
 * 
 * EJEMPLOS DE USO:
 * ----------------
 * // Consulta simple
 * $user = $db->getRow("SELECT * FROM users WHERE id = ?i", 5);
 * 
 * // Insertar
 * $id = $db->insert("users", ['nombre' => 'Juan', 'email' => 'juan@example.com']);
 * 
 * // Actualizar
 * $db->update("users", ['status' => 'inactive'], "id = ?i", 5);
 * 
 * // Buscar con IN
 * $users = $db->getAll("SELECT * FROM users WHERE id IN (?a)", [1,2,3,4,5]);
 * 
 * // Transacción
 * $db->transactional(function($db) {
 *     $db->insert("orders", ['user_id' => 1, 'total' => 100]);
 *     $db->update("users", ['balance' => 'balance - 100'], "id = ?i", 1);
 * });
 */
class Database
{
   protected $conn;
   protected $mysqli;
   protected $stats = [];

   const RESULT_ASSOC = MYSQLI_ASSOC;
   const RESULT_NUM   = MYSQLI_NUM;

   public function __construct()
   {
      $connectionObj = new Connection();
      $this->mysqli = $connectionObj->getConnection();
      $this->conn = $this->mysqli;
   }

   /**
    * Ejecuta una query con placeholders y retorna el resultado
    * 
    * Esta es la función principal para ejecutar cualquier query SQL.
    * Usa placeholders para mayor seguridad y prevención de SQL Injection.
    * 
    * @param string $query Query SQL con placeholders
    * @param mixed ...$args Valores para reemplazar los placeholders
    * @return \mysqli_result|bool Resultado de la query o false en caso de error
    * 
    * @example
    * // Insertar
    * $db->query("INSERT INTO users SET nombre = ?s, edad = ?i", "Juan", 25);
    * 
    * // Actualizar
    * $db->query("UPDATE users SET status = ?s WHERE id = ?i", "inactive", 5);
    * 
    * // Eliminar
    * $db->query("DELETE FROM users WHERE id = ?i", 5);
    * 
    * // Múltiples placeholders
    * $db->query("UPDATE ?n SET ?u WHERE id IN (?a)", "users", ['status' => 'active'], [1,2,3]);
    */
   public function query()
   {
      return $this->rawQuery($this->prepareQuery(func_get_args()));
   }

   /**
    * Obtiene una fila del resultado (uso interno principalmente)
    * 
    * @param \mysqli_result $result Resultado de mysqli_query
    * @param int $mode Modo de fetch (RESULT_ASSOC o RESULT_NUM)
    * @return array|null|false Array con la fila o false/null si no hay más
    * 
    * @internal Usado internamente por las funciones get*
    */
   public function fetch($result, $mode = self::RESULT_ASSOC)
   {
      return mysqli_fetch_array($result, $mode);
   }

   /**
    * Obtiene el número de filas afectadas por la última query
    * 
    * Útil después de INSERT, UPDATE o DELETE para saber cuántos
    * registros fueron modificados.
    * 
    * @return int Número de filas afectadas
    * 
    * @example
    * $db->query("UPDATE users SET status = ?s WHERE active = ?i", "inactive", 0);
    * $affected = $db->affectedRows();
    * echo "Se desactivaron {$affected} usuarios";
    */
   public function affectedRows()
   {
      return mysqli_affected_rows($this->conn);
   }

   /**
    * Obtiene el ID auto-increment generado por el último INSERT
    * 
    * Solo funciona si la tabla tiene una columna AUTO_INCREMENT.
    * 
    * @return int ID del último registro insertado
    * 
    * @example
    * $db->query("INSERT INTO users SET nombre = ?s, email = ?s", "Juan", "juan@example.com");
    * $userId = $db->insertId();
    * echo "Usuario creado con ID: {$userId}";
    */
   public function insertId()
   {
      return mysqli_insert_id($this->conn);
   }

   /**
    * Obtiene el número de filas en un resultado de SELECT
    * 
    * @param \mysqli_result $result Resultado de una query SELECT
    * @return int Número de filas en el resultado
    * 
    * @example
    * $res = $db->query("SELECT * FROM users");
    * $total = $db->numRows($res);
    * echo "Total de usuarios: {$total}";
    */
   public function numRows($result)
   {
      return mysqli_num_rows($result);
   }

   /**
    * Libera la memoria asociada a un resultado de query
    * 
    * Importante llamar esto cuando trabajas con grandes resultados
    * para liberar memoria.
    * 
    * @param \mysqli_result $result Resultado a liberar
    * @return void
    * 
    * @internal Las funciones get* llaman esto automáticamente
    */
   public function free($result)
   {
      mysqli_free_result($result);
   }

   /**
    * Obtiene un solo valor escalar (primera columna de la primera fila)
    * 
    * Ideal para queries que retornan un único valor como COUNT, MAX, MIN, etc.
    * 
    * @param string $query Query SQL con placeholders
    * @param mixed ...$args Valores para los placeholders
    * @return mixed|false El valor encontrado o false si no hay resultados
    * 
    * @example
    * // Obtener un nombre
    * $nombre = $db->getOne("SELECT nombre FROM users WHERE id = ?i", 5);
    * 
    * // Contar registros
    * $total = $db->getOne("SELECT COUNT(*) FROM users WHERE status = ?s", "active");
    * 
    * // Obtener un máximo
    * $maxPrice = $db->getOne("SELECT MAX(precio) FROM productos");
    * 
    * // Verificar existencia
    * $exists = $db->getOne("SELECT 1 FROM users WHERE email = ?s LIMIT 1", $email);
    */
   public function getOne()
   {
      $query = $this->prepareQuery(func_get_args());
      if ($res = $this->rawQuery($query)) {
         $row = $this->fetch($res);
         if (is_array($row)) {
            return reset($row);
         }
         $this->free($res);
      }
      return FALSE;
   }

   /**
    * Obtiene una sola fila como array asociativo
    * 
    * Retorna la primera fila del resultado. Ideal cuando esperas
    * un único registro.
    * 
    * @param string $query Query SQL con placeholders
    * @param mixed ...$args Valores para los placeholders
    * @return array|false Array asociativo con la fila o false si no existe
    * 
    * @example
    * // Obtener un usuario completo
    * $user = $db->getRow("SELECT * FROM users WHERE id = ?i", 5);
    * echo $user['nombre']; // 'Juan'
    * echo $user['email'];  // 'juan@example.com'
    * 
    * // Con múltiples condiciones
    * $user = $db->getRow("SELECT * FROM users WHERE email = ?s AND status = ?s", $email, "active");
    * 
    * // Con JOIN
    * $order = $db->getRow(
    *     "SELECT o.*, u.nombre as user_name FROM orders o 
    *      JOIN users u ON o.user_id = u.id WHERE o.id = ?i",
    *     $orderId
    * );
    */
   public function getRow()
   {
      $query = $this->prepareQuery(func_get_args());
      if ($res = $this->rawQuery($query)) {
         $ret = $this->fetch($res);
         $this->free($res);
         return $ret;
      }
      return FALSE;
   }

   /**
    * Obtiene una columna (primera columna de cada fila) como array simple
    * 
    * Ideal cuando solo necesitas un campo de múltiples registros,
    * como una lista de IDs, nombres, emails, etc.
    * 
    * @param string $query Query SQL con placeholders
    * @param mixed ...$args Valores para los placeholders
    * @return array Array con los valores de la primera columna
    * 
    * @example
    * // Obtener lista de IDs
    * $ids = $db->getCol("SELECT id FROM users WHERE status = ?s", "active");
    * // Retorna: [1, 2, 3, 4, 5]
    * 
    * // Obtener lista de emails
    * $emails = $db->getCol("SELECT email FROM users WHERE role = ?s", "admin");
    * // Retorna: ['admin@example.com', 'admin2@example.com']
    * 
    * // Usar con IN() posteriormente
    * $tagIds = $db->getCol("SELECT id FROM tags WHERE category = ?s", "tech");
    * $posts = $db->getAll("SELECT * FROM posts WHERE tag_id IN (?a)", $tagIds);
    */
   public function getCol()
   {
      $ret   = array();
      $query = $this->prepareQuery(func_get_args());
      if ($res = $this->rawQuery($query)) {
         while ($row = $this->fetch($res)) {
            $ret[] = reset($row);
         }
         $this->free($res);
      }
      return $ret;
   }

   /**
    * Obtiene todas las filas como array de arrays asociativos
    * 
    * Retorna todos los registros que coincidan con la query.
    * Cada fila es un array asociativo.
    * 
    * @param string $query Query SQL con placeholders
    * @param mixed ...$args Valores para los placeholders
    * @return array Array de arrays asociativos
    * 
    * @example
    * // Obtener todos los usuarios activos
    * $users = $db->getAll("SELECT * FROM users WHERE status = ?s", "active");
    * // Retorna: [['id' => 1, 'nombre' => 'Juan'], ['id' => 2, 'nombre' => 'Maria']]
    * 
    * // Con paginación
    * $users = $db->getAll("SELECT * FROM users LIMIT ?i, ?i", $start, $perPage);
    * 
    * // Con ORDER BY
    * $products = $db->getAll("SELECT * FROM productos WHERE precio > ?i ORDER BY precio DESC", 100);
    * 
    * // Con JOIN
    * $orders = $db->getAll(
    *     "SELECT o.*, u.nombre as user_name FROM orders o 
    *      JOIN users u ON o.user_id = u.id WHERE o.status = ?s",
    *     "pending"
    * );
    */
   public function getAll()
   {
      $ret   = array();
      $query = $this->prepareQuery(func_get_args());
      if ($res = $this->rawQuery($query)) {
         while ($row = $this->fetch($res)) {
            $ret[] = $row;
         }
         $this->free($res);
      }
      return $ret;
   }

   /**
    * Obtiene filas indexadas por un campo específico
    * 
    * Similar a getAll() pero retorna un array asociativo donde
    * la clave es el valor de un campo específico.
    * 
    * @param string $index Nombre del campo para usar como índice
    * @param string $query Query SQL con placeholders
    * @param mixed ...$args Valores para los placeholders
    * @return array Array indexado por el campo especificado
    * 
    * @example
    * // Indexar por ID
    * $users = $db->getInd("id", "SELECT * FROM users");
    * // Retorna: [1 => ['id' => 1, 'nombre' => 'Juan'], 2 => ['id' => 2, 'nombre' => 'Maria']]
    * echo $users[1]['nombre']; // 'Juan'
    * 
    * // Indexar por email
    * $users = $db->getInd("email", "SELECT * FROM users WHERE status = ?s", "active");
    * // Retorna: ['juan@example.com' => [...], 'maria@example.com' => [...]]
    * echo $users['juan@example.com']['nombre']; // 'Juan'
    * 
    * // Acceso rápido sin bucles
    * if (isset($users['juan@example.com'])) {
    *     echo "Juan existe!";
    * }
    */
   public function getInd()
   {
      $args  = func_get_args();
      $index = array_shift($args);
      $query = $this->prepareQuery($args);

      $ret = array();
      if ($res = $this->rawQuery($query)) {
         while ($row = $this->fetch($res)) {
            $ret[$row[$index]] = $row;
         }
         $this->free($res);
      }
      return $ret;
   }

   /**
    * Obtiene un array asociativo de dos columnas (clave => valor)
    * 
    * La primera columna especificada se usa como clave,
    * la segunda como valor. Ideal para crear lookups o mapeos.
    * 
    * @param string $index Nombre del campo para usar como clave
    * @param string $query Query SQL con placeholders (debe seleccionar 2 columnas)
    * @param mixed ...$args Valores para los placeholders
    * @return array Array asociativo [clave => valor]
    * 
    * @example
    * // Mapeo ID => nombre
    * $userNames = $db->getIndCol("id", "SELECT id, nombre FROM users");
    * // Retorna: [1 => 'Juan', 2 => 'Maria', 3 => 'Pedro']
    * echo $userNames[1]; // 'Juan'
    * 
    * // Mapeo email => nombre
    * $lookup = $db->getIndCol("email", "SELECT email, nombre FROM users");
    * // Retorna: ['juan@example.com' => 'Juan', 'maria@example.com' => 'Maria']
    * echo $lookup['juan@example.com']; // 'Juan'
    * 
    * // Usar para dropdowns
    * $categories = $db->getIndCol("id", "SELECT id, nombre FROM categorias ORDER BY nombre");
    * foreach ($categories as $id => $nombre) {
    *     echo "<option value='{$id}'>{$nombre}</option>";
    * }
    */
   public function getIndCol()
   {
      $args  = func_get_args();
      $index = array_shift($args);
      $query = $this->prepareQuery($args);

      $ret = array();
      if ($res = $this->rawQuery($query)) {
         while ($row = $this->fetch($res)) {
            $key = $row[$index];
            unset($row[$index]);
            $ret[$key] = reset($row);
         }
         $this->free($res);
      }
      return $ret;
   }

   /**
    * Parsea placeholders en una query o fragmento sin ejecutarla
    * 
    * Útil cuando necesitas construir partes de queries dinámicamente
    * o cuando quieres ver la query final sin ejecutarla.
    * 
    * @param string $query Query o fragmento SQL con placeholders
    * @param mixed ...$args Valores para los placeholders
    * @return string Query con placeholders reemplazados
    * 
    * @example
    * // Ver la query final
    * $query = $db->parse("SELECT * FROM users WHERE id = ?i AND status = ?s", 5, "active");
    * echo $query; // "SELECT * FROM users WHERE id = 5 AND status = 'active'"
    * 
    * // Construir WHERE dinámico
    * $where = $db->parse("created_at > ?s AND status = ?s", $date, "active");
    * $users = $db->getAll("SELECT * FROM users WHERE ?p", $where);
    * 
    * // Debugging
    * $parsed = $db->parse("UPDATE ?n SET ?u WHERE id = ?i", "users", ['status' => 'active'], 5);
    * var_dump($parsed); // Ver la query exacta que se ejecutaría
    */
   public function parse()
   {
      return $this->prepareQuery(func_get_args());
   }

   /**
    * Valida que un valor esté en una lista de valores permitidos
    * 
    * Ideal para validar campos como ORDER BY, columnas dinámicas,
    * o cualquier valor que venga del usuario.
    * 
    * @param mixed $input Valor a validar
    * @param array $allowed Array de valores permitidos
    * @param mixed $default Valor por defecto si no está en la lista (false por defecto)
    * @return mixed El valor si es válido, o el valor por defecto
    * 
    * @example
    * // Validar ORDER BY
    * $sortBy = $_GET['sort'] ?? 'name';
    * $validSort = $db->whiteList($sortBy, ['name', 'price', 'date'], 'name');
    * $products = $db->getAll("SELECT * FROM productos ORDER BY ?n", $validSort);
    * 
    * // Validar columna dinámica
    * $field = $_GET['field'] ?? 'id';
    * $safeField = $db->whiteList($field, ['id', 'nombre', 'email'], 'id');
    * $data = $db->getCol("SELECT ?n FROM users", $safeField);
    * 
    * // Con mensaje de error
    * $role = $db->whiteList($userRole, ['admin', 'user', 'guest'], false);
    * if ($role === false) {
    *     die("Rol inválido");
    * }
    */
   public function whiteList($input, $allowed, $default = FALSE)
   {
      $found = array_search($input, $allowed);
      return ($found === FALSE) ? $default : $allowed[$found];
   }

   /**
    * Filtra un array para solo permitir campos específicos (whitelist)
    * 
    * Remueve todas las claves que no estén en la lista de permitidos.
    * Esencial para prevenir mass-assignment en inserts/updates.
    * 
    * @param array $input Array a filtrar (ej. $_POST, $_GET)
    * @param array $allowed Array con nombres de campos permitidos
    * @return array Array filtrado con solo los campos permitidos
    * 
    * @example
    * // Filtrar datos de formulario
    * $allowedFields = ['nombre', 'email', 'telefono'];
    * $userData = $db->filterArray($_POST, $allowedFields);
    * $db->insert("users", $userData);
    * // Si $_POST tiene 'role' => 'admin', será ignorado
    * 
    * // Prevenir modificación de campos protegidos
    * $updateData = $db->filterArray($_POST, ['nombre', 'bio', 'avatar']);
    * $db->update("users", $updateData, "id = ?i", $userId);
    * // Campos como 'password', 'role', 'balance' no pueden ser modificados
    * 
    * // Uso con INSERT
    * $post = ['title' => 'Título', 'body' => 'Contenido', 'user_id' => 1, 'is_admin' => true];
    * $safeData = $db->filterArray($post, ['title', 'body']);
    * $db->insert("posts", $safeData);
    * // 'user_id' e 'is_admin' son removidos automáticamente
    */
   public function filterArray($input, $allowed)
   {
      foreach (array_keys($input) as $key) {
         if (!in_array($key, $allowed)) {
            unset($input[$key]);
         }
      }
      return $input;
   }

   /**
    * Obtiene la última query SQL ejecutada
    * 
    * Útil para debugging y logging.
    * 
    * @return string|null La última query ejecutada o null si no hay
    * 
    * @example
    * $users = $db->getAll("SELECT * FROM users WHERE status = ?s", "active");
    * echo $db->lastQuery();
    * // Muestra: SELECT * FROM users WHERE status = 'active'
    * 
    * // Para debugging
    * try {
    *     $db->query("UPDATE users SET status = ?s WHERE id = ?i", "active", $id);
    * } catch (Exception $e) {
    *     error_log("Error en query: " . $db->lastQuery());
    *     error_log("Mensaje: " . $e->getMessage());
    * }
    */
   public function lastQuery()
   {
      $last = end($this->stats);
      return $last['query'] ?? NULL;
   }

   /**
    * Obtiene estadísticas de todas las queries ejecutadas
    * 
    * Retorna un array con información de cada query:
    * - query: La query SQL ejecutada
    * - start: Timestamp de inicio
    * - timer: Tiempo de ejecución en segundos
    * - error: Mensaje de error (si hubo)
    * 
    * @return array Array de estadísticas de queries
    * 
    * @example
    * $db->getAll("SELECT * FROM users");
    * $db->insert("logs", ['action' => 'view_users']);
    * 
    * $stats = $db->getStats();
    * foreach ($stats as $stat) {
    *     echo "Query: {$stat['query']}\n";
    *     echo "Tiempo: " . ($stat['timer'] * 1000) . " ms\n";
    * }
    * 
    * // Calcular tiempo total
    * $totalTime = array_sum(array_column($stats, 'timer'));
    * echo "Tiempo total: " . ($totalTime * 1000) . " ms";
    */
   public function getStats()
   {
      return $this->stats;
   }

   /**
    * Ejecuta la query contra MySQL y registra stats
    */
   protected function rawQuery($query)
   {
      $start = microtime(TRUE);
      $res   = mysqli_query($this->conn, $query);
      $timer = microtime(TRUE) - $start;

      $this->stats[] = array(
         'query' => $query,
         'start' => $start,
         'timer' => $timer,
      );

      if (!$res) {
         $error = mysqli_error($this->conn);

         end($this->stats);
         $key = key($this->stats);
         $this->stats[$key]['error'] = $error;
         $this->cutStats();

         $this->error("$error. Query completa: [$query]");
      }
      $this->cutStats();
      return $res;
   }

   /**
    * Prepara la query sustituyendo placeholders
    */
   protected function prepareQuery($args)
   {
      $query = '';
      $raw   = array_shift($args);
      $array = preg_split('~(\?[nsiuap])~u', $raw, -1, PREG_SPLIT_DELIM_CAPTURE);
      $anum  = count($args);
      $pnum  = floor(count($array) / 2);

      if ($pnum != $anum) {
         $this->error("Número de argumentos ($anum) no coincide con placeholders ($pnum) en [$raw]");
      }

      foreach ($array as $i => $part) {
         if (($i % 2) == 0) {
            $query .= $part;
            continue;
         }

         $value = array_shift($args);
         switch ($part) {
            case '?n':
               $part = $this->escapeIdent($value);
               break;
            case '?s':
               $part = $this->escapeString($value);
               break;
            case '?i':
               $part = $this->escapeInt($value);
               break;
            case '?a':
               $part = $this->createIN($value);
               break;
            case '?u':
               $part = $this->createSET($value);
               break;
            case '?p':
               $part = $value;
               break;
         }
         $query .= $part;
      }
      return $query;
   }

   protected function escapeInt($value)
   {
      if ($value === NULL) {
         return 'NULL';
      }
      if (!is_numeric($value)) {
         $this->error("Placeholder ?i espera valor numérico, " . gettype($value) . " recibido");
         return FALSE;
      }
      if (is_float($value)) {
         $value = number_format($value, 0, '.', '');
      }
      return $value;
   }

   protected function escapeString($value)
   {
      if ($value === NULL) {
         return 'NULL';
      }
      return "'" . mysqli_real_escape_string($this->conn, $value) . "'";
   }

   protected function escapeIdent($value)
   {
      if ($value) {
         return "`" . str_replace("`", "``", $value) . "`";
      } else {
         $this->error("Valor vacío para placeholder de identificador (?n)");
      }
   }

   protected function createIN($data)
   {
      if (!is_array($data)) {
         $this->error("Valor para placeholder IN (?a) debe ser array");
         return;
      }
      if (!$data) {
         return 'NULL';
      }
      $query = $comma = '';
      foreach ($data as $value) {
         $query .= $comma . $this->escapeString($value);
         $comma  = ",";
      }
      return $query;
   }

   protected function createSET($data)
   {
      if (!is_array($data)) {
         $this->error("Placeholder SET (?u) espera array, " . gettype($data) . " recibido");
         return;
      }
      if (!$data) {
         $this->error("Array vacío para placeholder SET (?u)");
         return;
      }
      $query = $comma = '';
      foreach ($data as $key => $value) {
         $query .= $comma . $this->escapeIdent($key) . '=' . $this->escapeString($value);
         $comma  = ",";
      }
      return $query;
   }

   protected function error($err)
   {
      $err  = __CLASS__ . ": " . $err;
      throw new Exception($err);
   }

   /**
    * Mantiene las estadísticas en un tamaño razonable
    */
   protected function cutStats()
   {
      if (count($this->stats) > 100) {
         reset($this->stats);
         $first = key($this->stats);
         unset($this->stats[$first]);
      }
   }

   /**
    * Verifica si existe al menos un registro que cumpla la condición
    * 
    * Más eficiente que contar o traer datos completos cuando solo
    * necesitas saber si existe algo.
    * 
    * @param string $query Query SQL con placeholders
    * @param mixed ...$args Valores para los placeholders
    * @return bool true si existe al menos un registro, false si no
    * 
    * @example
    * // Verificar si un email ya existe
    * if ($db->exists("SELECT 1 FROM users WHERE email = ?s", $email)) {
    *     die("El email ya está registrado");
    * }
    * 
    * // Verificar permisos
    * if (!$db->exists("SELECT 1 FROM admins WHERE user_id = ?i", $userId)) {
    *     die("Acceso denegado");
    * }
    * 
    * // Verificar relaciones antes de eliminar
    * if ($db->exists("SELECT 1 FROM orders WHERE user_id = ?i", $userId)) {
    *     die("No se puede eliminar: el usuario tiene pedidos");
    * }
    * 
    * // Más eficiente que COUNT
    * // Malo:  $count = $db->getOne("SELECT COUNT(*) FROM huge_table WHERE ...");
    * // Bueno: $exists = $db->exists("SELECT 1 FROM huge_table WHERE ... LIMIT 1");
    */
   public function exists()
   {
      $query = $this->prepareQuery(func_get_args());
      if ($res = $this->rawQuery($query)) {
         $exists = mysqli_num_rows($res) > 0;
         $this->free($res);
         return $exists;
      }
      return false;
   }

   /**
    * Cuenta registros de una tabla con condiciones opcionales
    * 
    * Método simplificado para hacer COUNT() sin escribir SQL completo.
    * 
    * @param string $table Nombre de la tabla
    * @param string $where (Opcional) Condición WHERE con placeholders
    * @param mixed ...$args Valores para los placeholders del WHERE
    * @return int Número de registros que cumplen la condición
    * 
    * @example
    * // Contar todos los registros
    * $total = $db->count("users");
    * 
    * // Contar con condición simple
    * $activos = $db->count("users", "status = ?s", "active");
    * 
    * // Contar con múltiples condiciones
    * $admins = $db->count("users", "role = ?s AND status = ?s", "admin", "active");
    * 
    * // Contar para paginación
    * $totalPosts = $db->count("posts", "category_id = ?i", $categoryId);
    * $totalPages = ceil($totalPosts / $perPage);
    */
   public function count()
   {
      $args = func_get_args();
      $table = array_shift($args);

      if (empty($args)) {
         $query = "SELECT COUNT(*) FROM " . $this->escapeIdent($table);
      } else {
         $where = $this->prepareQuery($args);
         $query = "SELECT COUNT(*) FROM " . $this->escapeIdent($table) . " WHERE " . $where;
      }

      return (int)$this->getOne($query);
   }

   /**
    * Inserta un nuevo registro y retorna el ID generado
    * 
    * Forma simplificada de hacer INSERT. Retorna el ID auto-generado.
    * 
    * @param string $table Nombre de la tabla
    * @param array $data Array asociativo [campo => valor]
    * @return int ID del registro insertado (auto_increment)
    * 
    * @example
    * // Insertar usuario
    * $userId = $db->insert("users", [
    *     'nombre' => 'Juan Pérez',
    *     'email' => 'juan@example.com',
    *     'password' => password_hash('secret', PASSWORD_DEFAULT),
    *     'created_at' => date('Y-m-d H:i:s')
    * ]);
    * echo "Usuario creado con ID: {$userId}";
    * 
    * // Insertar y usar el ID
    * $orderId = $db->insert("orders", ['user_id' => $userId, 'total' => 100.50]);
    * $db->insert("order_items", ['order_id' => $orderId, 'product_id' => 5, 'qty' => 2]);
    * 
    * // Con datos filtrados
    * $safeData = $db->filterArray($_POST, ['nombre', 'email', 'telefono']);
    * $id = $db->insert("contactos", $safeData);
    */
   public function insert($table, $data)
   {
      $this->query("INSERT INTO ?n SET ?u", $table, $data);
      return $this->insertId();
   }

   /**
    * Actualiza registros de una tabla con condición WHERE opcional
    * 
    * Si no se proporciona WHERE, actualiza TODOS los registros (usar con precaución).
    * Retorna el número de filas afectadas.
    * 
    * @param string $table Nombre de la tabla
    * @param array $data Array asociativo [campo => valor] de campos a actualizar
    * @param string $where (Opcional) Condición WHERE con placeholders
    * @param mixed ...$args Valores para los placeholders del WHERE
    * @return int Número de filas afectadas
    * 
    * @example
    * // Actualizar un usuario específico
    * $affected = $db->update("users", 
    *     ['status' => 'inactive', 'last_login' => null],
    *     "id = ?i", 
    *     $userId
    * );
    * echo "Filas actualizadas: {$affected}";
    * 
    * // Actualizar múltiples usuarios
    * $db->update("users", 
    *     ['verified' => 1],
    *     "email LIKE ?s", 
    *     "%@company.com"
    * );
    * 
    * // Actualizar todos (sin WHERE) - ¡Peligroso!
    * // $db->update("users", ['status' => 'inactive']); // Actualiza TODOS
    * 
    * // Con datos del formulario
    * $updateData = $db->filterArray($_POST, ['nombre', 'bio', 'avatar']);
    * $db->update("users", $updateData, "id = ?i", $userId);
    */
   public function update($table, $data)
   {
      $args = func_get_args();
      array_shift($args); // remove table
      array_shift($args); // remove data

      if (empty($args)) {
         $this->query("UPDATE ?n SET ?u", $table, $data);
      } else {
         $where = $this->prepareQuery($args);
         $this->query("UPDATE ?n SET ?u WHERE ?p", $table, $data, $where);
      }

      return $this->affectedRows();
   }

   /**
    * Elimina registros de una tabla con condición WHERE opcional
    * 
    * Si no se proporciona WHERE, elimina TODOS los registros (¡usar con extrema precaución!).
    * Retorna el número de filas eliminadas.
    * 
    * @param string $table Nombre de la tabla
    * @param string $where (Opcional) Condición WHERE con placeholders
    * @param mixed ...$args Valores para los placeholders del WHERE
    * @return int Número de filas eliminadas
    * 
    * @example
    * // Eliminar un usuario específico
    * $deleted = $db->delete("users", "id = ?i", $userId);
    * if ($deleted > 0) {
    *     echo "Usuario eliminado";
    * }
    * 
    * // Eliminar registros antiguos
    * $deleted = $db->delete("logs", "created_at < ?s", date('Y-m-d', strtotime('-30 days')));
    * echo "Eliminados {$deleted} logs antiguos";
    * 
    * // Eliminar con múltiples condiciones
    * $db->delete("sessions", "user_id = ?i AND last_activity < ?s", $userId, $expiredDate);
    * 
    * // Eliminar con IN
    * $db->delete("temp_data", "id IN (?a)", $idsToDelete);
    * 
    * // NUNCA hacer esto sin WHERE:
    * // $db->delete("users"); // ¡ELIMINARÍA TODOS LOS USUARIOS!
    */
   public function delete($table)
   {
      $args = func_get_args();
      array_shift($args); // remove table

      if (empty($args)) {
         $this->query("DELETE FROM ?n", $table);
      } else {
         $where = $this->prepareQuery($args);
         $this->query("DELETE FROM ?n WHERE ?p", $table, $where);
      }

      return $this->affectedRows();
   }

   /**
    * Obtiene array asociativo de dos columnas (clave => valor)
    * 
    * Similar a getIndCol() pero usa la primera columna como clave automáticamente.
    * La query debe seleccionar exactamente 2 columnas.
    * 
    * @param string $query Query SQL que selecciona 2 columnas
    * @param mixed ...$args Valores para los placeholders
    * @return array Array asociativo [columna1 => columna2]
    * 
    * @example
    * // ID => nombre
    * $users = $db->getAssoc("SELECT id, nombre FROM users");
    * // Retorna: [1 => 'Juan', 2 => 'Maria', 3 => 'Pedro']
    * echo $users[1]; // 'Juan'
    * 
    * // Con condiciones
    * $activeUsers = $db->getAssoc("SELECT id, email FROM users WHERE status = ?s", "active");
    * // Retorna: [1 => 'juan@example.com', 2 => 'maria@example.com']
    * 
    * // Para select/dropdown
    * $categories = $db->getAssoc("SELECT id, nombre FROM categorias ORDER BY nombre");
    * foreach ($categories as $id => $nombre) {
    *     echo "<option value='{$id}'>{$nombre}</option>";
    * }
    * 
    * // Precios de productos
    * $prices = $db->getAssoc("SELECT id, precio FROM productos WHERE disponible = ?i", 1);
    * $totalPrice = $prices[5] + $prices[10]; // Suma precios de productos 5 y 10
    */
   public function getAssoc()
   {
      $ret = array();
      $query = $this->prepareQuery(func_get_args());

      if ($res = $this->rawQuery($query)) {
         while ($row = $this->fetch($res, self::RESULT_NUM)) {
            $ret[$row[0]] = $row[1];
         }
         $this->free($res);
      }

      return $ret;
   }

   /**
    * Ejecuta query y retorna la primera fila (alias conveniente de getRow)
    * 
    * Funcionalidad idéntica a getRow(), solo con nombre más descriptivo.
    * 
    * @param string $query Query SQL con placeholders
    * @param mixed ...$args Valores para los placeholders
    * @return array|false Primera fila o false si no existe
    * 
    * @example
    * // Obtener primer resultado
    * $lastOrder = $db->queryFirst("SELECT * FROM orders ORDER BY created_at DESC");
    * 
    * // Equivalente a getRow
    * $user = $db->queryFirst("SELECT * FROM users WHERE email = ?s", $email);
    * // Es lo mismo que:
    * $user = $db->getRow("SELECT * FROM users WHERE email = ?s", $email);
    */
   public function queryFirst()
   {
      return call_user_func_array([$this, 'getRow'], func_get_args());
   }

   /**
    * Obtiene filas organizadas en árbol jerárquico por múltiples campos
    * 
    * Crea una estructura anidada usando los campos especificados como índices.
    * Ideal para agrupar datos por categorías, fechas, etc.
    * 
    * @param string $keys Campos separados por coma para usar como índices
    * @param string $query Query SQL con placeholders
    * @param mixed ...$args Valores para los placeholders
    * @return array Array multidimensional indexado jerárquicamente
    * 
    * @example
    * // Agrupar productos por categoría e ID
    * $products = $db->getTree("category,id", "SELECT * FROM products");
    * // Retorna: ['electronics' => [1 => [...], 2 => [...]], 'books' => [3 => [...]]]
    * echo $products['electronics'][1]['nombre']; // Acceso directo
    * 
    * // Agrupar por año y mes
    * $posts = $db->getTree("year,month", 
    *     "SELECT *, YEAR(created_at) as year, MONTH(created_at) as month FROM posts"
    * );
    * // Retorna: [2024 => [1 => [...], 2 => [...]], 2023 => [...]]
    * foreach ($posts[2024] as $month => $monthPosts) {
    *     echo "Posts de mes {$month}";
    * }
    * 
    * // Agrupar por país y ciudad
    * $users = $db->getTree("country,city", "SELECT * FROM users");
    * // Retorna: ['Mexico' => ['CDMX' => [...], 'Guadalajara' => [...]]]
    */
   public function getTree()
   {
      $args = func_get_args();
      $keys = array_shift($args);
      $query = $this->prepareQuery($args);

      $keys = explode(',', $keys);
      $ret = array();

      if ($res = $this->rawQuery($query)) {
         while ($row = $this->fetch($res)) {
            $ref = &$ret;
            foreach ($keys as $key) {
               $key = trim($key);
               if (!isset($ref[$row[$key]])) {
                  $ref[$row[$key]] = array();
               }
               $ref = &$ref[$row[$key]];
            }
            $ref = $row;
         }
         $this->free($res);
      }

      return $ret;
   }

   /**
    * Inicia una transacción de base de datos
    * 
    * Todas las queries posteriores serán parte de la transacción hasta
    * que se llame commit() o rollback().
    * 
    * @return $this Para encadenamiento de métodos
    * 
    * @example
    * // Uso básico
    * $db->transaction();
    * try {
    *     $db->insert("users", ['nombre' => 'Juan']);
    *     $db->insert("logs", ['action' => 'user_created']);
    *     $db->commit();
    * } catch (Exception $e) {
    *     $db->rollback();
    *     throw $e;
    * }
    * 
    * // Mejor usar transactional() que maneja esto automáticamente
    */
   public function transaction()
   {
      mysqli_begin_transaction($this->conn);
      return $this;
   }

   /**
    * Confirma (commit) la transacción actual
    * 
    * Guarda permanentemente todos los cambios hechos desde transaction().
    * 
    * @return $this Para encadenamiento de métodos
    * 
    * @see transaction(), rollback(), transactional()
    */
   public function commit()
   {
      mysqli_commit($this->conn);
      return $this;
   }

   /**
    * Revierte (rollback) la transacción actual
    * 
    * Cancela todos los cambios hechos desde transaction().
    * La base de datos vuelve al estado anterior.
    * 
    * @return $this Para encadenamiento de métodos
    * 
    * @see transaction(), commit(), transactional()
    */
   public function rollback()
   {
      mysqli_rollback($this->conn);
      return $this;
   }

   /**
    * Ejecuta una función dentro de una transacción con rollback automático
    * 
    * Si la función se ejecuta sin errores, hace commit automáticamente.
    * Si lanza una excepción, hace rollback automáticamente y propaga la excepción.
    * Esta es la forma recomendada de usar transacciones.
    * 
    * @param callable $callback Función a ejecutar. Recibe $db como parámetro
    * @return mixed Lo que retorne la función callback
    * @throws Exception Si la función callback lanza una excepción
    * 
    * @example
    * // Transferencia bancaria segura
    * $db->transactional(function($db) use ($fromUserId, $toUserId, $amount) {
    *     // Restar del usuario origen
    *     $db->query("UPDATE accounts SET balance = balance - ?i WHERE user_id = ?i", 
    *         $amount, $fromUserId
    *     );
    *     
    *     // Sumar al usuario destino
    *     $db->query("UPDATE accounts SET balance = balance + ?i WHERE user_id = ?i", 
    *         $amount, $toUserId
    *     );
    *     
    *     // Registrar la transacción
    *     $db->insert("transactions", [
    *         'from_user' => $fromUserId,
    *         'to_user' => $toUserId,
    *         'amount' => $amount
    *     ]);
    * });
    * // Si algo falla, TODAS las operaciones se revierten automáticamente
    * 
    * // Crear pedido con items
    * $orderId = $db->transactional(function($db) use ($userId, $items) {
    *     // Crear orden
    *     $orderId = $db->insert("orders", ['user_id' => $userId, 'total' => 0]);
    *     
    *     $total = 0;
    *     foreach ($items as $item) {
    *         $db->insert("order_items", [
    *             'order_id' => $orderId,
    *             'product_id' => $item['id'],
    *             'quantity' => $item['qty'],
    *             'price' => $item['price']
    *         ]);
    *         $total += $item['price'] * $item['qty'];
    *     }
    *     
    *     // Actualizar total
    *     $db->update("orders", ['total' => $total], "id = ?i", $orderId);
    *     
    *     return $orderId;
    * });
    * 
    * echo "Orden {$orderId} creada exitosamente";
    */
   public function transactional(callable $callback)
   {
      $this->transaction();

      try {
         $result = $callback($this);
         $this->commit();
         return $result;
      } catch (Exception $e) {
         $this->rollback();
         throw $e;
      }
   }

   /**
    * Busca un registro por su ID (forma simplificada)
    * 
    * Asume que la columna de ID se llama 'id' por defecto,
    * pero se puede especificar otro nombre.
    * 
    * @param string $table Nombre de la tabla
    * @param int $id Valor del ID a buscar
    * @param string $idColumn Nombre de la columna ID (default: 'id')
    * @return array|false El registro encontrado o false si no existe
    * 
    * @example
    * // Buscar por ID (columna 'id')
    * $user = $db->findById("users", 5);
    * if ($user) {
    *     echo "Usuario: {$user['nombre']}";
    * } else {
    *     echo "Usuario no encontrado";
    * }
    * 
    * // Con nombre de columna personalizado
    * $product = $db->findById("products", 123, "product_id");
    * 
    * // Uso común en controladores
    * $userId = $_GET['id'] ?? 0;
    * $user = $db->findById("users", $userId);
    * if (!$user) {
    *     http_response_code(404);
    *     die("Usuario no encontrado");
    * }
    */
   public function findById($table, $id, $idColumn = 'id')
   {
      return $this->getRow("SELECT * FROM ?n WHERE ?n = ?i", $table, $idColumn, $id);
   }

   /**
    * Busca un registro por cualquier campo específico
    * 
    * Similar a findById() pero permite buscar por cualquier campo.
    * Retorna el primer registro que coincida.
    * 
    * @param string $table Nombre de la tabla
    * @param string $field Nombre del campo por el cual buscar
    * @param mixed $value Valor a buscar
    * @return array|false El registro encontrado o false si no existe
    * 
    * @example
    * // Buscar por email
    * $user = $db->findBy("users", "email", "juan@example.com");
    * if ($user) {
    *     echo "Usuario encontrado: {$user['nombre']}";
    * }
    * 
    * // Buscar por username
    * $user = $db->findBy("users", "username", $username);
    * 
    * // Buscar por slug
    * $post = $db->findBy("posts", "slug", $slug);
    * 
    * // Buscar por token
    * $session = $db->findBy("sessions", "token", $token);
    * if (!$session) {
    *     die("Sesión inválida");
    * }
    * 
    * // Verificar unicidad antes de insertar
    * if ($db->findBy("users", "email", $newEmail)) {
    *     die("El email ya está registrado");
    * }
    */
   public function findBy($table, $field, $value)
   {
      return $this->getRow("SELECT * FROM ?n WHERE ?n = ?s", $table, $field, $value);
   }

   /**
    * Obtiene todos los registros de una tabla con condiciones opcionales
    * 
    * Forma simplificada de getAll() con sintaxis más amigable.
    * Permite condiciones WHERE, ORDER BY, LIMIT, etc.
    * 
    * @param string $table Nombre de la tabla
    * @param string $conditions (Opcional) Condición WHERE/ORDER/LIMIT con placeholders
    * @param mixed ...$args Valores para los placeholders
    * @return array Array de registros
    * 
    * @example
    * // Obtener todos sin condiciones
    * $allUsers = $db->findAll("users");
    * 
    * // Con filtro simple
    * $activeUsers = $db->findAll("users", "status = ?s", "active");
    * 
    * // Con ORDER BY
    * $users = $db->findAll("users", "role = ?s ORDER BY nombre ASC", "admin");
    * 
    * // Con LIMIT
    * $latestPosts = $db->findAll("posts", "ORDER BY created_at DESC LIMIT ?i", 10);
    * 
    * // Con múltiples condiciones
    * $products = $db->findAll("products", 
    *     "category = ?s AND precio > ?i ORDER BY precio DESC",
    *     "electronics", 100
    * );
    * 
    * // Paginación
    * $page = 1;
    * $perPage = 20;
    * $offset = ($page - 1) * $perPage;
    * $users = $db->findAll("users", "ORDER BY created_at DESC LIMIT ?i, ?i", $offset, $perPage);
    */
   public function findAll($table)
   {
      $args = func_get_args();
      array_shift($args); // remove table

      if (empty($args)) {
         return $this->getAll("SELECT * FROM ?n", $table);
      } else {
         $condition = $this->prepareQuery($args);
         return $this->getAll("SELECT * FROM ?n WHERE ?p", $table, $condition);
      }
   }

   /**
    * Inserta un registro o lo actualiza si ya existe (UPSERT)
    * 
    * Utiliza ON DUPLICATE KEY UPDATE de MySQL.
    * Si el registro existe (basado en PRIMARY KEY o UNIQUE), lo actualiza.
    * Si no existe, lo inserta.
    * 
    * @param string $table Nombre de la tabla
    * @param array $data Array asociativo con los datos
    * @return int ID del registro (insertado o actualizado)
    * 
    * @example
    * // Insertar o actualizar usuario
    * $db->upsert("users", [
    *     'id' => 1,
    *     'nombre' => 'Juan Pérez',
    *     'email' => 'juan@example.com',
    *     'updated_at' => date('Y-m-d H:i:s')
    * ]);
    * // Si id=1 existe: actualiza nombre, email y updated_at
    * // Si id=1 NO existe: inserta nuevo registro
    * 
    * // Con UNIQUE key en email
    * $db->upsert("users", [
    *     'email' => 'maria@example.com',  // UNIQUE
    *     'nombre' => 'Maria',
    *     'last_login' => date('Y-m-d H:i:s')
    * ]);
    * // Si el email existe: actualiza nombre y last_login
    * // Si no existe: crea nuevo usuario
    * 
    * // Contador de visitas
    * $db->upsert("page_views", [
    *     'page_url' => '/productos',  // UNIQUE
    *     'views' => 1
    * ]);
    * // Primera vez: inserta con views=1
    * // Siguientes veces: actualiza views=1 (necesitarías incrementar manualmente)
    * 
    * @note La tabla debe tener PRIMARY KEY o UNIQUE constraint
    */
   public function upsert($table, $data)
   {
      $this->query("INSERT INTO ?n SET ?u ON DUPLICATE KEY UPDATE ?u", $table, $data, $data);
      return $this->insertId() ?: $this->affectedRows();
   }

   /**
    * Inserta múltiples registros en una sola query (bulk insert)
    * 
    * Mucho más eficiente que hacer INSERT en un loop.
    * Todos los arrays deben tener las mismas claves.
    * 
    * @param string $table Nombre de la tabla
    * @param array $rows Array de arrays asociativos con los datos
    * @return int Número de filas insertadas
    * 
    * @example
    * // Insertar múltiples usuarios
    * $users = [
    *     ['nombre' => 'Juan', 'email' => 'juan@example.com', 'role' => 'user'],
    *     ['nombre' => 'Maria', 'email' => 'maria@example.com', 'role' => 'admin'],
    *     ['nombre' => 'Pedro', 'email' => 'pedro@example.com', 'role' => 'user']
    * ];
    * $inserted = $db->insertBatch("users", $users);
    * echo "Insertados {$inserted} usuarios";
    * 
    * // Importar datos desde CSV
    * $products = [];
    * $file = fopen('products.csv', 'r');
    * while (($row = fgetcsv($file)) !== false) {
    *     $products[] = [
    *         'name' => $row[0],
    *         'price' => $row[1],
    *         'stock' => $row[2]
    *     ];
    * }
    * fclose($file);
    * $db->insertBatch("products", $products);
    * 
    * // Con fecha/hora
    * $now = date('Y-m-d H:i:s');
    * $logs = [
    *     ['action' => 'login', 'user_id' => 1, 'created_at' => $now],
    *     ['action' => 'view_page', 'user_id' => 1, 'created_at' => $now],
    *     ['action' => 'logout', 'user_id' => 1, 'created_at' => $now]
    * ];
    * $db->insertBatch("logs", $logs);
    * 
    * @note Todos los arrays en $rows deben tener las mismas claves
    * @note Es mucho más rápido que múltiples INSERT individuales
    */
   public function insertBatch($table, $rows)
   {
      if (empty($rows)) {
         return 0;
      }

      $keys = array_keys($rows[0]);
      $values = array();

      foreach ($rows as $row) {
         $rowValues = array();
         foreach ($keys as $key) {
            $rowValues[] = $this->escapeString($row[$key]);
         }
         $values[] = "(" . implode(",", $rowValues) . ")";
      }

      $fields = array_map([$this, 'escapeIdent'], $keys);
      $query = "INSERT INTO " . $this->escapeIdent($table) .
         " (" . implode(",", $fields) . ") VALUES " . implode(",", $values);

      $this->rawQuery($query);
      return $this->affectedRows();
   }

   /**
    * Ejecuta una query y retorna un generador PHP (lazy loading)
    * 
    * No carga todos los resultados en memoria a la vez.
    * Ideal para procesar grandes cantidades de datos.
    * Usa yield para retornar fila por fila.
    * 
    * @param string $query Query SQL con placeholders
    * @param mixed ...$args Valores para los placeholders
    * @return \Generator Generador que yield cada fila
    * 
    * @example
    * // Procesar millones de registros sin agotar memoria
    * foreach ($db->cursor("SELECT * FROM huge_table") as $row) {
    *     // Procesa una fila a la vez
    *     processRow($row);
    *     // La memoria se mantiene constante
    * }
    * 
    * // Exportar a CSV grandes datasets
    * $fp = fopen('export.csv', 'w');
    * foreach ($db->cursor("SELECT * FROM orders WHERE year = ?i", 2024) as $order) {
    *     fputcsv($fp, $order);
    * }
    * fclose($fp);
    * 
    * // Con condiciones
    * foreach ($db->cursor("SELECT * FROM users WHERE status = ?s", "active") as $user) {
    *     sendEmail($user['email'], 'Newsletter', $message);
    *     // Envía emails uno por uno sin cargar todos los usuarios
    * }
    * 
    * // Ventaja vs getAll():
    * // getAll(): Carga TODO en memoria (puede fallar con datos grandes)
    * // cursor(): Procesa fila por fila (memoria constante)
    * 
    * @note Solo usar cuando necesites procesar grandes cantidades de datos
    * @note Para datasets pequeños, getAll() es más simple
    */
   public function cursor()
   {
      $query = $this->prepareQuery(func_get_args());
      $res = $this->rawQuery($query);

      if ($res) {
         while ($row = $this->fetch($res)) {
            yield $row;
         }
         $this->free($res);
      }
   }

   /**
    * Obtiene el objeto mysqli subyacente para operaciones avanzadas
    * 
    * Permite acceso directo a funciones mysqli que no están envueltas
    * por esta clase. Usar con cuidado.
    * 
    * @return \mysqli Objeto de conexión mysqli
    * 
    * @example
    * // Usar funciones mysqli directamente
    * $mysqli = $db->getConnection();
    * $mysqli->autocommit(false);
    * 
    * // Obtener información del servidor
    * $mysqli = $db->getConnection();
    * echo "Versión MySQL: " . $mysqli->server_info;
    * echo "Charset: " . $mysqli->character_set_name();
    * 
    * // Prepared statements nativos de mysqli
    * $mysqli = $db->getConnection();
    * $stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
    * $stmt->bind_param("i", $userId);
    * $stmt->execute();
    * $result = $stmt->get_result();
    * 
    * // Cambiar base de datos
    * $db->getConnection()->select_db("otra_base_de_datos");
    * 
    * @note Solo usar cuando realmente necesites funcionalidad mysqli específica
    * @note Preferir los métodos de esta clase para mantener seguridad
    */
   public function getConnection()
   {
      return $this->mysqli;
   }
}
