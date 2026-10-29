
<?php
session_start();

$timeout = 900;

if (!isset($_SESSION["student_id"])) {
    header("Location: login.html");
    exit();
}

if (
    !isset($_SESSION["last_activity"]) ||
    time() - $_SESSION["last_activity"] > $timeout
) {
    session_unset();
    session_destroy();

    header("Location: login.html?timeout=1");
    exit();
}

$_SESSION["last_activity"] = time();

if (($_SESSION["role"] ?? "student") !== "admin") {
    http_response_code(403);
    exit("Access denied. Administrator access is required.");
}

$name = htmlspecialchars($_SESSION["name"], ENT_QUOTES, "UTF-8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Administrator Area</h1>
        <p>Welcome, <?= $name ?>!</p>
    </header>

    <main>
        <h2>Admin Dashboard</h2>
        <p>You have administrator access.</p>
        <p><a href="dashboard.php">Back to Dashboard</a></p>
        <p><a href="../php/logout.php">Logout</a></p>
    </main>
</body>
</html>
