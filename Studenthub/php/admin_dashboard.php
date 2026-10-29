
<?php
require_once "auth.php";
require_role("admin");
require_once "db.php";

// Count total students
$stmt = $conn->query("SELECT COUNT(*) FROM students");
$total_students = $stmt->fetchColumn();

// Count total events
$stmt = $conn->query("SELECT COUNT(*) FROM events");
$total_events = $stmt->fetchColumn();

// Count total registrations
$stmt = $conn->query("SELECT COUNT(*) FROM registrations");
$total_registrations = $stmt->fetchColumn();

// Get latest registrations
$sql = "SELECT registrations.id, students.name, events.title,
               registrations.registered_at
        FROM registrations
        JOIN students ON registrations.student_id = students.id
        JOIN events ON registrations.event_id = events.id
        ORDER BY registrations.registered_at DESC
        LIMIT 5";

$stmt = $conn->query($sql);
$latest_registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent audit logs
$stmt = $conn->query(
    "SELECT audit_logs.*, students.name
     FROM audit_logs
     LEFT JOIN students ON audit_logs.user_id = students.id
     ORDER BY audit_logs.created_at DESC
     LIMIT 10"
);

$audit_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Record dashboard access
$log = $conn->prepare(
    "INSERT INTO audit_logs (user_id, action, details)
     VALUES (?, ?, ?)"
);

$student_code = $_SESSION['student_id'] ?? '';

$stmt = $conn->prepare(
    "SELECT id FROM students WHERE student_id = ?"
);
$stmt->execute([$student_code]);
$user_id = $stmt->fetchColumn();

$log->execute([
    $user_id ?: null,
    "Dashboard Access",
    "Admin opened the dashboard"
]);

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StudentHub</title>
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .dashboard {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }

        .stat-card {
            padding: 25px;
            border-radius: 10px;
            background: #f0f4ff;
            text-align: center;
        }

        .stat-card h2 {
            margin: 10px 0;
            font-size: 30px;
        }

        .dashboard table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .dashboard th,
        .dashboard td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .dashboard th {
            background: #e8ecf8;
        }

        .menu {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .menu a {
            text-decoration: none;
            padding: 10px 15px;
            background: #e8ecf8;
            border-radius: 5px;
            color: #222;
        }

        .table-container {
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <h1>Admin Dashboard</h1>
        <p>Welcome to the StudentHub administration panel.</p>

        <nav class="menu">
    <a href="../html/index.html">Home</a>

    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <a href="admin_dashboard.php">Admin Dashboard</a>
        <a href="event_management.php">Manage Events</a>
        <a href="../html/events_ajax.php">AJAX Events</a>
    <?php else: ?>
        <a href="../html/dashboard.php">My Dashboard</a>
        <a href="../html/events.php">Events</a>
    <?php endif; ?>
</nav>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Students</h3>
                <h2><?= htmlspecialchars((string)$total_students) ?></h2>
            </div>

            <div class="stat-card">
                <h3>Total Events</h3>
                <h2><?= htmlspecialchars((string)$total_events) ?></h2>
            </div>

            <div class="stat-card">
                <h3>Total Registrations</h3>
                <h2><?= htmlspecialchars((string)$total_registrations) ?></h2>
            </div>
        </div>

        <h2>Latest Registrations</h2>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Event</th>
                        <th>Registered At</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($latest_registrations) > 0): ?>
                        <?php foreach ($latest_registrations as $registration): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)$registration['id']) ?></td>
                                <td><?= htmlspecialchars($registration['name']) ?></td>
                                <td><?= htmlspecialchars($registration['title']) ?></td>
                                <td><?= htmlspecialchars($registration['registered_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No registrations found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
<h2>Recent Audit Logs</h2>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Admin</th>
                <th>Action</th>
                <th>Details</th>
                <th>Date and Time</th>
            </tr>
        </thead>

        <tbody>
            <?php if (count($audit_logs) > 0): ?>
                <?php foreach ($audit_logs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars((string)$log['id']) ?></td>
                        <td><?= htmlspecialchars($log['name'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($log['action']) ?></td>
                        <td><?= htmlspecialchars($log['details'] ?? '') ?></td>
                        <td><?= htmlspecialchars($log['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No audit logs found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

        <h2>Role-Based Access</h2>
        <p>Only users with the admin role can access this dashboard.</p>
    </div>
</body>
</html>
