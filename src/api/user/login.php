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
$password = sanitize_input($_POST["password"] ?? "");

/* Validate inputs */
if (!validate_username($username)) {
  http_response_code(400);
  echo json_encode(["error" => "Invalid username"]);
  exit;
}

if (!validate_password($password)) {
  http_response_code(400);
  echo json_encode(["error" => "Invalid password"]);
  exit;
}

/* Login User */
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$user = find_user_for_login($db_connection, $username, $hashed_password);

if ($user) {
  $user_result = $user->fetch_assoc();
  session_login($user_result["id"]);
  echo json_encode(["message" => "Login successful"]);
} else {
  http_response_code(500);
  echo json_encode(["error" => "Could not login user"]);
}

$db_connection->close();
exit;
