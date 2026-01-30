<?php

use mysqli;

function init_db_connection()
{
  $db_conn = new mysqli($_ENV["DB_HOST"], $_ENV["DB_USER"], $_ENV["DB_PASSWORD"], $_ENV["DB_NAME"]);

  if ($db_conn->connect_error) {
    http_response_code(500);
    echo "Internal server error";
    exit;
  }

  return $db_conn;
}

function check_user_exists(mysqli $conn, int $user_id) {
  try {
    $res = $conn->query("SELECT id FROM users WHERE id = $user_id LIMIT 1");
    return $res->num_rows > 0;
  } catch(mysqli_sql_exception $e) {
    return null;
  }
  return false;
}
