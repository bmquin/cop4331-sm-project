<?php
header("Content-Type: application/json");

require "../_utils/database.php";
require "../_utils/http.php";
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
$email = trim($data["email"] ?? "");
$password = sanitize_input($data["password"] ?? "");
$confirm_pw = sanitize_input($data["confirm-password"] ?? "");

/* Validate inputs */
if (!validate_username($username)) {
  send_error("Invalid username", 400);
}

if (!validate_email($email)) {
  send_error("Invalid email", 400);
}

if (!validate_password($password)) {
  send_error("Invalid password", 400);
}

if ($password != $confirm_pw) {
  send_error("Passwords do not match", 400);
}

$db_connection = init_db_connection();
// Check if user with that username exists
if (!unique_username($db_connection, $username)) {
  send_error("Username is taken", 409);
}

// Check if user with that email exists
if (!unique_email($db_connection, $email)) {
  send_error("Email is taken", 409);
}

/* Create User */
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
  $created_usr_id = create_user(
    $db_connection,
    $username,
    $email,
    $hashed_password,
  );
  session_login($created_usr_id);
  send_success("Successfully logged in user");
} catch (Throwable $e) {
  send_error("Could not create user");
}

$db_connection->close();
exit();
