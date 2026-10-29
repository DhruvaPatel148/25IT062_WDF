
let allStudents = [];
let currentPage = 1;
let perPage = 6;

async function loadData() {
    try {
        const response = await fetch("../data/students.json");

        if (!response.ok) {
            throw new Error("Unable to load students");
        }

        allStudents = await response.json();
        update(allStudents);
    }
    catch (error) {
        console.log("Error:", error);
        document.getElementById("status").textContent =
            "Error loading students.";
    }
}

function update(students) {
    let search = document.getElementById("search").value.toLowerCase();
    let course = document.getElementById("course").value;
    let sort = document.getElementById("sort").value;

    let filteredStudents = [];

    for (let i = 0; i < students.length; i++) {
        if (
            students[i].name.toLowerCase().includes(search) &&
            (course === "all" || students[i].course === course)
        ) {
            filteredStudents.push(students[i]);
        }
    }

    if (sort === "year") {
        filteredStudents.sort(function(a, b) {
            return a.year - b.year;
        });
    } else {
        filteredStudents.sort(function(a, b) {
            return a.name.localeCompare(b.name);
        });
    }

    let totalPages = Math.ceil(filteredStudents.length / perPage);

    if (totalPages === 0) {
        document.getElementById("student-list").innerHTML = "";
        document.getElementById("status").textContent = "No students found.";
        document.getElementById("page-number").textContent = "Page 0";
        document.getElementById("prev").disabled = true;
        document.getElementById("next").disabled = true;
        return;
    }

    if (currentPage > totalPages) {
        currentPage = totalPages;
    }

    let start = (currentPage - 1) * perPage;
    let studentList = [];

    for (
        let i = start;
        i < start + perPage && i < filteredStudents.length;
        i++
    ) {
        studentList.push(
            "<div class='event-card'>" +
            "<h3>" + filteredStudents[i].name + "</h3>" +
            "<p>Enrollment: " + filteredStudents[i].enrollment + "</p>" +
            "<p>Course: " + filteredStudents[i].course + "</p>" +
            "<p>Year: " + filteredStudents[i].year + "</p>" +
            "</div>"
        );
    }

    document.getElementById("student-list").innerHTML =
        studentList.join("");

    document.getElementById("status").textContent = "";
    document.getElementById("page-number").textContent =
        "Page " + currentPage + " of " + totalPages;

    document.getElementById("prev").disabled = currentPage === 1;
    document.getElementById("next").disabled = currentPage === totalPages;
}

document.getElementById("search").addEventListener("input", function() {
    currentPage = 1;
    update(allStudents);
});

document.getElementById("course").addEventListener("change", function() {
    currentPage = 1;
    update(allStudents);
});

document.getElementById("sort").addEventListener("change", function() {
    currentPage = 1;
    update(allStudents);
});

document.getElementById("prev").addEventListener("click", function() {
    if (currentPage > 1) {
        currentPage--;
        update(allStudents);
    }
});

document.getElementById("next").addEventListener("click", function() {
    currentPage++;
    update(allStudents);
});

loadData();
