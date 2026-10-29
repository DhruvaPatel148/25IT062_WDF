<?php
require_once __DIR__ . "/../php/auth.php";
require_login();

$name = htmlspecialchars($_SESSION["name"], ENT_QUOTES, "UTF-8");
$role = htmlspecialchars($_SESSION["role"], ENT_QUOTES, "UTF-8");
$last_login = $_SESSION["last_login"]
    ? htmlspecialchars($_SESSION["last_login"], ENT_QUOTES, "UTF-8")
    : "This is your first login";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | StudentHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <a href="#main-content">Skip to main content</a>

    <header>
        <h1>StudentHub</h1>
        <p>Student Dashboard</p>
    </header>

    <nav aria-label="Main navigation">
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="register.html">Register</a></li>
            <li><a href="login.html">Login</a></li>
            <li><a href="dashboard.php" aria-current="page">Dashboard</a></li>
            <li><a href="events.html">Events</a></li>
            <li><a href="profile.html">Profile</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="faq.html">FAQ</a></li>
            <li><a href="feedback.html">Feedback</a></li>
            <li><a href="admin.php">Admin</a></li>
            <li><a href="../php/logout.php">Logout</a></li>
        </ul>
    </nav>

    <main id="main-content">
        <h2>Welcome, <?= $name ?>!</h2>
        <p>Role: <?= $role ?></p>
        <p>Last login: <?= $last_login ?></p>

        <section>
            <h3>Quick Links</h3>
            <ul>
                <li><a href="profile.html">View Profile</a></li>
                <li><a href="events.html">Explore Events</a></li>
                <li><a href="feedback.html">Submit Feedback</a></li>
            </ul>
        </section>

        <section>
            <h3>Announcements</h3>
            <article>
                <h4>Welcome to StudentHub</h4>
                <p>Check the events page for college activities.</p>
            </article>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 StudentHub</p>
    </footer>
</body>
</html>