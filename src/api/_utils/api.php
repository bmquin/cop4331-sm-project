<?php
require __DIR__ . '/../../../vendor/autoload.php';

# Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();

function sanitize_input(string $input)
{
  $input = trim($input);
  $input = htmlspecialchars($input);
  return $input;
}

function validate_password(string $password)
{
  if (strlen($password) < 8) {
    return false;
  }

  # Check if pw contains numbers
  if (!preg_match("~[0-9]+~", $password)) {
    return false;
  }

  return true;
}

function validate_email(string $email)
{
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    return false;
  }

  return true;
}

function validate_username(string $username)
{
  if (strlen($username) < 3 || strlen($username) > 10) {
    return false;
  }

  return true;
}

function validate_legal_name(string $name) 
{
  if (strlen($name) < 3 || strlen($name) > 30) {
    return false;
  }

  # Check if name only contains letters
  if (!preg_match("~^[a-zA-Z]+$~", $name)) {
    return false;
  }

  return true;
}

# Lossly validates phone number
function validate_phone(string $phone) 
{
  # Remove all '-' and ' ' from phone number
  $phone = preg_replace('~[-\\s]~', '', $phone);

  if (strlen($phone) > 17 || !preg_match("~[0-9]+~", $phone)) {
    return false;
  }

  return true;
}
