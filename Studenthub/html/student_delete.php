<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";
require_role("admin");

// Delete only works from the form (POST), never from a plain link
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../html/admin_students.php");
    exit();
}

$id = (int)($_POST["id"] ?? 0);

$stmt = $conn->prepare("SELECT student_id, role FROM students WHERE id = :id");
$stmt->execute([":id" => $id]);
$target = $stmt->fetch();

if (!$target) {
    header("Location: ../html/admin_students.php?msg=notfound");
    exit();
}

// Safety: never delete an admin or the account you are logged in with
if ($target["role"] === "admin" || $target["student_id"] === $_SESSION["student_id"]) {
    header("Location: ../html/admin_students.php?msg=blocked");
    exit();
}

$delete = $conn->prepare("DELETE FROM students WHERE id = :id");
$delete->execute([":id" => $id]);

header("Location: ../html/admin_students.php?msg=deleted");
exit();
?>