<?php
require_once __DIR__ . "/../php/auth.php";
require_role("admin");

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
        <h1>Admin Page</h1>
        <p>Welcome, <?= $name ?>! StudentHub administration</p>
    </header>

    <nav aria-label="Main navigation">
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="register.html">Register</a></li>
            <li><a href="login.html">Login</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="events.html">Events</a></li>
            <li><a href="profile.html">Profile</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="faq.html">FAQ</a></li>
            <li><a href="feedback.html">Feedback</a></li>
            <li><a href="admin.php" aria-current="page">Admin</a></li>
            <li><a href="../php/logout.php">Logout</a></li>
        </ul>
    </nav>

    <main>
        <section>
            <h2>Administration</h2>
            <p>This page is visible to administrators only.</p>

            <div class="grid-container">
                <article class="card">
    <h3>Manage Students</h3>
    <p>Add, edit, search and delete student records.</p>
    <a href="admin_students.php">Open Student Management</a>
</article>

                <article class="card">
                    <h3>Manage Events</h3>
                    <p>Event management will be added in a later practical.</p>
                </article>

                <article class="card">
                    <h3>View Feedback</h3>
                    <p>Feedback management will be added in a later practical.</p>
                </article>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 StudentHub</p>
    </footer>
</body>
</html>