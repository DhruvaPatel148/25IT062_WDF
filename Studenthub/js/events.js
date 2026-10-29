
let allEvents = [];
let currentPage = 1;
let perPage = 6;

async function loadData() {
    try {
        const response = await fetch("../data/events.json");

        if (!response.ok) {
            throw new Error("Unable to load events");
        }

        allEvents = await response.json();
        update(allEvents);
    }
    catch (error) {
        console.log("Error:", error);
        document.getElementById("status").textContent =
            "Error loading events.";
    }
}

function update(events) {
    let search = document.getElementById("search").value.toLowerCase();
    let category = document.getElementById("category").value;
    let sort = document.getElementById("sort").value;

    let filteredEvents = [];

    // Search and filter
    for (let i = 0; i < events.length; i++) {
        if (
            events[i].title.toLowerCase().includes(search) &&
            (category === "all" || events[i].category === category)
        ) {
            filteredEvents.push(events[i]);
        }
    }

    // Sorting
    if (sort === "title") {
        filteredEvents.sort(function(a, b) {
            return a.title.localeCompare(b.title);
        });
    } else {
        filteredEvents.sort(function(a, b) {
            return new Date(a.date) - new Date(b.date);
        });
    }

    let totalPages = Math.ceil(filteredEvents.length / perPage);

    if (totalPages === 0) {
        document.getElementById("event-list").innerHTML = "";
        document.getElementById("status").textContent = "No events found.";
        document.getElementById("page-number").textContent = "Page 0";
        document.getElementById("prev").disabled = true;
        document.getElementById("next").disabled = true;
        return;
    }

    if (currentPage > totalPages) {
        currentPage = totalPages;
    }

    // Pagination
    let start = (currentPage - 1) * perPage;
    let eventList = [];

    for (let i = start; i < start + perPage && i < filteredEvents.length; i++) {
        eventList.push(
            "<div class='event-card'>" +
            "<h3>" + filteredEvents[i].title + "</h3>" +
            "<p>Category: " + filteredEvents[i].category + "</p>" +
            "<p>Date: " + filteredEvents[i].date + "</p>" +
            "<p>Venue: " + filteredEvents[i].venue + "</p>" +
            "</div>"
        );
    }

    document.getElementById("event-list").innerHTML = eventList.join("");
    document.getElementById("status").textContent = "";
    document.getElementById("page-number").textContent =
        "Page " + currentPage + " of " + totalPages;

    document.getElementById("prev").disabled = currentPage === 1;
    document.getElementById("next").disabled = currentPage === totalPages;
}

// Search
document.getElementById("search").addEventListener("input", function() {
    currentPage = 1;
    update(allEvents);
});

// Category filter
document.getElementById("category").addEventListener("change", function() {
    currentPage = 1;
    update(allEvents);
});

// Sorting
document.getElementById("sort").addEventListener("change", function() {
    currentPage = 1;
    update(allEvents);
});

// Previous page
document.getElementById("prev").addEventListener("click", function() {
    if (currentPage > 1) {
        currentPage--;
        update(allEvents);
    }
});

// Next page
document.getElementById("next").addEventListener("click", function() {
    currentPage++;
    update(allEvents);
});

loadData();
