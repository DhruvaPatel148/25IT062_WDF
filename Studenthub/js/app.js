// Light and dark theme
const themeButton = document.createElement("button");
themeButton.id = "themeButton";
themeButton.textContent = "Switch to Dark Mode";
themeButton.setAttribute("aria-label", "Switch website theme");

document.body.insertBefore(themeButton, document.body.firstChild);

function applyTheme(theme) {
    if (theme === "dark") {
        document.body.classList.add("dark-mode");
        themeButton.textContent = "Switch to Light Mode";
    } else {
        document.body.classList.remove("dark-mode");
        themeButton.textContent = "Switch to Dark Mode";
    }
}

const savedTheme = localStorage.getItem("studentHubTheme") || "light";
applyTheme(savedTheme);

themeButton.addEventListener("click", function () {
    if (document.body.classList.contains("dark-mode")) {
        localStorage.setItem("studentHubTheme", "light");
        applyTheme("light");
    } else {
        localStorage.setItem("studentHubTheme", "dark");
        applyTheme("dark");
    }
});


// Notification banner - only on the home page
const currentPage = window.location.pathname;

if (currentPage.endsWith("/index.html") || currentPage.endsWith("/")) {
    const notification = document.createElement("div");
    notification.className = "notification";
    notification.textContent = "Welcome to StudentHub! ";

    const closeNotification = document.createElement("button");
    closeNotification.textContent = "Close";
    closeNotification.setAttribute("aria-label", "Close welcome notification");

    closeNotification.addEventListener("click", function () {
        notification.remove();
    });

    notification.appendChild(closeNotification);
    document.body.insertBefore(notification, document.body.children[1]);
}


// Modal popup
const modalButton = document.createElement("button");
modalButton.textContent = "Show Welcome Message";
modalButton.id = "modalButton";

const modalOverlay = document.createElement("div");
modalOverlay.id = "modalOverlay";
modalOverlay.style.display = "none";
modalOverlay.style.position = "fixed";
modalOverlay.style.top = "0";
modalOverlay.style.left = "0";
modalOverlay.style.width = "100%";
modalOverlay.style.height = "100%";
modalOverlay.style.backgroundColor = "rgba(0, 0, 0, 0.6)";
modalOverlay.style.zIndex = "1000";
modalOverlay.style.justifyContent = "center";
modalOverlay.style.alignItems = "center";
modalOverlay.style.padding = "20px";

const modal = document.createElement("div");
modal.className = "modal";
modal.style.maxWidth = "450px";
modal.style.width = "100%";
modal.style.padding = "25px";
modal.style.textAlign = "center";
modal.style.borderRadius = "10px";

const modalHeading = document.createElement("h2");
modalHeading.textContent = "Welcome to StudentHub!";

const modalMessage = document.createElement("p");
modalMessage.textContent = "Explore student information, campus events, and announcements.";

const closeModal = document.createElement("button");
closeModal.textContent = "Close Popup";

modal.appendChild(modalHeading);
modal.appendChild(modalMessage);
modal.appendChild(closeModal);
modalOverlay.appendChild(modal);
document.body.appendChild(modalOverlay);

modalButton.addEventListener("click", function () {
    modalOverlay.style.display = "flex";
    closeModal.focus();
});

closeModal.addEventListener("click", function () {
    modalOverlay.style.display = "none";
    modalButton.focus();
});

modalOverlay.addEventListener("click", function (event) {
    if (event.target === modalOverlay) {
        modalOverlay.style.display = "none";
        modalButton.focus();
    }
});

document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && modalOverlay.style.display === "flex") {
        modalOverlay.style.display = "none";
        modalButton.focus();
    }
});


// Content slider
const slider = document.createElement("section");
slider.className = "slider";

const sliderHeading = document.createElement("h2");
sliderHeading.textContent = "StudentHub Highlights";

const slideText = document.createElement("p");
slideText.id = "slideText";

const previousButton = document.createElement("button");
previousButton.textContent = "Previous";

const nextButton = document.createElement("button");
nextButton.textContent = "Next";

slider.appendChild(sliderHeading);
slider.appendChild(slideText);
slider.appendChild(previousButton);
slider.appendChild(nextButton);

document.body.appendChild(slider);

const slides = [
    "Discover campus events and activities.",
    "Connect with fellow students.",
    "Stay updated with StudentHub announcements."
];

let slideIndex = 0;

function showSlide() {
    slideText.textContent = slides[slideIndex];
}

nextButton.addEventListener("click", function () {
    slideIndex++;

    if (slideIndex >= slides.length) {
        slideIndex = 0;
    }

    showSlide();
});

previousButton.addEventListener("click", function () {
    slideIndex--;

    if (slideIndex < 0) {
        slideIndex = slides.length - 1;
    }

    showSlide();
});

showSlide();


// Add popup button and slider inside the main page area
const mainContent = document.querySelector("main");

if (mainContent) {
    mainContent.appendChild(modalButton);
    mainContent.appendChild(slider);
} else {
    document.body.appendChild(modalButton);
}


// Hamburger menu
const navigation = document.querySelector("nav");

if (navigation) {
    const menuButton = document.createElement("button");
    menuButton.textContent = "☰ Menu";
    menuButton.id = "menuButton";
    menuButton.setAttribute("aria-expanded", "false");
    menuButton.setAttribute("aria-controls", "mainNavigation");

    navigation.id = "mainNavigation";
    navigation.parentNode.insertBefore(menuButton, navigation);

    // Hide the menu button on larger screens using JavaScript
    function setMenuForScreen() {
        if (window.innerWidth <= 600) {
            menuButton.style.display = "block";
            navigation.style.display = "none";
            menuButton.setAttribute("aria-expanded", "false");
        } else {
            menuButton.style.display = "none";
            navigation.style.display = "block";
            menuButton.setAttribute("aria-expanded", "true");
        }
    }

    setMenuForScreen();
    window.addEventListener("resize", setMenuForScreen);

    menuButton.addEventListener("click", function () {
        const isOpen = menuButton.getAttribute("aria-expanded") === "true";

        menuButton.setAttribute("aria-expanded", String(!isOpen));

        if (isOpen) {
            navigation.style.display = "none";
        } else {
            navigation.style.display = "block";
        }
    });
}

// Collapsible FAQ - only on the FAQ page
const currentPath = window.location.pathname;

if (currentPath.endsWith("/faq.html")) {
    const faqSection = document.createElement("section");
    faqSection.id = "faqSection";

    const faqHeading = document.createElement("h2");
    faqHeading.textContent = "Frequently Asked Questions";

    faqSection.appendChild(faqHeading);

    const questions = [
        {
            question: "What is StudentHub?",
            answer: "StudentHub is a student portal that provides student information and campus updates."
        },
        {
            question: "How can I check campus events?",
            answer: "Visit the Events page to view campus activities and upcoming events."
        },
        {
            question: "How can I update my profile?",
            answer: "Visit the Profile page to view or update your student information."
        }
    ];

    questions.forEach(function (item) {
        const questionButton = document.createElement("button");
        questionButton.className = "faq-question";
        questionButton.textContent = item.question;
        questionButton.setAttribute("aria-expanded", "false");
        questionButton.style.display = "block";
        questionButton.style.width = "100%";
        questionButton.style.textAlign = "left";

        const answer = document.createElement("p");
        answer.textContent = item.answer;
        answer.style.display = "none";

        questionButton.addEventListener("click", function () {
            const isOpen =
                questionButton.getAttribute("aria-expanded") === "true";

            questionButton.setAttribute(
                "aria-expanded",
                String(!isOpen)
            );

            answer.style.display = isOpen ? "none" : "block";
        });

        faqSection.appendChild(questionButton);
        faqSection.appendChild(answer);
    });

    const mainArea = document.querySelector("main");

    if (mainArea) {
        mainArea.appendChild(faqSection);
    }
}



// Practical 5 - Registration Form Validation

const form = document.getElementById("form");

if (form) {
    const passwordInput = document.getElementById("password");

    passwordInput.addEventListener("input", function() {
        let password = passwordInput.value;
        let strength = document.getElementById("password-strength");

        if (password.length === 0) {
            strength.textContent = "Password strength: Not entered";
        } else if (password.length < 8) {
            strength.textContent = "Password strength: Weak";
        } else if (
            /[A-Z]/.test(password) &&
            /[a-z]/.test(password) &&
            /[0-9]/.test(password) &&
            /[@$!%?&]/.test(password)
        ) {
            strength.textContent = "Password strength: Strong";
        } else {
            strength.textContent = "Password strength: Medium";
        }
    });

    form.addEventListener("submit", function(event) {
        event.preventDefault();

        let isValid = true;

        let name = document.getElementById("name").value.trim();
        let email = document.getElementById("email").value.trim();
        let mobile = document.getElementById("mobile").value.trim();
        let id = document.getElementById("id").value.trim();
        let password = document.getElementById("password").value;
        let confirmPassword = document.getElementById("confirmPassword").value;
        let course = document.getElementById("course").value;
        let year = document.getElementById("year").value;

        // Clear previous errors
        document.querySelectorAll("#form .error").forEach(function(error) {
            error.textContent = "";
        });

        document.getElementById("success-message").textContent = "";

        // Name validation
        let nameRegex = /^[A-Za-z ]{1,30}$/;

        if (!nameRegex.test(name)) {
            document.getElementById("name-error").textContent =
                "Enter a name with 1-30 letters.";
            isValid = false;
        }

        // Email validation
        let emailRegex = /^([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9.-]+)\.([A-Za-z]{2,})$/;

        if (!emailRegex.test(email)) {
            document.getElementById("email-error").textContent =
                "Enter a valid email address.";
            isValid = false;
        }

        // Student ID validation
        let idRegex = /^[dD]?(25|26)[iI][tT]\d{3}$/;

        if (!idRegex.test(id)) {
            document.getElementById("id-error").textContent =
                "Enter a valid ID, e.g. 25IT062 or D26IT001.";
            isValid = false;
        }

        // Mobile validation
        let phoneRegex = /^[7-9][0-9]{9}$/;

        if (!phoneRegex.test(mobile)) {
            document.getElementById("mobile-error").textContent =
                "Enter a valid 10-digit mobile number.";
            isValid = false;
        }

        // Password validation
        let passwordRegex =
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%?&])[A-Za-z\d@$!%?&]{8,}$/;

        if (!passwordRegex.test(password)) {
            document.getElementById("password-error").textContent =
                "Use 8+ characters, uppercase, lowercase, number and special character.";
            isValid = false;
        }

        // Confirm password
        if (password !== confirmPassword || confirmPassword === "") {
            document.getElementById("confirm-error").textContent =
                "Passwords do not match.";
            isValid = false;
        }

        // Gender validation
        let gender = document.querySelector('input[name="gender"]:checked');

        if (!gender) {
            document.getElementById("gender-error").textContent =
                "Please select a gender.";
            isValid = false;
        }

        // Terms validation
        let terms = document.querySelector('input[name="terms"]:checked');

        if (!terms) {
            document.getElementById("term-error").textContent =
                "Please accept the terms and conditions.";
            isValid = false;
        }

        // Course validation
        if (course === "") {
            document.getElementById("course-error").textContent =
                "Please select a course.";
            isValid = false;
        }

        // Year validation
        if (year === "") {
            document.getElementById("year-error").textContent =
                "Please select a year.";
            isValid = false;
        }

        // Display result
        if (isValid) {
            document.getElementById("success-message").textContent =
                "Registration Successful!";
            form.reset();
            document.getElementById("password-strength").textContent =
                "Password strength: Not entered";
        }
    });
}