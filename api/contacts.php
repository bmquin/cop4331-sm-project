<?php

require __DIR__ . '/_utils/database.php';
require __DIR__ . '/_utils/http.php';
require __DIR__ . '/_utils/api.php';
require __DIR__ . '/_utils/session.php';
require __DIR__ . '/../vendor/autoload.php';

/*-----------------
    Session check
  -----------------*/

if (!is_logged_in()) {
  send_error("Not logged in", 401);
}

$user_id = session_user_id();

/*-------------------------
    Validate request body
  -------------------------*/

$data = get_json_body();
if (get_request_method() !== 'GET' && $data === null) {
  send_error("Invalid data format", 400);
}

/*-----------------
    Extract inputs
  -----------------*/

$contact_id = $data['contact_id'] ?? null;
$first_name = $data['first_name'] ?? null;
$last_name  = $data['last_name']  ?? null;
$phone      = $data['phone']      ?? null;
$email      = $data['email']      ?? null;

/*-----------------------
    Sanitize + validate
  -----------------------*/

if (get_request_method() === 'POST' || get_request_method() === 'PUT') {

  $first_name = sanitize_input($first_name);
  $last_name  = sanitize_input($last_name);
  $phone      = sanitize_input($phone);
  $email      = sanitize_input($email);

  if (!validate_legal_name($first_name) || !validate_legal_name($last_name)) {
    send_error("Invalid first or last name", 400);
  }

  if (!validate_phone($phone)) {
    send_error("Invalid phone number", 400);
  }

  if (!validate_email($email)) {
    send_error("Invalid email", 400);
  }
}

/*-----------------------
    Database connection
  -----------------------*/

$db_connection = init_db_connection();

/*-----------------------
    Confirm user exists
  -----------------------*/

if (!check_user_exists($db_connection, $user_id)) {
  $db_connection->close();
  send_error("User does not exist", 400);
}

/*---------------------------
    Check contact existence
  ---------------------------*/

$uses_contact_id = (get_request_method() === 'PUT' || get_request_method() === 'DELETE');
if ($uses_contact_id && !check_contact_exists($db_connection, $contact_id)) {
  $db_connection->close();
  send_error("Contact does not exist", 400);
}

/*----------
    Routes
  ----------*/

if (get_request_method() === 'POST') {

  $success = add_contact($db_connection, $user_id, $first_name, $last_name, $phone, $email);
  $db_connection->close();

  $success
    ? send_message("Successfully added contact")
    : send_error("Could not add contact", 500);
}

else if (get_request_method() === 'PUT') {

  $success = modify_contact($db_connection, $user_id, $contact_id, $first_name, $last_name, $phone, $email);
  $db_connection->close();

  $success
    ? send_message("Successfully updated contact")
    : send_error("Could not update contact", 500);
}

else if (get_request_method() === 'DELETE') {

  $success = remove_contact($db_connection, $user_id, $contact_id);
  $db_connection->close();

  $success
    ? send_message("Successfully removed contact")
    : send_error("Could not remove contact", 500);
}

else if (get_request_method() === 'GET') {

  $contacts = get_contacts($db_connection, $user_id);
  $db_connection->close();

  send_result($contacts);
}

else {

  $db_connection->close();
  send_error("Method not allowed", 405);
}
