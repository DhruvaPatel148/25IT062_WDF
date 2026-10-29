<?php
require_once __DIR__ . "/../php/auth.php";
require_once __DIR__ . "/../php/db.php";
require_role("admin");

$per_page = 10;
$allowed_courses = ["IT", "CE", "CSE"];
$allowed_years = ["1", "2", "3", "4"];

// 1. Read search and filter values from the URL
$search = trim($_GET["search"] ?? "");
$course = $_GET["course"] ?? "";
$year = $_GET["year"] ?? "";
$page = max(1, (int)($_GET["page"] ?? 1));

if (!in_array($course, $allowed_courses, true)) {
    $course = "";
}
if (!in_array($year, $allowed_years, true)) {
    $year = "";
}

// 2. Build the WHERE part of the query
$where = [];
$params = [];

if ($search !== "") {
    $where[] = "(name LIKE :s1 OR email LIKE :s2 OR student_id LIKE :s3 OR course LIKE :s4)";
    $like = "%" . $search . "%";
    $params[":s1"] = $like;
    $params[":s2"] = $like;
    $params[":s3"] = $like;
    $params[":s4"] = $like;
}

if ($course !== "") {
    $where[] = "course = :course";
    $params[":course"] = $course;
}

if ($year !== "") {
    $where[] = "year = :year";
    $params[":year"] = (int)$year;
}

$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// 3. Count matching rows (needed for pagination)
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM students $where_sql");
$count_stmt->execute($params);
$total = (int)$count_stmt->fetchColumn();

$total_pages = max(1, (int)ceil($total / $per_page));
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $per_page;

// 4. Get only the rows for the current page
$stmt = $conn->prepare(
    "SELECT id, student_id, name, email, mobile, course, year, gender, role
     FROM students
     $where_sql
     ORDER BY id DESC
     LIMIT :limit OFFSET :offset"
);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(":limit", $per_page, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->execute();
$students = $stmt->fetchAll();

// 5. Success / failure messages (only known keys are shown)
$messages = [
    "added" => "Student added successfully.",
    "updated" => "Student updated successfully.",
    "deleted" => "Student deleted successfully.",
    "notfound" => "Student not found.",
    "blocked" => "You cannot delete an administrator or your own account."
];
$msg = $messages[$_GET["msg"] ?? ""] ?? "";

// Builds a pagination link that keeps the search and filters
function page_link($page)
{
    global $search, $course, $year;

    return "admin_students.php?" . http_build_query([
        "search" => $search,
        "course" => $course,
        "year" => $year,
        "page" => $page
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Manage Students</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Student Management</h1>
        <p>Add, edit, search and delete student records</p>
    </header>

    <nav aria-label="Main navigation">
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="events.html">Events</a></li>
            <li><a href="admin.php">Admin</a></li>
            <li><a href="admin_students.php" aria-current="page">Manage Students</a></li>
            <li><a href="../php/logout.php">Logout</a></li>
        </ul>
    </nav>

    <main>
        <section>
            <h2>Students</h2>

            <?php if ($msg !== ""): ?>
                <p class="success" role="status"><?= h($msg) ?></p>
            <?php endif; ?>

            <form method="get" action="admin_students.php" class="filter-form">
                <div>
                    <label for="search">Search (name, email, ID, course):</label>
                    <input type="text" id="search" name="search" value="<?= h($search) ?>">
                </div>

                <div>
                    <label for="course">Course:</label>
                    <select id="course" name="course">
                        <option value="">All</option>
                        <?php foreach ($allowed_courses as $c): ?>
                            <option value="<?= $c ?>" <?= $course === $c ? "selected" : "" ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="year">Year:</label>
                    <select id="year" name="year">
                        <option value="">All</option>
                        <?php foreach ($allowed_years as $y): ?>
                            <option value="<?= $y ?>" <?= $year === $y ? "selected" : "" ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit">Search</button>
                <a href="admin_students.php">Reset</a>
            </form>

            <p><a href="student_form.php">+ Add New Student</a></p>
            <p>Found <?= $total ?> student(s). Page <?= $page ?> of <?= $total_pages ?>.</p>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Gender</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr><td colspan="10">No students found.</td></tr>
                        <?php endif; ?>

                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td><?= (int)$s["id"] ?></td>
                                <td><?= h($s["student_id"]) ?></td>
                                <td><?= h($s["name"]) ?></td>
                                <td><?= h($s["email"]) ?></td>
                                <td><?= h($s["mobile"]) ?></td>
                                <td><?= h($s["course"]) ?></td>
                                <td><?= h($s["year"]) ?></td>
                                <td><?= h($s["gender"]) ?></td>
                                <td><?= h($s["role"]) ?></td>
                                <td>
                                    <a href="student_form.php?id=<?= (int)$s["id"] ?>">Edit</a>

                                    <form method="post" action="../php/student_delete.php"
                                          class="inline-form"
                                          onsubmit="return confirm('Delete this student permanently?');">
                                        <input type="hidden" name="id" value="<?= (int)$s["id"] ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <p class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= h(page_link($page - 1)) ?>">&laquo; Previous</a>
                    <?php endif; ?>

                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <?php if ($p === $page): ?>
                            <strong><?= $p ?></strong>
                        <?php else: ?>
                            <a href="<?= h(page_link($p)) ?>"><?= $p ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="<?= h(page_link($page + 1)) ?>">Next &raquo;</a>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 StudentHub</p>
    </footer>
</body>
</html>