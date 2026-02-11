<?php
require_once __DIR__ . '/../session.php';

session_logout();

http_response_code(200);