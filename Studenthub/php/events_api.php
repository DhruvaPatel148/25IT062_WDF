
<?php
header("Content-Type: application/json");

require_once "auth.php";
require_role("admin");
require_once "db.php";

$method = $_SERVER["REQUEST_METHOD"];

function sendResponse($success, $message, $data = null) {
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

if ($method == "GET") {
    try {
        $stmt = $conn->query("SELECT * FROM events ORDER BY id DESC");
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(true, "Events fetched successfully", $events);
    } catch (PDOException $e) {
        http_response_code(500);
        sendResponse(false, "Unable to fetch events");
    }
}

if ($method == "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!is_array($input)) {
        http_response_code(400);
        sendResponse(false, "Invalid JSON data");
    }

    $title = trim($input["title"] ?? "");
    $category = trim($input["category"] ?? "General");
    $description = trim($input["description"] ?? "");
    $event_date = $input["event_date"] ?? "";
    $venue = trim($input["venue"] ?? "");

    if ($title == "" || $event_date == "" || $venue == "") {
        http_response_code(400);
        sendResponse(false, "Title, date and venue are required");
    }

    $date = DateTime::createFromFormat("!Y-m-d", $event_date);

    if (!$date || $date->format("Y-m-d") != $event_date) {
        http_response_code(400);
        sendResponse(false, "Enter a valid event date");
    }

    try {
        $stmt = $conn->prepare(
            "INSERT INTO events
            (title, category, description, event_date, venue, status)
            VALUES (?, ?, ?, ?, ?, 'open')"
        );

        $stmt->execute([
            $title,
            $category,
            $description,
            $event_date,
            $venue
        ]);

        sendResponse(true, "Event added successfully", [
            "id" => $conn->lastInsertId()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        sendResponse(false, "Unable to add event");
    }
}

if ($method == "PUT") {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!is_array($input)) {
        http_response_code(400);
        sendResponse(false, "Invalid JSON data");
    }

    $id = filter_var($input["id"] ?? null, FILTER_VALIDATE_INT);
    $title = trim($input["title"] ?? "");
    $category = trim($input["category"] ?? "General");
    $description = trim($input["description"] ?? "");
    $event_date = $input["event_date"] ?? "";
    $venue = trim($input["venue"] ?? "");
    $status = $input["status"] ?? "open";

    if (!$id || $title == "" || $event_date == "" || $venue == "") {
        http_response_code(400);
        sendResponse(false, "Enter valid event details");
    }

    $date = DateTime::createFromFormat("!Y-m-d", $event_date);

    if (!$date || $date->format("Y-m-d") != $event_date ||
        !in_array($status, ["open", "closed"])) {
        http_response_code(400);
        sendResponse(false, "Invalid date or status");
    }

    try {
        $stmt = $conn->prepare(
            "UPDATE events SET title=?, category=?, description=?,
            event_date=?, venue=?, status=? WHERE id=?"
        );

        $stmt->execute([
            $title,
            $category,
            $description,
            $event_date,
            $venue,
            $status,
            $id
        ]);

        if ($stmt->rowCount() == 0) {
            $check = $conn->prepare("SELECT id FROM events WHERE id=?");
            $check->execute([$id]);

            if (!$check->fetch()) {
                http_response_code(404);
                sendResponse(false, "Event not found");
            }
        }

        sendResponse(true, "Event updated successfully");
    } catch (PDOException $e) {
        http_response_code(500);
        sendResponse(false, "Unable to update event");
    }
}

if ($method == "DELETE") {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!is_array($input)) {
        http_response_code(400);
        sendResponse(false, "Invalid JSON data");
    }

    $id = filter_var($input["id"] ?? null, FILTER_VALIDATE_INT);

    if (!$id) {
        http_response_code(400);
        sendResponse(false, "Valid event ID is required");
    }

    try {
        $stmt = $conn->prepare("DELETE FROM events WHERE id=?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() == 0) {
            http_response_code(404);
            sendResponse(false, "Event not found");
        }

        sendResponse(true, "Event deleted successfully");
    } catch (PDOException $e) {
        http_response_code(500);
        sendResponse(false, "Unable to delete event");
    }
}

http_response_code(405);
header("Allow: GET, POST, PUT, DELETE");
sendResponse(false, "Method not allowed");
?>
