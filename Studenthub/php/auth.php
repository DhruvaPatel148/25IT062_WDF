<?php
// php/auth.php - shared session helpers (Practical 10)
// Include this at the top of login.php, logout.php and every protected page.

$base_url = "/Studenthub";   // your project folder name inside htdocs
$timeout_seconds = 900;      // 15 minutes of inactivity

session_set_cookie_params([
    "httponly" => true,      // JavaScript cannot read the session cookie
    "samesite" => "Lax"
]);
session_start();

// Only logged-in users may continue. Idle users are logged out.
function require_login()
{
    global $base_url, $timeout_seconds;

    if (!isset($_SESSION["student_id"])) {
        header("Location: " . $base_url . "/html/login.html");
        exit();
    }

    $last_activity = $_SESSION["last_activity"] ?? 0;

    if (time() - $last_activity > $timeout_seconds) {
        session_unset();
        session_destroy();
        header("Location: " . $base_url . "/html/login.html?timeout=1");
        exit();
    }

    $_SESSION["last_activity"] = time();
}

// Only users with the given role may continue.
function require_role($role)
{
    global $base_url;

    require_login();

    if (($_SESSION["role"] ?? "") !== $role) {
        http_response_code(403);
        exit(
            "<h1>403 - Access denied</h1>" .
            "<p>You do not have permission to view this page.</p>" .
            "<p><a href=\"" . $base_url . "/html/dashboard.php\">Back to Dashboard</a></p>"
        );
    }
}
// Safe output helper for printing data inside HTML
function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

?>