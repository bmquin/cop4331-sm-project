<?php
require "./api/_utils/session.php";

echo is_logged_in() ?
  "You are logged in with user ID: " . session_user_id() : "You are not logged in";


if (is_logged_in()) {
  echo "<a href='/api/user/logout.php'>Logout with this link</a>";
}
