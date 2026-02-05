<?php
session_start();

function is_logged_in()
{
  if (isset($_SESSION["logged_in"])) {
    return $_SESSION["logged_in"];
  }

  return false;
}

function session_user_id()
{
  return $_SESSION["user_id"];
}


function session_login($user_id)
{
  session_regenerate_id(true);
  $_SESSION["user_id"] = $user_id;
  $_SESSION["logged_in"] = true;
}

function session_logout()
{
  session_unset();
  session_destroy();
  $_SESSION["logged_in"] = false;
}
