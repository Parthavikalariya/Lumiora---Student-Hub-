// Light / Dark Theme Switcher
document.addEventListener("DOMContentLoaded", function () {
    const themeToggle = document.getElementById("themeToggle");
    const savedTheme = localStorage.getItem("theme");

    if (savedTheme === "dark") {
        document.body.classList.add("dark-theme");
    }

    function updateButton() {
        if (!themeToggle) return;

        if (document.body.classList.contains("dark-theme")) {
            themeToggle.innerHTML = "☀️ Light Mode";
        } else {
            themeToggle.innerHTML = "🌙 Dark Mode";
        }
    }

    updateButton();

    if (themeToggle) {
        themeToggle.addEventListener("click", function () {
            document.body.classList.toggle("dark-theme");

            if (document.body.classList.contains("dark-theme")) {
                localStorage.setItem("theme", "dark");
            } else {
                localStorage.setItem("theme", "light");
            }

            updateButton();
        });
    }

    // HAMBURGER MENU
    const hamburger = document.getElementById("hamburger");
    const navbar = document.getElementById("navbar");

    if (hamburger && navbar) {
        hamburger.addEventListener("click", function () {
            navbar.classList.toggle("active");
        });
    }

    // NOTIFICATION BANNER
    const closeNotification = document.getElementById("closeNotification");

    if (closeNotification) {
        closeNotification.addEventListener("click", function () {
            const banner = document.querySelector(".notification-banner");

            if (banner) {
                banner.style.display = "none";
            }
        });
    }

    // IMAGE SLIDER
    const slides = document.querySelectorAll(".slide");
    const dots = document.querySelectorAll(".dot");
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");

    let currentSlide = 0;

    function showSlide(index) {
        if (slides.length === 0) {
            return;
        }

        if (index >= slides.length) {
            currentSlide = 0;
        } else if (index < 0) {
            currentSlide = slides.length - 1;
        } else {
            currentSlide = index;
        }

        slides.forEach(function (slide) {
            slide.classList.remove("active");
        });

        dots.forEach(function (dot) {
            dot.classList.remove("active");
        });

        slides[currentSlide].classList.add("active");

        if (dots[currentSlide]) {
            dots[currentSlide].classList.add("active");
        }
    }

    if (nextBtn) {
        nextBtn.addEventListener("click", function () {
            showSlide(currentSlide + 1);
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener("click", function () {
            showSlide(currentSlide - 1);
        });
    }

    dots.forEach(function (dot) {
        dot.addEventListener("click", function () {
            const slideIndex = parseInt(this.getAttribute("data-slide"));
            showSlide(slideIndex);
        });
    });

    if (slides.length > 0) {
        showSlide(0);
    }

    // NEW POPUP
    const openWhatsNew = document.getElementById("openWhatsNew");
    const closeWhatsNew = document.getElementById("closeWhatsNew");
    const whatsNewModal = document.getElementById("whatsNewModal");

    if (openWhatsNew && whatsNewModal) {
        openWhatsNew.addEventListener("click", function () {
            whatsNewModal.classList.add("show");
        });
    }

    if (closeWhatsNew && whatsNewModal) {
        closeWhatsNew.addEventListener("click", function () {
            whatsNewModal.classList.remove("show");
        });
    }

    if (whatsNewModal) {
        whatsNewModal.addEventListener("click", function (event) {
            if (event.target === whatsNewModal) {
                whatsNewModal.classList.remove("show");
            }
        });
    }

    // FAQ SECTION
    const faqButtons = document.querySelectorAll(".faq-question");

    faqButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const faqItem = this.closest(".faq-item");

            if (!faqItem) {
                return;
            }

            const answer = faqItem.querySelector(".faq-answer");
            const icon = faqItem.querySelector(".faq-plus");

            if (!answer || !icon) {
                return;
            }

            document.querySelectorAll(".faq-item").forEach(function (item) {
                if (item !== faqItem) {
                    item.classList.remove("active");

                    const otherAnswer = item.querySelector(".faq-answer");
                    const otherIcon = item.querySelector(".faq-plus");

                    if (otherAnswer) {
                        otherAnswer.style.display = "none";
                    }

                    if (otherIcon) {
                        otherIcon.textContent = "+";
                    }
                }
            });

            if (faqItem.classList.contains("active")) {
                faqItem.classList.remove("active");
                answer.style.display = "none";
                icon.textContent = "+";
            } else {
                faqItem.classList.add("active");
                answer.style.display = "block";
                icon.textContent = "−";
            }
        });
    });
});

// LOGOUT
document.addEventListener("DOMContentLoaded", function () {
    const logoutBtn = document.getElementById("logoutBtn");
    const logoutBtn2 = document.getElementById("logoutBtn2");
    const logoutModal = document.getElementById("logoutModal");
    const closeLogout = document.getElementById("closeLogout");

    function showLogoutMessage(event) {
        if (event) {
            event.preventDefault();
        }

        logoutModal.style.display = "flex";
        document.body.style.overflow = "hidden";

        setTimeout(function () {
            window.location.href = "index.html";
        }, 2000);
    }

    if (logoutBtn) {
        logoutBtn.addEventListener("click", showLogoutMessage);
    }

    if (logoutBtn2) {
        logoutBtn2.addEventListener("click", showLogoutMessage);
    }

    if (closeLogout) {
        closeLogout.addEventListener("click", function () {
            logoutModal.style.display = "none";
            document.body.style.overflow = "";
        });
    }

    if (logoutModal) {
        logoutModal.addEventListener("click", function (event) {
            if (event.target === logoutModal) {
                logoutModal.style.display = "none";
                document.body.style.overflow = "";
            }
        });
    }
});

//Register Form
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registerForm");
    const photoInput = document.getElementById("profilePhoto");
    const photoPreview = document.getElementById("profilePreview");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirmPassword");
    const mobile = document.getElementById("mobile");
    const aadhaar = document.getElementById("aadhaar");
    const pincode = document.getElementById("pincode");

    if (photoInput && photoPreview) {
        photoInput.addEventListener("change", function () {
            const file = this.files[0];

            if (!file) return;

            if (!file.type.startsWith("image/")) {
                alert("Please select a valid image file.");
                this.value = "";
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert("Photo size must be less than 2 MB.");
                this.value = "";
                return;
            }

            const reader = new FileReader();

            reader.onload = function (e) {
                photoPreview.src = e.target.result;
            };

            reader.readAsDataURL(file);
        });
    }

    if (mobile) {
        mobile.addEventListener("input", function () {
            this.value = this.value.replace(/\D/g, "").slice(0, 10);
        });
    }

    if (aadhaar) {
        aadhaar.addEventListener("input", function () {
            this.value = this.value.replace(/\D/g, "").slice(0, 12);
        });
    }

    if (pincode) {
        pincode.addEventListener("input", function () {
            this.value = this.value.replace(/\D/g, "").slice(0, 6);
        });
    }

    if (form) {
        form.addEventListener("submit", function (event) {
            event.preventDefault();

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const firstName = document.getElementById("firstName").value.trim();
            const lastName = document.getElementById("lastName").value.trim();
            const email = document.getElementById("email").value.trim();
            const mobileNumber = mobile.value.trim();
            const passwordValue = password.value;
            const confirmPasswordValue = confirmPassword.value;

            const nameRegex = /^[A-Za-z ]{2,30}$/;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const mobileRegex = /^[6-9][0-9]{9}$/;
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;

            if (!nameRegex.test(firstName)) {
                alert("Please enter a valid first name.");
                return;
            }

            if (!nameRegex.test(lastName)) {
                alert("Please enter a valid last name.");
                return;
            }

            if (!emailRegex.test(email)) {
                alert("Please enter a valid email address.");
                return;
            }

            if (!mobileRegex.test(mobileNumber)) {
                alert("Please enter a valid 10-digit mobile number.");
                return;
            }

            if (!passwordRegex.test(passwordValue)) {
                alert("Password must contain 8+ characters, uppercase, lowercase, number and special character.");
                return;
            }

            if (passwordValue !== confirmPasswordValue) {
                alert("Password and Confirm Password do not match.");
                return;
            }

            if (!document.getElementById("terms").checked) {
                alert("Please accept the Terms and Conditions.");
                return;
            }

            if (photoPreview && photoPreview.src) {
                localStorage.setItem("profilePhoto", photoPreview.src);
            }

            alert("Account created successfully!");
            window.location.href = "login.html";
        });
    }
});

//Forgot Password
const forgotForm = document.getElementById("forgotForm");

if (forgotForm) {
    forgotForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const email = document.getElementById("forgotEmail").value.trim();
        const registeredEmail = localStorage.getItem("registeredEmail");

        if (email === registeredEmail) {
            alert("Email verified. Please create a new password.");
            window.location.href = "reset-password.html";
        } else {
            alert("Email address is not registered.");
        }
    });
}

//Reset Password
const resetPasswordForm = document.getElementById("resetPasswordForm");

if (resetPasswordForm) {
    resetPasswordForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const newPassword = document.getElementById("newPassword").value;
        const confirmNewPassword = document.getElementById("confirmNewPassword").value;

        const passwordPattern =
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;

        if (!passwordPattern.test(newPassword)) {
            alert("Password must contain 8+ characters, uppercase, lowercase, number and special character.");
            return;
        }

        if (newPassword !== confirmNewPassword) {
            alert("New Password and Confirm Password do not match.");
            return;
        }

        localStorage.setItem("registeredPassword", newPassword);

        alert("Password reset successfully!");

        window.location.href = "login.html";
    });
}

// Fetch API + Search + Filter + Sort + Pagination

let allEvents = [];
let filteredEvents = [];
let currentPage = 1;

const eventsPerPage = 6;
const eventsContainer = document.getElementById("eventsContainer");
const searchInput = document.getElementById("searchInput");
const categoryFilter = document.getElementById("categoryFilter");
const sortSelect = document.getElementById("sortSelect");
const eventCount = document.getElementById("eventCount");
const pagination = document.getElementById("pagination");
const noResults = document.getElementById("noResults");

// FETCH JSON DATA

async function loadEvents() {

    try {
        const response = await fetch("data.json");
        
        if (!response.ok) {
            throw new Error("Unable to load event data");
        }

        allEvents = await response.json();
        filteredEvents = [...allEvents];
        displayEvents();

    } catch (error) {

        console.error("Error:", error);

        eventsContainer.innerHTML = `
            <div class="no-results" style="display:block;">
                <h3>Unable to load events</h3>
                <p>Please check the data.json file.</p>
            </div>
        `;
    }
}

function displayEvents() {
    applySearchAndFilter();
}

function applySearchAndFilter() {

    const searchText =
        searchInput.value.toLowerCase().trim();

    const category =
        categoryFilter.value;

    filteredEvents = allEvents.filter(event => {

        const matchesSearch =
            event.title.toLowerCase().includes(searchText) ||
            event.description.toLowerCase().includes(searchText) ||
            event.venue.toLowerCase().includes(searchText);

        const matchesCategory =
            category === "all" ||
            event.category === category;

        return matchesSearch && matchesCategory;

    });

    const sortValue = sortSelect.value;

    filteredEvents.sort((a, b) => {

        if (sortValue === "date-asc") {
            return new Date(a.date) - new Date(b.date);
        }
        if (sortValue === "date-desc") {
            return new Date(b.date) - new Date(a.date);
        }
        if (sortValue === "title-asc") {
            return a.title.localeCompare(b.title);
        }
        if (sortValue === "title-desc") {
            return b.title.localeCompare(a.title);
        }
    });

    currentPage = 1;
    renderEvents();
}

function renderEvents() {

    eventsContainer.innerHTML = "";

    if (filteredEvents.length === 0) {
        noResults.style.display = "block";
        eventCount.textContent = "0 events found";
        pagination.innerHTML = "";
        return;
    }
    noResults.style.display = "none";

    const startIndex = (currentPage - 1) * eventsPerPage;
    const endIndex = startIndex + eventsPerPage;
    const pageEvents = filteredEvents.slice(startIndex, endIndex);
    
        eventCount.textContent =
        `${filteredEvents.length} event(s) found`;

    pageEvents.forEach(event => {

        const card = document.createElement("div");
        card.className = "event-card";

        card.innerHTML = `
            <span class="event-category">
                ${event.category}
            </span>

            <h3>${event.title}</h3>
            <p class="event-description">${event.description}</p>

            <div class="event-details">
                <p>📅 <strong>Date:</strong>${formatDate(event.date)}</p>
                <p>⏰ <strong>Time:</strong>${event.time}</p>
                <p>📍<strong>Venue:</strong>${event.venue}</p>
            </div>
        `;
        eventsContainer.appendChild(card);
    });
    createPagination();
}
function formatDate(dateString) {

    const date = new Date(dateString);

    return date.toLocaleDateString("en-IN", {
        day: "2-digit",
        month: "short",
        year: "numeric"
    });
}
function createPagination() {

    pagination.innerHTML = "";

    const totalPages =
        Math.ceil(
            filteredEvents.length / eventsPerPage
        );

    if (totalPages <= 1) {
        return;
    }

    const previousButton = document.createElement("button");
    previousButton.textContent = "Previous";

    previousButton.disabled =
        currentPage === 1;

    previousButton.addEventListener(
        "click",
        () => {
            if (currentPage > 1) {
                currentPage--;
                renderEvents();

                window.scrollTo({
                    top: 0,
                    behavior: "smooth"
                });

            }
        }
    );
    pagination.appendChild(previousButton);

    for ( let page = 1; page <= totalPages; page++) {

        const pageButton = document.createElement("button");
        pageButton.textContent = page;

        if (page === currentPage) {
            pageButton.classList.add("active");
        }

        pageButton.addEventListener(
            "click",
            () => {

                currentPage = page;
                renderEvents();

                window.scrollTo({
                    top: 0,
                    behavior: "smooth"
                });
            }
        );
        pagination.appendChild(pageButton);
    }

    const nextButton = document.createElement("button");

    nextButton.textContent = "Next";

    nextButton.disabled =
        currentPage === totalPages;

    nextButton.addEventListener(
        "click",
        () => {

            if (currentPage < totalPages) {

                currentPage++;
                renderEvents();

                window.scrollTo({
                    top: 0,
                    behavior: "smooth"
                });
            }
        }
    );
    pagination.appendChild(nextButton);
}

searchInput.addEventListener("input",applySearchAndFilter);
categoryFilter.addEventListener("change",applySearchAndFilter);
sortSelect.addEventListener("change",applySearchAndFilter);

loadEvents();