<?php
require "../_utils/http.php";
require "../_utils/session.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
  send_error("Method not allowed", 405);
}

echo json_encode([
    "logged_in" => is_logged_in()
]);