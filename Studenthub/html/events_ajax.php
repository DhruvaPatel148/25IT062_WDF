
<?php
require_once "../php/auth.php";
require_role("admin");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - AJAX Event Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .ajax-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
        }

        .ajax-form {
            display: grid;
            gap: 12px;
            margin-bottom: 25px;
        }

        .ajax-form label {
            display: block;
            margin-bottom: 5px;
        }

        .ajax-form input,
        .ajax-form textarea,
        .ajax-form select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }

        .event-card {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 15px;
            margin: 12px 0;
        }

        .event-card button {
            margin: 5px;
            padding: 8px 12px;
            cursor: pointer;
        }

        #message {
            margin: 15px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <main class="ajax-container">
        <h1>Event Management - Fetch API</h1>
        <p>Add, view, update and delete events without reloading the page.</p>

        <p id="message" role="status" aria-live="polite"></p>

        <h2 id="form-heading">Add New Event</h2>

        <form id="eventForm" class="ajax-form">
            <input type="hidden" id="eventId">

            <div>
                <label for="title">Event Title *</label>
                <input type="text" id="title" required maxlength="150">
            </div>

            <div>
                <label for="category">Category</label>
                <input type="text" id="category" value="General" maxlength="50">
            </div>

            <div>
                <label for="description">Description</label>
                <textarea id="description" rows="3"></textarea>
            </div>

            <div>
                <label for="event_date">Event Date *</label>
                <input type="date" id="event_date" required>
            </div>

            <div>
                <label for="venue">Venue *</label>
                <input type="text" id="venue" required maxlength="150">
            </div>

            <div id="statusField" hidden>
                <label for="status">Status</label>
                <select id="status">
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                </select>
            </div>

            <div>
                <button type="submit" id="saveButton">Add Event</button>
                <button type="button" id="cancelButton" hidden>Cancel Edit</button>
            </div>
        </form>

        <h2>All Events</h2>
        <div id="eventsList">Loading events...</div>
    </main>

    <script src="../js/events_ajax.js"></script>
</body>
</html>
