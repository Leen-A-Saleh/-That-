
document.addEventListener("DOMContentLoaded", function () {
    const menuBtn = document.querySelector(".menu-btn");
    const sidebar = document.querySelector(".sidebar");
    const overlay = document.querySelector(".sidebar-overlay");

    if (menuBtn && sidebar && overlay) {
        // Open sidebar on mobile
        menuBtn.addEventListener("click", () => {
            sidebar.classList.toggle("open");
            overlay.classList.toggle("open");
        });

        overlay.addEventListener("click", () => {
            sidebar.classList.remove("open");
            overlay.classList.remove("open");
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

    const modalOverlay = document.getElementById("modal-overlay");
    if (modalOverlay) {
        modalOverlay.onclick = closeModal;
    }
});

function getProgressColorClass(pct) {
    if (pct <= 30) return "progress-low";
    if (pct <= 80) return "progress-mid";
    return "progress-high";
}

function viewDetails(data, dateStr) {
    const modal = document.getElementById("case-modal");
    if (!modal) return;

    document.getElementById("modal-avatar").textContent = data.client_name.charAt(0);
    document.getElementById("modal-name").textContent = data.client_name;
    document.getElementById("modal-condition").textContent = "رقم الحالة: #" + data.case_id;
    document.getElementById("modal-doctor").textContent = data.therapist_name;
    document.getElementById("modal-date").textContent = dateStr;
    document.getElementById("modal-sessions").textContent = data.sessions_count + " جلسة";

    const progressPct = Number(data.progress) || 0;
    const progressColor = getProgressColorClass(progressPct);
    const progressPctEl = document.getElementById("modal-progress-pct");
    const progressFillEl = document.getElementById("modal-progress-fill");
    progressPctEl.textContent = progressPct + "%";
    progressPctEl.className = "modal-progress-pct " + progressColor;
    progressFillEl.style.width = progressPct + "%";
    progressFillEl.className = "modal-bar-fill " + progressColor;

    const badge = document.getElementById("modal-badge");
    const map = {
        IN_PROGRESS: "جارية",
        UNDER_REVIEW: "تحت المراجعة",
        CLOSED: "مغلقة",
    };
    const clsMap = {
        IN_PROGRESS: "active",
        UNDER_REVIEW: "review",
        CLOSED: "pending",
    };

    badge.textContent = map[data.status];
    badge.className = "badge " + (clsMap[data.status] || "active");

    modal.classList.add("open");
    document.body.style.overflow = "hidden";
}

function closeModal() {
    const modal = document.getElementById("case-modal");
    if (modal) {
        modal.classList.remove("open");
        document.body.style.overflow = "";
    }
}