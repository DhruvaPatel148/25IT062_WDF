<?php
require_once __DIR__ . "/auth.php";

// Empty the session data
$_SESSION = [];

// Delete the session cookie from the browser
$p = session_get_cookie_params();
setcookie(session_name(), "", time() - 3600, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);

// Destroy the session on the server
session_destroy();

header("Location: ../html/login.html?loggedout=1");
exit();
?>