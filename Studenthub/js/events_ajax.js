
const apiUrl = "../php/events_api.php";

const eventForm = document.getElementById("eventForm");
const eventsList = document.getElementById("eventsList");
const message = document.getElementById("message");
const eventId = document.getElementById("eventId");
const formHeading = document.getElementById("form-heading");
const saveButton = document.getElementById("saveButton");
const cancelButton = document.getElementById("cancelButton");
const statusField = document.getElementById("statusField");

function showMessage(text) {
    message.textContent = text;
}

function resetForm() {
    eventForm.reset();
    eventId.value = "";
    document.getElementById("category").value = "General";
    formHeading.textContent = "Add New Event";
    saveButton.textContent = "Add Event";
    cancelButton.hidden = true;
    statusField.hidden = true;
}

async function loadEvents() {
    eventsList.textContent = "Loading events...";

    try {
        const response = await fetch(apiUrl);
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || "Unable to load events");
        }

        eventsList.replaceChildren();

        if (result.data.length === 0) {
            eventsList.textContent = "No events found.";
            return;
        }

        result.data.forEach(function(event) {
            const card = document.createElement("div");
            card.className = "event-card";

            const title = document.createElement("h3");
            title.textContent = event.title;

            const category = document.createElement("p");
            category.textContent = "Category: " + event.category;

            const description = document.createElement("p");
            description.textContent = "Description: " + event.description;

            const date = document.createElement("p");
            date.textContent = "Date: " + event.event_date;

            const venue = document.createElement("p");
            venue.textContent = "Venue: " + event.venue;

            const status = document.createElement("p");
            status.textContent = "Status: " + event.status;

            const editButton = document.createElement("button");
            editButton.textContent = "Edit";
            editButton.type = "button";
            editButton.addEventListener("click", function() {
                editEvent(event);
            });

            const deleteButton = document.createElement("button");
            deleteButton.textContent = "Delete";
            deleteButton.type = "button";
            deleteButton.addEventListener("click", function() {
                deleteEvent(event.id);
            });

            card.append(
                title,
                category,
                description,
                date,
                venue,
                status,
                editButton,
                deleteButton
            );

            eventsList.appendChild(card);
        });
    } catch (error) {
        eventsList.textContent = "Error loading events.";
        showMessage(error.message);
    }
}

function editEvent(event) {
    eventId.value = event.id;
    document.getElementById("title").value = event.title;
    document.getElementById("category").value = event.category || "General";
    document.getElementById("description").value = event.description || "";
    document.getElementById("event_date").value = event.event_date;
    document.getElementById("venue").value = event.venue;
    document.getElementById("status").value = event.status;

    formHeading.textContent = "Update Event";
    saveButton.textContent = "Update Event";
    cancelButton.hidden = false;
    statusField.hidden = false;

    showMessage("Editing event: " + event.title);
    window.scrollTo(0, 0);
}

eventForm.addEventListener("submit", async function(event) {
    event.preventDefault();

    const id = eventId.value;

    const eventData = {
        title: document.getElementById("title").value.trim(),
        category: document.getElementById("category").value.trim() || "General",
        description: document.getElementById("description").value.trim(),
        event_date: document.getElementById("event_date").value,
        venue: document.getElementById("venue").value.trim(),
        status: document.getElementById("status").value
    };

    const method = id ? "PUT" : "POST";

    if (id) {
        eventData.id = id;
    }

    saveButton.disabled = true;
    showMessage("Saving event...");

    try {
        const response = await fetch(apiUrl, {
            method: method,
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(eventData)
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || "Unable to save event");
        }

        showMessage(result.message);
        resetForm();
        await loadEvents();
    } catch (error) {
        showMessage(error.message);
    } finally {
        saveButton.disabled = false;
    }
});

async function deleteEvent(id) {
    if (!confirm("Are you sure you want to delete this event?")) {
        return;
    }

    showMessage("Deleting event...");

    try {
        const response = await fetch(apiUrl, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id: id })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || "Unable to delete event");
        }

        showMessage(result.message);
        await loadEvents();
    } catch (error) {
        showMessage(error.message);
    }
}

cancelButton.addEventListener("click", function() {
    resetForm();
    showMessage("Editing cancelled.");
});

loadEvents();
