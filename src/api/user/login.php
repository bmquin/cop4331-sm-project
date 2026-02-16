<?php
header("Content-Type: application/json");

require "../_utils/database.php";
require '../_utils/http.php';
require "../_utils/api.php";
require "../_utils/session.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  send_error("Method not allowed", 405);
}

if (is_logged_in()) {
  send_error("Already logged in", 403);
}

$data = get_json_body();
// Capture and sanitize input
$username = sanitize_input($data["username"] ?? "");
$password = sanitize_input($data["password"] ?? "");

/* Validate inputs */
if (!validate_username($username)) {
  send_error("Invalid username", 400);
}

if (!validate_password($password)) {
  send_error("Invalid password", 400);
}

/* Login User */
$db_connection = init_db_connection();
try {
  $user = find_user_for_login($db_connection, $username, $password);

  if (!$user) {
    send_error("Invalid credentials", 401);
  }

  session_login($user["id"]);
  send_success("Login successful");
} catch (Throwable $e) {
  error_log($e->getMessage());
  send_error("Could not login user");
}

$db_connection->close();
exit();
