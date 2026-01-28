<?php

namespace Utils;

use mysqli;

class Database
{
  static function connect()
  {
    $db_conn = new mysqli($_ENV["DB_HOST"], $_ENV["DB_USER"], $_ENV["DB_PASSWORD"], $_ENV["DB_NAME"]);

    if ($db_conn->connect_error) {
      http_response_code(500);
      echo "Internal server error";
      exit;
    }

    return $db_conn;
  }
}
