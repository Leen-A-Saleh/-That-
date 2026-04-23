document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("resultModal");
  if (!modal) return;

  const el = {
    patient:    document.getElementById("modalPatient"),
    test:       document.getElementById("modalTest"),
    category:   document.getElementById("modalCategory"),
    score:      document.getElementById("modalScore"),
    percent:    document.getElementById("modalPercent"),
    level:      document.getElementById("modalLevel"),
    trend:      document.getElementById("modalTrend"),
    trendIcon:  document.getElementById("modalTrendIcon"),
    trendLabel: document.getElementById("modalTrendLabel"),
    prev:       document.getElementById("modalPrev"),
    date:       document.getElementById("modalDate"),
    status:     document.getElementById("modalStatus"),
  };

  let currentId = null;

  function openModal(btn) {
    const d = btn.dataset;
    currentId = d.id;

    el.patient.textContent  = d.name;
    el.test.textContent     = d.title;
    el.category.textContent = d.category;
    el.score.textContent    = d.max ? `${d.score}/${d.max}` : d.score;
    el.percent.textContent  = d.percent !== "" ? `${d.percent}%` : "";
    el.date.textContent     = d.date;

    el.level.textContent = d.levelLabel;
    el.level.className   = "pill " + d.levelClass;

    el.trend.className        = "trend " + d.trendClass;
    el.trendIcon.className    = "fa-solid " + d.trendIcon;
    el.trendLabel.textContent = d.trendLabel;
    el.prev.textContent = d.prev
      ? `( النتيجة السابقة: ${d.prev} )`
      : "";

    el.status.textContent = d.statusLabel;
    el.status.className   = "status-pill " + d.statusClass;

    modal.querySelectorAll(".btn-status").forEach(function (b) {
      b.disabled = b.dataset.setStatus === d.status;
    });

    modal.classList.add("show");
  }

  function closeModal() {
    modal.classList.remove("show");
    currentId = null;
  }

  document.querySelectorAll(".action-view").forEach(function (btn) {
    btn.addEventListener("click", function () {
      openModal(this);
    });
  });

  modal.querySelectorAll("[data-close]").forEach(function (btn) {
    btn.addEventListener("click", closeModal);
  });

  modal.addEventListener("click", function (e) {
    if (e.target === modal) closeModal();
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && modal.classList.contains("show")) closeModal();
  });

  modal.querySelectorAll(".btn-status").forEach(function (btn) {
    btn.addEventListener("click", function () {
      if (!currentId || this.disabled) return;
      const newStatus = this.dataset.setStatus;

      const fd = new FormData();
      fd.append("action", "update_status");
      fd.append("result_id", currentId);
      fd.append("status", newStatus);

      fetch("index.php", {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        body: fd
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert(data.message || "تعذّر تحديث الحالة");
          }
        })
        .catch(() => alert("حدث خطأ في الاتصال"));
    });
  });
});
