<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/client.php';
require_once __DIR__ . '/../../Database/profile-database.php';
// محرّك التوصية بالذكاء الاصطناعي الداخلي (Rule-Based)
require_once __DIR__ . '/therapist_recommendation.php';

start_secure_session();
require_role(['CLIENT']);

$therapists = [];
$bookingError = trim((string) ($_GET['booking_error'] ?? ''));
$showNotificationDot = false;

// نتيجة نظام التوصية: أفضل الأخصائيين المناسبين للمستخدم بناءً على إجابات استبيانه
$recommendation = ['minor' => false, 'has_survey' => false, 'age' => 0, 'recommendations' => []];

try {
  $therapists = client_get_therapist_listings();
} catch (Throwable $exception) {
  $therapists = [];
}

try {
  $recommendation = recommend_for_user(client_current_user_id(), 3);
} catch (Throwable $exception) {
  // في حال أي خطأ لا نكسر الصفحة؛ نكتفي بعدم عرض التوصيات
  $recommendation = ['minor' => false, 'has_survey' => false, 'age' => 0, 'recommendations' => []];
}

try {
  $showNotificationDot = hasUnreadNotifications();
} catch (Throwable) {
  $showNotificationDot = false;
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
        <span class="dot" id="notificationDot"<?= $showNotificationDot ? '' : ' style="display:none;"' ?>></span>
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

    <?php if (!empty($recommendation['recommendations'])): ?>
      <!-- ─── قسم التوصيات الذكية: ناتج نظام الـ Rule-Based AI ─── -->
      <section class="reco-section">
        <div class="reco-header">
          <h2><i class="fa-solid fa-wand-magic-sparkles"></i> الأخصائيون المقترحون لك</h2>
          <p>اقتراحات مبنية على تحليل إجاباتك في الاستبيان</p>
        </div>

        <div class="reco-doctors">
          <?php foreach ($recommendation['recommendations'] as $reco): ?>
            <div class="doctor-card reco-card">
              <span class="reco-badge"><i class="fa-solid fa-star"></i> مقترح لك</span>
              <img src="<?= e((string) $reco['image']) ?>" onerror="this.src='../../storage/avatars/user.png'" alt="<?= e((string) $reco['name']) ?>">
              <div class="doctor-info">
                <div class="doctor-name"><?= e((string) $reco['name']) ?></div>
                <div class="doctor-special"><?= e((string) $reco['specialization']) ?></div>
                <div class="doctor-degree"><?= e((string) $reco['certification']) ?></div>
                <div class="doctor-details">
                  <i class="fa fa-briefcase"></i> <?= e((string) $reco['experience_text']) ?><br>
                  <i class="fa fa-sack-dollar"></i> جلسة استشارية: <?= e((string) $reco['consult_price']) ?><br>
                  <i class="fa fa-coins"></i> جلسة علاجية: <?= e((string) $reco['therapy_price']) ?><br>
                  <i class="fa fa-envelope"></i>
                  <a href="mailto:<?= e((string) $reco['email']) ?>"><?= e((string) $reco['email']) ?></a>
                </div>

                <?php if (!empty($reco['reasons'])): ?>
                  <div class="reco-reasons">
                    <span class="reco-reasons-title"><i class="fa-solid fa-circle-info"></i> سبب الاقتراح:</span>
                    <ul>
                      <?php foreach ($reco['reasons'] as $reason): ?>
                        <li><?= e((string) $reason) ?></li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                <?php endif; ?>

                <button class="book-btn" type="button"
                  onclick="window.location.href='../client-booking-page/booking.php?id=<?= (int) $reco['id'] ?>'">
                  حجز موعد
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

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
    const bookingError = <?= json_encode($bookingError, JSON_UNESCAPED_UNICODE) ?>;
  </script>
  <script src="./dashboard.js"></script>
</body>

</html>
