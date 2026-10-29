
<?php
require_once "auth.php";
require_role("admin");

require_once "db.php";

$message = "";

// Add Event
if (isset($_POST["add_event"])) {
    $title = trim($_POST["title"]);
    $category = trim($_POST["category"]);
    $description = trim($_POST["description"]);
    $event_date = $_POST["event_date"];
    $venue = trim($_POST["venue"]);
    $status = $_POST["status"];
    $poster = null;

    if (
        $title == "" || $category == "" ||
        $description == "" || $event_date == "" ||
        $venue == ""
    ) {
        $message = "Please fill all required fields.";
    } elseif (!in_array($status, ["open", "closed"])) {
        $message = "Invalid event status.";
    } else {
        // Validate poster
        if (
            isset($_FILES["poster"]) &&
            $_FILES["poster"]["error"] != UPLOAD_ERR_NO_FILE
        ) {
            $file = $_FILES["poster"];

            if ($file["error"] != UPLOAD_ERR_OK) {
                $message = "Poster upload failed.";
            } elseif ($file["size"] > 2 * 1024 * 1024) {
                $message = "Poster must be smaller than 2 MB.";
            } else {
                $allowed = [
                    "image/jpeg" => "jpg",
                    "image/png" => "png",
                    "image/webp" => "webp"
                ];

                $mime = mime_content_type($file["tmp_name"]);

                if (!isset($allowed[$mime])) {
                    $message = "Only JPG, PNG and WEBP posters are allowed.";
                } else {
                    $folder = __DIR__ . "/../uploads/event_posters/";

                    if (!is_dir($folder)) {
                        mkdir($folder, 0755, true);
                    }

                    $filename = uniqid("poster_", true)
                        . "." . $allowed[$mime];

                    if (move_uploaded_file(
                        $file["tmp_name"],
                        $folder . $filename
                    )) {
                        $poster = $filename;
                    } else {
                        $message = "Could not save the poster.";
                    }
                }
            }
        }

        if ($message == "") {
            $sql = "INSERT INTO events
                    (title, category, description, event_date,
                     venue, poster, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $title, $category, $description,
                $event_date, $venue, $poster, $status
            ]);

            $message = "Event added successfully!";
        }
    }
}

// Edit Event
if (isset($_POST["update_event"])) {
    $id = (int) $_POST["id"];
    $title = trim($_POST["title"]);
    $category = trim($_POST["category"]);
    $description = trim($_POST["description"]);
    $event_date = $_POST["event_date"];
    $venue = trim($_POST["venue"]);
    $status = $_POST["status"];

    if (
        $title == "" || $category == "" ||
        $description == "" || $event_date == "" ||
        $venue == ""
    ) {
        $message = "Please fill all required fields.";
    } elseif (!in_array($status, ["open", "closed"])) {
        $message = "Invalid event status.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE events
             SET title=?, category=?, description=?,
                 event_date=?, venue=?, status=?
             WHERE id=?"
        );

        $stmt->execute([
            $title, $category, $description,
            $event_date, $venue, $status, $id
        ]);

        $message = "Event update request processed.";
    }
}

// Delete Event
if (isset($_POST["delete_event"])) {
    $id = (int) $_POST["id"];

    // Check related registrations
    $check = $conn->prepare(
        "SELECT COUNT(*) FROM registrations WHERE event_id = ?"
    );
    $check->execute([$id]);

    if ($check->fetchColumn() > 0) {
        $message = "This event has registrations and cannot be deleted.";
    } else {
        $stmt = $conn->prepare(
            "SELECT poster FROM events WHERE id = ?"
        );
        $stmt->execute([$id]);
        $oldPoster = $stmt->fetchColumn();

        $stmt = $conn->prepare(
            "DELETE FROM events WHERE id = ?"
        );
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            if ($oldPoster) {
                $path = __DIR__ . "/../uploads/event_posters/"
                    . basename($oldPoster);

                if (is_file($path)) {
                    unlink($path);
                }
            }

            $message = "Event deleted successfully!";
        } else {
            $message = "Event not found.";
        }
    }
}

// Category and Status Filters
$categoryFilter = trim($_GET["category"] ?? "");
$statusFilter = $_GET["status"] ?? "";

$sql = "SELECT * FROM events WHERE 1=1";
$params = [];

if ($categoryFilter != "") {
    $sql .= " AND category = ?";
    $params[] = $categoryFilter;
}

if (in_array($statusFilter, ["open", "closed"])) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY event_date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories
$stmt = $conn->query(
    "SELECT DISTINCT category FROM events ORDER BY category"
);
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Management</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 25px;
            background: #f4f6f9;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        h1 {
            color: #27364b;
        }

        h2 {
            margin-top: 30px;
        }

        a {
            color: #245ca6;
        }

        form,
        .event {
            background: white;
            padding: 20px;
            margin-bottom: 22px;
            border-radius: 8px;
            box-shadow: 0 2px 6px #00000010;
        }

        label {
            display: block;
            font-weight: bold;
            margin: 12px 0 6px;
        }

        input,
        textarea,
        select {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 6px 0 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 15px;
            background: white;
        }

        textarea {
            min-height: 80px;
            resize: vertical;
        }

        button {
            display: block;
            width: 100%;
            padding: 11px 16px;
            background: #27364b;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 15px;
            margin-top: 12px;
        }

        button:hover {
            background: #405775;
        }

        .message {
            padding: 12px;
            background: #e7f5e9;
            color: #176b35;
            border-radius: 5px;
        }

        .event {
            border: 1px solid #ddd;
        }

        .event img {
            max-width: 220px;
            max-height: 250px;
            display: block;
            margin: 12px 0;
            object-fit: contain;
        }

        .edit-form {
            border: 1px solid #ddd;
            margin-top: 15px;
            box-shadow: none;
        }

        .delete-form {
            padding: 0;
            margin: 10px 0 0;
            box-shadow: none;
        }

        .delete-button {
            background: #b52b35;
        }

        .delete-button:hover {
            background: #8e1e27;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: end;
        }

        .filter-form .filter-field {
            flex: 1;
            min-width: 180px;
        }

        .filter-form button {
            margin-bottom: 14px;
        }

        .clear-link {
            display: inline-block;
            margin: 10px 0;
        }

        @media (max-width: 600px) {
            body {
                padding: 12px;
            }

            form,
            .event {
                padding: 14px;
            }
        }
    </style>
</head>

<body>
<div class="container">

    <h1>Event Management</h1>

    <p>
        <a href="../html/admin.php">Back to Admin Dashboard</a>
    </p>

    <?php if ($message != ""): ?>
        <p class="message">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <h2>Add New Event</h2>

    <form method="POST" enctype="multipart/form-data">
        <label for="title">Event Title</label>
        <input type="text" id="title" name="title" required>

        <label for="category">Category</label>
        <input type="text" id="category" name="category" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" required></textarea>

        <label for="event_date">Event Date</label>
        <input type="date" id="event_date" name="event_date" required>

        <label for="venue">Venue</label>
        <input type="text" id="venue" name="venue" required>

        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="open">Open</option>
            <option value="closed">Closed</option>
        </select>

        <label for="poster">
            Poster (JPG, PNG or WEBP; maximum 2 MB)
        </label>
        <input type="file" id="poster" name="poster"
               accept=".jpg,.jpeg,.png,.webp">

        <button type="submit" name="add_event">Add Event</button>
    </form>

    <h2>Filter Events</h2>

    <form method="GET" class="filter-form">
        <div class="filter-field">
            <label for="filter_category">Category</label>
            <select id="filter_category" name="category">
                <option value="">All Categories</option>

                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category) ?>"
                        <?= $categoryFilter == $category ? "selected" : "" ?>>
                        <?= htmlspecialchars($category) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-field">
            <label for="filter_status">Status</label>
            <select id="filter_status" name="status">
                <option value="">All Statuses</option>
                <option value="open"
                    <?= $statusFilter == "open" ? "selected" : "" ?>>
                    Open
                </option>
                <option value="closed"
                    <?= $statusFilter == "closed" ? "selected" : "" ?>>
                    Closed
                </option>
            </select>
        </div>

        <div class="filter-field">
            <button type="submit">Apply Filters</button>
        </div>
    </form>

    <a class="clear-link" href="event_management.php">
        Clear Filters
    </a>

    <h2>Existing Events</h2>

    <?php if (count($events) == 0): ?>
        <p>No events found.</p>
    <?php endif; ?>

    <?php foreach ($events as $event): ?>
        <div class="event">

            <h3><?= htmlspecialchars($event["title"]) ?></h3>

            <p><b>Category:</b>
                <?= htmlspecialchars($event["category"]) ?></p>

            <p>
                <?= nl2br(htmlspecialchars($event["description"])) ?>
            </p>

            <p><b>Date:</b>
                <?= htmlspecialchars($event["event_date"]) ?></p>

            <p><b>Venue:</b>
                <?= htmlspecialchars($event["venue"]) ?></p>

            <p><b>Status:</b>
                <?= htmlspecialchars($event["status"]) ?></p>

            <?php if (!empty($event["poster"])): ?>
                <img
                    src="../uploads/event_posters/<?= rawurlencode(basename($event["poster"])) ?>"
                    alt="Event poster">
            <?php endif; ?>

            <h4>Edit This Event</h4>

            <form method="POST" class="edit-form">
                <input type="hidden" name="id"
                       value="<?= (int) $event["id"] ?>">

                <label>Event Title</label>
                <input type="text" name="title"
                       value="<?= htmlspecialchars($event["title"]) ?>"
                       required>

                <label>Category</label>
                <input type="text" name="category"
                       value="<?= htmlspecialchars($event["category"]) ?>"
                       required>

                <label>Description</label>
                <textarea name="description" required><?= htmlspecialchars($event["description"]) ?></textarea>

                <label>Event Date</label>
                <input type="date" name="event_date"
                       value="<?= htmlspecialchars($event["event_date"]) ?>"
                       required>

                <label>Venue</label>
                <input type="text" name="venue"
                       value="<?= htmlspecialchars($event["venue"]) ?>"
                       required>

                <label>Status</label>
                <select name="status">
                    <option value="open"
                        <?= $event["status"] == "open" ? "selected" : "" ?>>
                        Open
                    </option>
                    <option value="closed"
                        <?= $event["status"] == "closed" ? "selected" : "" ?>>
                        Closed
                    </option>
                </select>

                <button type="submit" name="update_event">
                    Update Event
                </button>
            </form>

            <form method="POST" class="delete-form"
                  onsubmit="return confirm('Are you sure you want to delete this event?')">

                <input type="hidden" name="id"
                       value="<?= (int) $event["id"] ?>">

                <button type="submit" name="delete_event"
                        class="delete-button">
                    Delete Event
                </button>
            </form>

        </div>
    <?php endforeach; ?>

</div>
</body>
</html>
