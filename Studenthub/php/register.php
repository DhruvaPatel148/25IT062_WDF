
<?php
$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $student_id = trim($_POST["student_id"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $year = trim($_POST["year"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $terms = $_POST["terms"] ?? "";

    if ($name === "" || !preg_match("/^[a-zA-Z ]+$/", $name)) {
        $errors[] = "Please enter a valid name.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $errors[] = "Mobile number must contain exactly 10 digits.";
    }

    if ($student_id === "") {
        $errors[] = "Student ID is required.";
    }

    if (!in_array($course, ["IT", "CE", "CSE"], true)) {
        $errors[] = "Please select a valid course.";
    }

    if (!in_array($year, ["1", "2", "3", "4"], true)) {
        $errors[] = "Please select a valid year.";
    }

    if (!in_array($gender, ["Male", "Female", "Other"], true)) {
        $errors[] = "Please select a gender.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must contain at least 8 characters.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if ($terms !== "yes") {
        $errors[] = "Please accept the terms and conditions.";
    }

    if (empty($errors)) {
        try {
            require_once __DIR__ . "/db.php";

            $check = $conn->prepare(
                "SELECT email, student_id
                 FROM students
                 WHERE email = :email OR student_id = :student_id"
            );

            $check->execute([
                ":email" => $email,
                ":student_id" => $student_id
            ]);

            $existing = $check->fetch();

            if ($existing) {
                if ($existing["email"] === $email) {
                    $errors[] = "This email is already registered.";
                }

                if ($existing["student_id"] === $student_id) {
                    $errors[] = "This Student ID is already registered.";
                }
            } else {
                $password_hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $stmt = $conn->prepare(
                    "INSERT INTO students
                    (student_id, name, email, password_hash,
                     mobile, course, year, gender)
                    VALUES
                    (:student_id, :name, :email, :password_hash,
                     :mobile, :course, :year, :gender)"
                );

                $stmt->execute([
                    ":student_id" => $student_id,
                    ":name" => $name,
                    ":email" => $email,
                    ":password_hash" => $password_hash,
                    ":mobile" => $mobile,
                    ":course" => $course,
                    ":year" => (int)$year,
                    ":gender" => $gender
                ]);

                $success = "Registration successful! You can now log in.";
            }

        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = "Unable to complete registration. Please try again.";
        }
    }
}

function escapeOutput($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Registration Result</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>StudentHub</h1>
        <p>Registration Result</p>
    </header>

    <main>
        <?php if ($success !== ""): ?>
            <h2>Registration Successful</h2>
            <p><?= escapeOutput($success) ?></p>
            <p><a href="../html/login.html">Go to Login</a></p>
        <?php else: ?>
            <h2>Registration Error</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= escapeOutput($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <p><a href="../html/register.html">Back to Registration</a></p>
        <?php endif; ?>
    </main>
</body>
</html>
