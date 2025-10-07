<?php

namespace Config\Infrastructure\Connection;

require_once __DIR__ . '/env.php';

use mysqli;
use mysqli_sql_exception;
use function Config\Infrastructure\Env\env;

class Connection
{
  private $connection;

  public function __construct()
  {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
      $this->connection = new mysqli(
        env("DB_HOST"),
        env("DB_USER"),
        env("DB_PASSWORD"),
        env("DB_NAME")
      );

      $this->connection->set_charset(env("DB_CHARSET"));
    } catch (mysqli_sql_exception $e) {
      die("Coneccion fallida: " . $e->getMessage());
    }
  }

  public function getConnection()
  {
    return $this->connection;
  }
}
