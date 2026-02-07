<?php
header("Content-Type: application/json");

require "../_utils/database.php";
require "../_utils/api.php";
require "../_utils/session.php";

$db_connection = init_db_connection();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed"]);
    exit();
}

if (is_logged_in()) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Already logged in"]);
    exit();
}

// Capture and sanitize input
$username = sanitize_input($_POST["username"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = sanitize_input($_POST["password"] ?? "");
$confirm_pw = sanitize_input($_POST["confirm-password"] ?? "");

/* Validate inputs */
if (!validate_username($username)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid username"]);
    exit();
}

if (!validate_email($email)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid email"]);
    exit();
}

if (!validate_password($password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid password"]);
    exit();
}

if ($password != $confirm_pw) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Passwords do not match"]);
    exit();
}

// Check if user with that username exists
if (!unique_username($db_connection, $username)) {
    http_response_code(409);
    echo json_encode(["success" => false, "error" => "Username is taken"]);
    exit();
}

// Check if user with that email exists
if (!unique_email($db_connection, $email)) {
    http_response_code(409);
    echo json_encode(["success" => false, "error" => "Email is taken"]);
    exit();
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
    echo json_encode([
        "success" => true,
        "message" => "Successfully created a new user",
    ]);
    // Added this so the user has to login again right after signing up
    session_logout();
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Could not create user"]);
}

$db_connection->close();
exit();
