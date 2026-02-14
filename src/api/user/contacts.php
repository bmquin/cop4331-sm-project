<?php

require '../_utils/database.php';
require '../_utils/http.php';
require '../_utils/api.php';
require '../_utils/session.php';

/*-----------------
    Session check
  -----------------*/

if (!is_logged_in()) {
  send_error("Not logged in", 401);
}

$user_id = session_user_id();

/*-----------------------------
    Read query + request body
  -----------------------------*/

$q = $_GET['q'] ?? '';
$q = sanitize_input($q);

$data = get_json_body();
if (get_request_method() !== 'GET' && $data === null) {
  send_error("Invalid data format", 400);
}

/*------------------
    Extract inputs
  ------------------*/

$contact_id = $data['contact_id'] ?? null;
$first_name = $data['first_name'] ?? null;
$last_name  = $data['last_name']  ?? null;
$phone      = $data['phone']      ?? null;
$email      = $data['email']      ?? null;

/*-----------------------
    Sanitize + Validate
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

/*-------------------------------------------
    Prevent duplicate email or phone number
  -------------------------------------------*/

if (get_request_method() === 'POST' || get_request_method() === 'PUT') {
  try {
    if (check_contact_email_exists($db_connection, $user_id, $email)) {
      $db_connection->close();
      send_error("Contact with email already exists", 400);
    }

    if (check_phone_number_exists($db_connection, $user_id, $phone)) {
      $db_connection->close();
      send_error("Contact with phone number already exists", 400);
    }
  } catch (Throwable $e) {
    $db_connection->close();
    send_error("Internal Server Error", 500);
  }
}

/*-----------------------------------
    Confirm user and contact exists
  -----------------------------------*/

try {
  // check user existance
  if (!check_user_exists($db_connection, $user_id)) {
    $db_connection->close();
    send_error("User does not exist", 400);
  }

  // check contact existance
  $uses_contact_id = (get_request_method() === 'PUT' || get_request_method() === 'DELETE');
  if ($uses_contact_id && !check_contact_exists($db_connection, $contact_id)) {
    $db_connection->close();
    send_error("Contact does not exist", 400);
  }
} catch (Throwable $e) {
  $db_connection->close();
  send_error("Internal Server Error", 500);
}

/*----------
    Routes
  ----------*/

if (get_request_method() === 'POST') {

  try {
    $success = add_contact(
      $db_connection,
      $user_id,
      $first_name,
      $last_name,
      $phone,
      $email
    );
    $db_connection->close();

    $success
      ? send_success("Successfully added contact")
      : send_error("Could not add contact", 500);

  } catch (Throwable $e) {
    error_log($e->getMessage());
    $db_connection->close();
    send_error("Could not add contact", 500);
  }
}

else if (get_request_method() === 'PUT') {

  try {
    $success = modify_contact(
      $db_connection,
      $user_id,
      $contact_id,
      $first_name,
      $last_name,
      $phone,
      $email
    );
    $db_connection->close();

    $success
      ? send_success("Successfully updated contact")
      : send_error("Could not update contact", 500);

  } catch (Throwable $e) {
    error_log($e->getMessage());
    $db_connection->close();
    send_error("Could not update contact", 500);
  }
}

else if (get_request_method() === 'DELETE') {

  try {
    $success = remove_contact($db_connection, $user_id, $contact_id);
    $db_connection->close();

    $success
      ? send_success("Successfully removed contact")
      : send_error("Could not remove contact", 500);

  } catch (Throwable $e) {
    error_log($e->getMessage());
    $db_connection->close();
    send_error("Could not remove contact", 500);
  }
}

else if (get_request_method() === 'GET') {

  try {
    $contacts = get_contacts($db_connection, $user_id, $q);
    $db_connection->close();

    send_result($contacts);

  } catch (Throwable $e) {
    error_log($e->getMessage());
    $db_connection->close();
    send_error("Could not get contacts", 500);
  }
}

else {
  $db_connection->close();
  send_error("Method not allowed", 405);
}
