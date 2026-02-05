<?php
header('Content-Type: application/json');

require "../_utils/api.php";
require "../_utils/session.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
  http_response_code(405);
  echo "Method not allowed";
  exit;
}

if (is_logged_in()) {
  session_logout();
}

header('Location: /');
exit;
