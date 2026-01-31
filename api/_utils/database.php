<?php

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

function check_user_exists(mysqli $db, int $user_id)
{
  try {
    $result = $db->query("SELECT id FROM users WHERE id = $user_id LIMIT 1");
    return $result->num_rows > 0;
  } catch (mysqli_sql_exception $e) {
    return null;
  }
  return false;
}

# Check if user with username already exists
function unique_username(mysqli $db, string $username)
{
  $statement = $db->prepare("SELECT id FROM users WHERE username = ?");
  $statement->bind_param("s", $username);
  $statement->execute();

  $result = $statement->get_result();

  return $result->num_rows === 0;
}

function create_user(mysqli $db, string $username, string $email, string $hashed_password)
{
  $statement = $db->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
  $statement->bind_param("sss", $username, $email, $hashed_password);

  if ($statement->execute()) {
    return true;
  } else {
    return false;
  }
}