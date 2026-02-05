<?php

function init_db_connection()
{
  $db_conn = new mysqli($_ENV["DB_HOST"], $_ENV["DB_USER"], $_ENV["DB_PASSWORD"], $_ENV["DB_NAME"]);

  if ($db_conn->connect_error) {
    http_response_code(500);
    echo "Internal server error";
    exit;
  }

  return $db_conn;
}

function check_user_exists(mysqli $db, int $user_id)
{
  $statement = $db->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
  $statement->bind_param("i", $user_id);
  $statement->execute();

  $result = $statement->get_result();

  return $result->num_rows > 0;
}

# Find user by username and hashed_password
function find_user_for_login(mysqli $db, string $username, string $hashed_password)
{
  $statement = $db->prepare("select id from users where username = ? AND password_hash = ?");
  $statement->bind_param("is", $username, $hashed_password);
  $statement->execute();

  $result = $statement->get_result();

  return $result;
}

# Check if user with email already exists
function unique_email(mysqli $db, string $email)
{
  $statement = $db->prepare("SELECT id FROM users WHERE email = ?");
  $statement->bind_param("s", $email);
  $statement->execute();

  $result = $statement->get_result();

  return $result->num_rows === 0;
}

# Check if user with username already exists
function unique_username(mysqli $db, string $username)
{
  $statement = $db->prepare("SELECT id FROM users WHERE username = ?");
  $statement->bind_param("s", $username);
  $statement->execute();

  $result = $statement->get_result();

  return $result->num_rows === 0;
}

function create_user(mysqli $db, string $username, string $email, string $hashed_password)
{
  $statement = $db->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
  $statement->bind_param("sss", $username, $email, $hashed_password);

  if ($statement->execute()) {
    # Return user id of last name created
    return $db->insert_id;
  } else {
    return 0;
  }
}

/* ----------------------------
     Contact Database Utility
   ---------------------------- */

function check_contact_exists(mysqli $db, int $contact_id)
{
  $statement = $db->prepare("SELECT id FROM contacts WHERE id = ? LIMIT 1");
  $statement->bind_param("i", $contact_id);
  $statement->execute();

  $result = $statement->get_result();

  return $result->num_rows > 0;
}

function add_contact(mysqli $db, int $user_id, string $first_name, string $last_name, string $phone, string $email)
{
  $statement = $db->prepare("INSERT INTO contacts (user_id, first_name, last_name, phone, email) VALUES (?, ?, ?, ?, ?)");
  $statement->bind_param("issss", $user_id, $first_name, $last_name, $phone, $email);

  return (bool) $statement->execute();
}

function modify_contact(mysqli $db, int $user_id, int $contact_id, string $first_name, string $last_name, string $phone, string $email)
{
  $statement = $db->prepare("UPDATE contacts SET first_name = ?, last_name = ?, phone = ?, email = ? WHERE id = ? AND user_id = ?");
  $statement->bind_param("ssssii", $first_name, $last_name, $phone, $email, $contact_id, $user_id);

  return (bool) $statement->execute();
}

function remove_contact(mysqli $db, int $user_id, int $contact_id)
{
  $statement = $db->prepare("DELETE FROM contacts WHERE id = ? AND user_id = ?");
  $statement->bind_param("ii", $contact_id, $user_id);

  return (bool) $statement->execute();
}

function get_contacts(mysqli $db, int $user_id)
{
  $statement = $db->prepare("SELECT * FROM contacts WHERE user_id = ?");
  $statement->bind_param("i", $user_id);
  $statement->execute();

  $result = $statement->get_result();
  $contacts = $result->fetch_all(MYSQLI_ASSOC);

  return $contacts;
}
