
<?php
require "db.php";

$sql = "SELECT * FROM students WHERE email = :email";
$stmt = $conn->prepare($sql);

$email = "student@example.com";
$stmt->execute(["email" => $email]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

if ($student) {
    echo "Student found: " . htmlspecialchars($student["name"]);
} else {
    echo "Database connected successfully! No matching student found.";
}
?>
