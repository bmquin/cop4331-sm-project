<?php

require __DIR__ . '/_utils/database.php';
require __DIR__ . '/_utils/http.php';
require __DIR__ . '/_utils/api.php';
require __DIR__ . '/../vendor/autoload.php';

# Send error if request body isn't json formatted
$data = get_json_body();
if (get_request_method() != 'GET' && $data === null) {
  send_error("Invalid data format", 400);
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

# Connect to mysql database
$db = init_db_connection();

# Check if target user exists
if (!check_user_exists($db, $user_id)) {
  $db->close();
  send_error("User does not exist", 400);
}

# Check if target contact exists
if ((get_request_method() == 'PUT' || get_request_method() == 'DELETE') && !check_contact_exists($db, $contact_id)) {
  $db->close();
  send_error("Contact does not exist", 400);
}

# TODO: confirm user session, send error if session is invalid.

#----------------------
#  POST - add contact
#----------------------
if (get_request_method() == 'POST') {
  $success = add_contact($db, $user_id, $first_name, $last_name, $phone, $email);
  $db->close();

  if ($success) {
    send_message("Successfully added contact");
  } else {
    send_error("Could not add contact", 500);
  }
} 

#------------------------
#  PUT - modify contact
#------------------------
else if (get_request_method() == 'PUT') {
  $success = modify_contact($db, $user_id, $contact_id, $first_name, $last_name, $phone, $email);
  $db->close();

  if ($success) {
    send_message("Successfully updated contact");
  } else {
    send_error("Could not update contact", 500);
  }
}

#---------------------------
#  DELETE - remove contact
#---------------------------
else if (get_request_method() == 'DELETE') {
  $success = remove_contact($db, $user_id, $contact_id);
  $db->close();

  if ($success) {
    send_message("Successfully removed contact");
  } else {
    send_error("Could not remove contact", 500);
  }
}

#----------------------
#  GET - get contacts
#----------------------
else if (get_request_method() == 'GET') {
  $contacts = get_contacts($db, $user_id);
  $db->close();

  send_result($contacts);
}

#---------------------------
#  Invalid request method
#---------------------------
else {
  $db->close();
  send_error("Method not allowed", 500);
}