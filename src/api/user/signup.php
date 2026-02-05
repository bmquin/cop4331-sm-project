<?php
header('Content-Type: application/json');

require "../_utils/database.php";
require "../_utils/api.php";
require "../_utils/session.php";

$db_connection = init_db_connection();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  echo "Method not allowed";
  exit;
}

// Capture and sanitize input
$username = sanitize_input($_POST["username"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = sanitize_input($_POST["password"] ?? "");

/* Validate inputs */
if (!validate_username($username)) {
  http_response_code(400);
  echo json_encode(["error" => "Invalid username"]);
  exit;
}

if (!validate_email($email)) {
  http_response_code(400);
  echo json_encode(["error" => "Invalid email"]);
  exit;
}

if (!validate_password($password)) {
  http_response_code(400);
  echo json_encode(["error" => "Invalid password"]);
  exit;
}

// Check if user with that username exists
if (!unique_username($db_connection, $username)) {
  http_response_code(409);
  echo json_encode(["error" => "Username is taken"]);
  exit;
}

// Check if user with that email exists
if (!unique_email($db_connection, $email)) {
  http_response_code(409);
  echo json_encode(["error" => "Email is taken"]);
  exit;
}

/* Create User */
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$created_usr_id = create_user($db_connection, $username, $email, $hashed_password);

if ($created_usr_id) {
  session_login($created_usr_id);
  echo json_encode(["message" => "Successfully created a new user"]);
} else {
  http_response_code(500);
  echo json_encode(["error" => "Could not create user"]);
}

$db_connection->close();
exit;
