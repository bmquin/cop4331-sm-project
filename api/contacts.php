<?php

require __DIR__ . '/_utils/Database.php';
require __DIR__ . '/_utils/Http.php';
require __DIR__ . '/_utils/api.php';
require __DIR__ . '/../vendor/autoload.php';

# Send error if request body isn't json formatted
$data = get_json_body();
if (get_request_method() != 'GET' && $data === null) {
  send_error("Invalid data format");
}

# Retrieve contact information
$user_id = getallheaders()['user_id'] ?? null;
$contact_id = $data['contact_id'] ?? null;
$first_name = $data['first_name'] ?? null;
$last_name = $data['last_name'] ?? null;
$phone = $data['phone'] ?? null;
$email = $data['email'] ?? null;

# Sanatize and validate input
if (get_request_method() == 'POST' || get_request_method() == 'PUT') {
  $first_name = sanitize_input($first_name);
  $last_name = sanitize_input($last_name);
  $phone = sanitize_input($phone);
  $email = sanitize_input($email);

  if (!validate_email($email)) {
    send_error("Invalid email address");
  }
}

# Connect to mysql database
$db = init_db_connection();

# Check if target user exists
if (!check_user_exists($db, $user_id)) {
  $db->close();
  send_error("User does not exist");
}

# TODO: confirm user session, send error if session is invalid.

#----------------------
#  POST - add contact
#----------------------
if (get_request_method() == 'POST') {
  try {
    $statement = "INSERT INTO contacts (user_id, first_name, last_name, phone, email) VALUES ('$user_id', '$first_name', '$last_name', '$phone', '$email')";
    $result = $db->query($statement);

  } catch (mysqli_sql_exception $e) {
    $db->close();
    send_error("Internal server error");
  }

  $db->close();
  send_success();
} 

#------------------------
#  PUT - update contact
#------------------------
if (get_request_method() == 'PUT') {
  try {
    $statement = "UPDATE contacts SET first_name = '$first_name', last_name = '$last_name', phone = '$phone', email = '$email' WHERE id = '$contact_id' AND user_id = '$user_id'";
    $result = $db->query($statement);

  } catch (mysqli_sql_exception $e) {
    $db->close();
    send_error("Internal server error");
  }

  if ($db->affected_rows == 0) {
    $db->close();
    send_error("Contact does not exist, existing values are already equal to new values, or user does not have this contact");
  }

  $db->close();
  send_success();
}

#---------------------------
#  DELETE - remove contact
#---------------------------
else if (get_request_method() == 'DELETE') {
  try {
    $result = $db->query("DELETE FROM contacts WHERE id = $contact_id AND user_id = $user_id");
  } catch (mysqli_sql_exception $e) {
    $db->close();
    send_error("Internal server error");
  }

  if ($db->affected_rows == 0) {
    $db->close();
    send_error("Contact does not exist or user does not have this contact");
  }

  $db->close();
  send_success();
}

#----------------------
#  GET - get contacts
#----------------------
else if (get_request_method() == 'GET') {
  try {
    $result = $db->query("SELECT * FROM contacts WHERE user_id = $user_id");
  } catch (mysqli_sql_exception $e) {
    $db->close();
    send_error("Internal server error");
  }

  $contacts = $result->fetch_all(MYSQLI_ASSOC);

  $db->close();
  send_result($contacts);
}

#---------------------------
#  Invalid request method
#---------------------------
else {
  $db->close();
  send_error("Invalid request method");
}