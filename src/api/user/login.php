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
$password = sanitize_input($_POST["password"] ?? "");

/* Validate inputs */
if (!validate_username($username)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid username"]);
    exit();
}

if (!validate_password($password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid password"]);
    exit();
}

/* Login User */
try {
    $user = find_user_for_login($db_connection, $username, $password);

    if (!$user) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "error" => "Incorrect credentials",
        ]);
        exit();
    }

    session_login($user["id"]);
    echo json_encode(["success" => true, "message" => "Login successful"]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Could not login user"]);
}

$db_connection->close();
exit();
