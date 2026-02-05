<?php
session_start();

function is_logged_in()
{
  // TEMPORARY DEV LOGIN
  return true;
}

function session_user_id()
{
  // TEMPORARY DEV USER ID
  return 1;
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
