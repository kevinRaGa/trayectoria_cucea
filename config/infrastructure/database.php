<?php

require_once __DIR__ . '/env.php';

class Database {
  private $connection;

  public function __construct()
  {
    try {
      $this->connection = new PDO(
        "mysql:host=" . env("DB_HOST") . ";dbname=" . env("DB_NAME,") . ";charset=" . env("DB_CHARSET"),
        env("DB_USER"),
        env("DB_PASSWORD"), [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
          PDO::ATTR_EMULATE_PREPARES => false,
        ]
      );
    } catch (PDOException $e) {
      die("Conneccion fallida: " . $e->getMessage());
    }
    
  }

  public function getConnection()
  {
    return $this->connection;
  }

}

$db = new Database();           
$pdo = $db->getConnection();

?>