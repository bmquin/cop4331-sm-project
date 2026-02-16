<?php

#--------------------------
#  Client Request Utility
#--------------------------

function get_request_method()
{
  return $_SERVER['REQUEST_METHOD'];
}

function get_uri()
{
  return $_SERVER['REQUEST_URI'];
}

function get_body()
{
  return file_get_contents('php://input');
}

function get_json_body()
{
  $json_body = json_decode(get_body());
  if (json_last_error() == JSON_ERROR_NONE) {
    return (array)$json_body;
  } else {
    return null;
  }
}

#---------------------------
#  Server Response Utility
#---------------------------

function send_json(object|array $body)
{
  header('Content-Type: application/json');
  echo json_encode($body);
  exit;
}

function send_result(object|array $result)
{
  $body = ["success" => true, "result" => array_values((array)$result)];
  send_json($body);
}

function send_error(string $msg, int $error_code = 500)
{
  http_response_code($error_code);
  $body = ["success" => false, "error" => $msg];
  send_json($body);
}

function send_success($msg)
{
  $body = ["success" => true, "message" => $msg];
  send_json($body);
}
