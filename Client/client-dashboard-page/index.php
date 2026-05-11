<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/client.php';

start_secure_session();
require_role(['CLIENT']);

$therapists = [];
$bookingError = trim((string) ($_GET['booking_error'] ?? ''));
try {
  $therapists = client_get_therapist_listings();
} catch (Throwable $exception) {
  $therapists = [];
}
?>
<!doctype html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ذات - للإستشارات النفسية</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="icon" type="image/png" sizes="32x32" href="../images/Silver.png" />
  <link rel="shortcut icon" sizes="10x10" href="../images/Silver.png" />
  <link rel="apple-touch-icon" href="../images/Silver.png" />
  <link rel="stylesheet" href="./style.css" />
</head>

<body>
  <header class="navbar">
    <div class="menu-btn" id="menuBtn">
      <i class="fa fa-bars"></i>
    </div>
    <div class="nav-title">الرئيسية</div>
    <div class="nav-right">
      <a href="../client-notifications-page/notifications.php" class="bell">
        <i class="fa-regular fa-bell"></i>
        <span class="dot" id="notificationDot"></span>
      </a>
    </div>
  </header>

  <?php $clientSidebarActive = 'dashboard';
  include __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="main">
    <section class="hero">
      <h1>مرحباً بك في ذات</h1>
      <p>إبحث عن الأخصائي المناسب لك واحجز موعدك الآن</p>
    </section>

    <div class="search">
      <input type="text" placeholder="إبحث عن أخصائي أو تخصص..." id="searchInput" />
      <i class="fa fa-search"></i>
    </div>

    <div class="count">
      <span id="doctorCount">0</span>
      أخصائي متاح
    </div>
    
    <div class="doctors" id="doctorsContainer"></div>
  </main>

  <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>

  <script>
    const doctorsFromDatabase = <?= json_encode($therapists, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    (() => {
      const container = document.getElementById("doctorsContainer");
      const count = document.getElementById("doctorCount");
      const searchInput = document.getElementById("searchInput");
      let doctors = Array.isArray(doctorsFromDatabase) ? doctorsFromDatabase : [];

      function renderDoctors(list) {
        if (!container) return;

        container.innerHTML = "";

        if (count) {
          count.innerText = list.length;
        }

        list.forEach((doc) => {
          const availability = Array.isArray(doc.availability) && doc.availability.length > 0
            ? doc.availability.map((slot) => `${slot.day_label} ${slot.start} - ${slot.end}`).join("<br>")
            : doc.work;

          container.innerHTML += `
<div class="doctor-card">
<img src="${doc.image}" onerror="this.src='../images/default-doctor.png'">
<div class="doctor-info">

<div class="doctor-name">${doc.name}</div>
<div class="doctor-special">${doc.special}</div>
<div class="doctor-degree">${doc.degree}</div>
<div class="doctor-details">
<i class="fa fa-briefcase"></i> ${doc.experience}<br>
<i class="fa fa-clock"></i> ${availability}<br>
<i class="fa fa-sack-dollar"></i> جلسة استشارية: ${doc.consultPrice}<br>
<i class="fa fa-coins"></i> جلسة علاجية: ${doc.therapyPrice}<br>
<i class="fa fa-envelope"></i>
<a href="mailto:${doc.email}">
${doc.email}
</a>
</div>
<button class="book-btn" data-id="${doc.id}">
حجز موعد
</button>
</div>
</div>
`;
        });
      }

      renderDoctors(doctors);

      if (searchInput) {
        searchInput.addEventListener("input", function () {
          const value = this.value.toLowerCase();
          const filtered = doctors.filter(
            (doc) =>
              String(doc.name || "").toLowerCase().includes(value) ||
              String(doc.special || "").toLowerCase().includes(value) ||
              String(doc.degree || "").toLowerCase().includes(value)
          );

          renderDoctors(filtered);
        });
      }

      document.addEventListener("click", function (e) {
        if (e.target.classList.contains("book-btn")) {
          const doctorId = Number(e.target.dataset.id || 0);
          if (!Number.isInteger(doctorId) || doctorId <= 0) {
            alert("تعذر تحديد الأخصائي. يرجى المحاولة مرة أخرى.");
            return;
          }

          window.location.href = `../client-booking-page/booking.php?id=${encodeURIComponent(doctorId)}`;
        }
      });
    })();
  </script>
  <?php if ($bookingError === 'missing_therapist_id'): ?>
    <script>
      alert("لا يمكن فتح صفحة الحجز بدون تحديد أخصائي.");
    </script>
  <?php endif; ?>
  <script src="./dashboard.js"></script>
</body>

</html>
