
let allFAQs = [];
let currentPage = 1;
let perPage = 6;

async function loadData() {
    try {
        const response = await fetch("../data/faqs.json");

        if (!response.ok) {
            throw new Error("Unable to load FAQs");
        }

        allFAQs = await response.json();
        update(allFAQs);
    }
    catch (error) {
        console.log("Error:", error);
        document.getElementById("status").textContent =
            "Error loading FAQs.";
    }
}

function update(faqs) {
    let search = document.getElementById("search").value.toLowerCase();
    let category = document.getElementById("category").value;

    let filteredFAQs = [];

    for (let i = 0; i < faqs.length; i++) {
        if (
            (
                faqs[i].question.toLowerCase().includes(search) ||
                faqs[i].answer.toLowerCase().includes(search)
            ) &&
            (category === "all" || faqs[i].category === category)
        ) {
            filteredFAQs.push(faqs[i]);
        }
    }

    let totalPages = Math.ceil(filteredFAQs.length / perPage);

    if (totalPages === 0) {
        document.getElementById("faq-list").innerHTML = "";
        document.getElementById("status").textContent = "No FAQs found.";
        document.getElementById("page-number").textContent = "Page 0";
        document.getElementById("prev").disabled = true;
        document.getElementById("next").disabled = true;
        return;
    }

    if (currentPage > totalPages) {
        currentPage = totalPages;
    }

    let start = (currentPage - 1) * perPage;
    let faqList = [];

    
for (
    let i = start;
    i < start + perPage && i < filteredFAQs.length;
    i++
) {
    faqList.push(
        "<div class='event-card'>" +
        "<details>" +
        "<summary>" + filteredFAQs[i].question + "</summary>" +
        "<p>" + filteredFAQs[i].answer + "</p>" +
        "<p><strong>Category:</strong> " +
        filteredFAQs[i].category + "</p>" +
        "</details>" +
        "</div>"
    );
}


    document.getElementById("faq-list").innerHTML = faqList.join("");
    document.getElementById("status").textContent = "";

    document.getElementById("page-number").textContent =
        "Page " + currentPage + " of " + totalPages;

    document.getElementById("prev").disabled = currentPage === 1;
    document.getElementById("next").disabled = currentPage === totalPages;
}

document.getElementById("search").addEventListener("input", function() {
    currentPage = 1;
    update(allFAQs);
});

document.getElementById("category").addEventListener("change", function() {
    currentPage = 1;
    update(allFAQs);
});

document.getElementById("prev").addEventListener("click", function() {
    if (currentPage > 1) {
        currentPage--;
        update(allFAQs);
    }
});

document.getElementById("next").addEventListener("click", function() {
    currentPage++;
    update(allFAQs);
});

loadData();
