<?php
require_once __DIR__ . "/../php/auth.php";
require_once __DIR__ . "/../php/db.php";
require_role("admin");

// id > 0 means Edit mode, otherwise Add mode
$id = (int)($_GET["id"] ?? $_POST["id"] ?? 0);
$is_edit = $id > 0;
$errors = [];

$form = [
    "student_id" => "",
    "name" => "",
    "email" => "",
    "mobile" => "",
    "course" => "",
    "year" => "",
    "gender" => ""
];

// Edit mode: load the existing record into the form
if ($is_edit && $_SERVER["REQUEST_METHOD"] !== "POST") {
    $stmt = $conn->prepare(
        "SELECT student_id, name, email, mobile, course, year, gender
         FROM students WHERE id = :id"
    );
    $stmt->execute([":id" => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        header("Location: admin_students.php?msg=notfound");
        exit();
    }

    $form = $row;
}

// Form submitted: validate, then save
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($form as $key => $value) {
        $form[$key] = trim($_POST[$key] ?? "");
    }
    $new_password = $_POST["new_password"] ?? "";

    if ($form["name"] === "" || !preg_match("/^[a-zA-Z ]+$/", $form["name"])) {
        $errors[] = "Please enter a valid name (letters and spaces only).";
    }
    if (!filter_var($form["email"], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (!preg_match("/^[0-9]{10}$/", $form["mobile"])) {
        $errors[] = "Mobile number must contain exactly 10 digits.";
    }
    if ($form["student_id"] === "") {
        $errors[] = "Student ID is required.";
    }
    if (!in_array($form["course"], ["IT", "CE", "CSE"], true)) {
        $errors[] = "Please select a valid course.";
    }
    if (!in_array($form["year"], ["1", "2", "3", "4"], true)) {
        $errors[] = "Please select a valid year.";
    }
    if (!in_array($form["gender"], ["Male", "Female", "Other"], true)) {
        $errors[] = "Please select a gender.";
    }
    // Password is required when adding. When editing, empty = keep old one.
    if ((!$is_edit || $new_password !== "") && strlen($new_password) < 8) {
        $errors[] = "Password must contain at least 8 characters.";
    }

    if (empty($errors)) {
        try {
            // Duplicate check (ignores the student being edited)
            $check = $conn->prepare(
                "SELECT email, student_id FROM students
                 WHERE (email = :email OR student_id = :student_id)
                 AND id <> :id"
            );
            $check->execute([
                ":email" => $form["email"],
                ":student_id" => $form["student_id"],
                ":id" => $id
            ]);

            foreach ($check->fetchAll() as $existing) {
                if (strcasecmp($existing["email"], $form["email"]) === 0) {
                    $errors[] = "This email is already used by another student.";
                }
                if (strcasecmp($existing["student_id"], $form["student_id"]) === 0) {
                    $errors[] = "This Student ID is already used by another student.";
                }
            }

            if (empty($errors)) {
                $data = [
                    ":student_id" => $form["student_id"],
                    ":name" => $form["name"],
                    ":email" => $form["email"],
                    ":mobile" => $form["mobile"],
                    ":course" => $form["course"],
                    ":year" => (int)$form["year"],
                    ":gender" => $form["gender"]
                ];

                if ($is_edit) {
                    $sql = "UPDATE students SET student_id = :student_id, name = :name,
                            email = :email, mobile = :mobile, course = :course,
                            year = :year, gender = :gender";

                    if ($new_password !== "") {
                        $sql .= ", password_hash = :password_hash";
                        $data[":password_hash"] = password_hash($new_password, PASSWORD_DEFAULT);
                    }

                    $sql .= " WHERE id = :id";
                    $data[":id"] = $id;
                    $result = "updated";
                } else {
                    $sql = "INSERT INTO students
                            (student_id, name, email, password_hash, mobile, course, year, gender)
                            VALUES
                            (:student_id, :name, :email, :password_hash, :mobile, :course, :year, :gender)";
                    $data[":password_hash"] = password_hash($new_password, PASSWORD_DEFAULT);
                    $result = "added";
                }

                $conn->prepare($sql)->execute($data);

                header("Location: admin_students.php?msg=" . $result);
                exit();
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = "Unable to save the student. Please try again.";
        }
    }
}

$title = $is_edit ? "Edit Student" : "Add Student";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - <?= h($title) ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1><?= h($title) ?></h1>
        <p>Student Management</p>
    </header>

    <nav aria-label="Main navigation">
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="admin.php">Admin</a></li>
            <li><a href="admin_students.php">Manage Students</a></li>
            <li><a href="../php/logout.php">Logout</a></li>
        </ul>
    </nav>

    <main>
        <section>
            <h2><?= h($title) ?></h2>

            <?php if (!empty($errors)): ?>
                <ul class="error" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="post" action="student_form.php">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="id" value="<?= $id ?>">
                <?php endif; ?>

                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" value="<?= h($form["name"]) ?>" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= h($form["email"]) ?>" required>

                <label for="mobile">Mobile Number:</label>
                <input type="tel" id="mobile" name="mobile" pattern="[0-9]{10}"
                       value="<?= h($form["mobile"]) ?>" required>

                <label for="student_id">Student ID:</label>
                <input type="text" id="student_id" name="student_id"
                       value="<?= h($form["student_id"]) ?>" required>

                <label for="course">Course:</label>
                <select id="course" name="course" required>
                    <option value="">Select Course</option>
                    <?php foreach (["IT" => "Information Technology", "CE" => "Computer Engineering", "CSE" => "Computer Science"] as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $form["course"] === $value ? "selected" : "" ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="year">Year of Study:</label>
                <select id="year" name="year" required>
                    <option value="">Select Year</option>
                    <?php foreach ([1 => "First Year", 2 => "Second Year", 3 => "Third Year", 4 => "Fourth Year"] as $value => $label): ?>
                        <option value="<?= $value ?>" <?= (string)$form["year"] === (string)$value ? "selected" : "" ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>

                <p>Gender:</p>
                <?php foreach (["Male", "Female", "Other"] as $g): ?>
                    <input type="radio" id="gender_<?= $g ?>" name="gender" value="<?= $g ?>"
                           <?= $form["gender"] === $g ? "checked" : "" ?> required>
                    <label for="gender_<?= $g ?>"><?= $g ?></label>
                <?php endforeach; ?>

                <label for="new_password">
                    Password<?= $is_edit ? " (leave empty to keep the current password)" : "" ?>:
                </label>
                <input type="password" id="new_password" name="new_password"
                       <?= $is_edit ? "" : "required" ?>>

                <button type="submit"><?= $is_edit ? "Update Student" : "Add Student" ?></button>
                <p><a href="admin_students.php">Cancel</a></p>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 StudentHub</p>
    </footer>
</body>
</html>