document.addEventListener("DOMContentLoaded", function () {

  function postAction(action, id) {
    const fd = new FormData();
    fd.append("action", action);
    fd.append("notification_id", id);
    return fetch("index.php", {
      method: "POST",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      body: fd
    }).then(r => r.json());
  }

  document.querySelectorAll(".icon-btn.delete").forEach(function (btn) {
    btn.addEventListener("click", function () {
      const row = btn.closest(".notification");
      const id = row.dataset.id;
      if (!id) return;

      postAction("delete", id)
        .then(data => {
          if (!data.success) {
            alert(data.message || "تعذّر حذف التنبيه");
            return;
          }
          row.style.height = row.offsetHeight + "px";
          row.style.transition = "opacity 0.2s ease, height 0.25s ease, margin 0.25s ease";
          requestAnimationFrame(() => {
            row.style.opacity = "0";
            row.style.margin = "0";
            row.style.height = "0";
          });
          setTimeout(() => {
            row.remove();
            if (!document.querySelector(".notification")) location.reload();
          }, 260);
        })
        .catch(() => alert("حدث خطأ في الاتصال"));
    });
  });

  document.querySelectorAll(".icon-btn.done").forEach(function (btn) {
    btn.addEventListener("click", function () {
      const row = btn.closest(".notification");
      const id = row.dataset.id;
      if (!id) return;

      postAction("toggle_read", id)
        .then(data => {
          if (!data.success) {
            alert(data.message || "تعذّر تحديث الحالة");
            return;
          }
          const isRead = data.is_read === 1;
          row.classList.toggle("read", isRead);
          const dot = row.querySelector(".notif-title-row .status-dot");
          if (isRead && dot) dot.remove();
          if (isRead) btn.remove();
        })
        .catch(() => alert("حدث خطأ في الاتصال"));
    });
  });

});
