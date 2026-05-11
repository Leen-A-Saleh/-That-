
document.addEventListener("DOMContentLoaded", function () {
    const menuBtn = document.querySelector(".menu-btn");
    const sidebar = document.querySelector(".sidebar");
    const overlay = document.querySelector(".sidebar-overlay");

    if (menuBtn && sidebar && overlay) {
        // Open sidebar on mobile
        menuBtn.addEventListener("click", () => {
            sidebar.classList.add("active");
            overlay.classList.add("active");
        });

        // Close sidebar when clicking the overlay
        overlay.addEventListener("click", () => {
            sidebar.classList.remove("active");
            overlay.classList.remove("active");
        });
    }

    // Note: Filters submit the form automatically via onchange="this.form.submit()" in PHP
    const searchInput = document.getElementById("search-input");
    if (searchInput) {
        // Submit search when pressing the Enter key
        searchInput.addEventListener("keypress", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                this.form.submit();
            }
        });
    }

    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", function () {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "../login-page/login.php"; 
            }
        });
    }
});

/**
 * Function to open the details modal
 * Triggered by the "View Details" button in cases.php
 * @param {Object} data - The case data object
 * @param {string} dateStr - The formatted date/time string from PHP
 */
function viewDetails(data, dateStr) {
    const modal = document.getElementById("case-modal");
    if (!modal) return;

    // Fill basic client information
    document.getElementById("modal-avatar").textContent = data.client_name.charAt(0);
    document.getElementById("modal-name").textContent = data.client_name;
    document.getElementById("modal-condition").textContent = "Case ID: #" + data.case_id;
    document.getElementById("modal-doctor").textContent = data.therapist_name;
    document.getElementById("modal-date").textContent = dateStr || "No sessions yet";
    document.getElementById("modal-sessions").textContent = (data.sessions_count || 0) + " sessions";
    
    // Progress bar and percentage
    const progressPct = data.progress || 0;
    document.getElementById("modal-progress-pct").textContent = progressPct + "%";
    document.getElementById("modal-progress-fill").style.width = progressPct + "%";

    // Status Badge mapping
    const badge = document.getElementById("modal-badge");
    const statusTextMap = { 
        'IN_PROGRESS': 'Active', 
        'UNDER_REVIEW': 'Under Review', 
        'CLOSED': 'Closed' 
    };
    const statusClassMap = { 
        'IN_PROGRESS': 'active', 
        'UNDER_REVIEW': 'review', 
        'CLOSED': 'pending' 
    };
    
    badge.textContent = statusTextMap[data.status] || 'Unknown';
    badge.className = "badge " + (statusClassMap[data.status] || "active");

    // Display the modal and lock background scrolling
    modal.classList.add("open");
    document.body.style.overflow = "hidden"; 
}


// Function to close the details modal

function closeModal() {
    const modal = document.getElementById("case-modal");
    if (modal) {
        modal.classList.remove("open");
        document.body.style.overflow = ""; // Restore scrolling
    }
}