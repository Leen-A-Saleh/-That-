document.addEventListener("DOMContentLoaded", function () {
    // 1. Polling for Badges
    const notifBadges = document.querySelectorAll('.global-notif-badge');
    const msgBadges = document.querySelectorAll('.global-msg-badge');

    function updateBadges() {
        if (notifBadges.length === 0 && msgBadges.length === 0) return;

        fetch("/That-Copy/Public/api/api-badges.php", {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
            cache: "no-store",
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notifCount = parseInt(data.unread_notifications, 10);
                const msgCount = parseInt(data.unread_messages, 10);

                notifBadges.forEach(badge => {
                    if (notifCount > 0) {
                        badge.textContent = notifCount;
                        // Use original display style (inline-flex or block)
                        badge.style.display = badge.classList.contains('sidebar-chat-badge') ? 'inline-flex' : 'inline-flex';
                    } else {
                        badge.textContent = '';
                        badge.style.display = 'none';
                    }
                });

                msgBadges.forEach(badge => {
                    if (msgCount > 0) {
                        badge.textContent = msgCount;
                        badge.style.display = badge.classList.contains('sidebar-chat-badge') ? 'inline-flex' : 'inline-flex';
                    } else {
                        badge.textContent = '';
                        badge.style.display = 'none';
                    }
                });
            }
        })
        .catch(console.error);
    }

    // Initial fetch
    updateBadges();
    // Poll every 10 seconds
    setInterval(updateBadges, 10000);

    // Expose globally so other scripts can trigger it manually (e.g., after reading a notification)
    window.refreshGlobalBadges = updateBadges;


    // 2. Relative Time Formatting
    function timeSince(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        let interval = seconds / 31536000;
        if (interval >= 1) return Math.floor(interval) + " سنة مضت";
        
        interval = seconds / 2592000;
        if (interval >= 1) return Math.floor(interval) + " شهر مضى";
        
        interval = seconds / 604800;
        if (interval >= 1) {
            const weeks = Math.floor(interval);
            return weeks === 1 ? "منذ أسبوع" : "منذ " + weeks + " أسابيع";
        }
        
        interval = seconds / 86400;
        if (interval >= 1) {
            const days = Math.floor(interval);
            if (days === 1) return "أمس";
            if (days === 2) return "منذ يومين";
            return "منذ " + days + " أيام";
        }
        
        interval = seconds / 3600;
        if (interval >= 1) {
            const hours = Math.floor(interval);
            if (hours === 1) return "منذ ساعة";
            if (hours === 2) return "منذ ساعتين";
            if (hours <= 10) return "منذ " + hours + " ساعات";
            return "منذ " + hours + " ساعة";
        }
        
        interval = seconds / 60;
        if (interval >= 1) {
            const minutes = Math.floor(interval);
            if (minutes === 1) return "منذ دقيقة";
            if (minutes === 2) return "منذ دقيقتين";
            if (minutes <= 10) return "منذ " + minutes + " دقائق";
            return "منذ " + minutes + " دقيقة";
        }
        
        return "الآن";
    }

    function updateRelativeTimes() {
        const timeElements = document.querySelectorAll('.relative-time');
        timeElements.forEach(el => {
            const timestamp = el.getAttribute('data-timestamp');
            if (timestamp) {
                // Ensure date parsing works for standard ISO or YYYY-MM-DD HH:MM:SS
                // Replace space with T to make it ISO 8601 compliant if needed
                const validTimestamp = timestamp.replace(" ", "T");
                const dateObj = new Date(validTimestamp);
                if (!isNaN(dateObj.getTime())) {
                    el.textContent = timeSince(dateObj);
                }
            }
        });
    }

    // Expose globally to be called when new elements are dynamically added
    window.updateRelativeTimes = updateRelativeTimes;

    updateRelativeTimes();
    setInterval(updateRelativeTimes, 60000);
});
