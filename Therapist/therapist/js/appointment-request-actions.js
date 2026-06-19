function therapistHandleAppointmentAction(button, action) {
  const card = button.closest("[data-appointment-card]");
  const appointmentId = button.getAttribute("data-appointment-id");

  if (!appointmentId || !card) {
    return;
  }

  const footer = card.querySelector(".request-footer, .actions");
  const acceptBtn = card.querySelector(".btn-accept");
  const rejectBtn = card.querySelector(".btn-reject");
  const buttons = [acceptBtn, rejectBtn].filter(Boolean);

  buttons.forEach(function (btn) {
    btn.disabled = true;
  });

  const formData = new FormData();
  formData.append("appointment_id", appointmentId);
  formData.append("action", action);

  const apiUrl = button.getAttribute("data-api-url") || "index.php";

  fetch(apiUrl, {
    method: "POST",
    body: formData,
  })
    .then(function (response) {
      return response
        .json()
        .catch(function () {
          return {
            success: false,
            message: "استجابة غير متوقعة من الخادم.",
          };
        })
        .then(function (data) {
          return { response: response, data: data };
        });
    })
    .then(function (result) {
      if (!result.data.success) {
        const message =
          result.data.message || "تعذر تنفيذ الإجراء. يرجى المحاولة مرة أخرى.";
        showErrorAlert(message);
        buttons.forEach(function (btn) {
          btn.disabled = false;
        });
        return;
      }

      if (!footer) {
        return;
      }

      if (action === "accept") {
        card.style.opacity = "0.5";
        footer.innerHTML =
          '<span style="color: #28a745; font-weight: 600;">✓ تم قبول الطلب</span>';
        card.dataset.status = "accepted";
      } else {
        card.style.opacity = "0.5";
        footer.innerHTML =
          '<span style="color: #dc3545; font-weight: 600;">✗ تم رفض الطلب</span>';
        card.dataset.status = "rejected";
      }
    })
    .catch(function () {
      showErrorAlert("حدث خطأ، يرجى المحاولة مرة أخرى");
      buttons.forEach(function (btn) {
        btn.disabled = false;
      });
    });
}

document.querySelectorAll(".btn-accept[data-appointment-id]").forEach(function (btn) {
  btn.addEventListener("click", function () {
    therapistHandleAppointmentAction(btn, "accept");
  });
});

document.querySelectorAll(".btn-reject[data-appointment-id]").forEach(function (btn) {
  btn.addEventListener("click", function () {
    therapistHandleAppointmentAction(btn, "reject");
  });
});
