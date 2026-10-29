<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// Only accept form submissions
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../html/login.html");
    exit();
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

$stmt = $conn->prepare(
    "SELECT id, student_id, name, email, password_hash, role, last_login
     FROM students
     WHERE email = :email
     LIMIT 1"
);
$stmt->execute([":email" => $email]);
$student = $stmt->fetch();

// One generic message, so attackers cannot tell which part was wrong
if (!$student || !password_verify($password, $student["password_hash"])) {
    header("Location: ../html/login.html?error=1");
    exit();
}

// Login is correct: create a fresh session ID, then save user details
session_regenerate_id(true);

$_SESSION["student_id"] = $student["student_id"];
$_SESSION["name"] = $student["name"];
$_SESSION["email"] = $student["email"];
$_SESSION["role"] = $student["role"];
$_SESSION["last_login"] = $student["last_login"];   // previous login time
$_SESSION["last_activity"] = time();

// Record this login time
$update = $conn->prepare("UPDATE students SET last_login = NOW() WHERE id = :id");
$update->execute([":id" => $student["id"]]);

// Role-based redirection
if ($student["role"] === "admin") {
    header("Location: ../html/admin.php");
} else {
    header("Location: ../html/dashboard.php");
}
exit();
?>