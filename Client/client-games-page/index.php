<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/client.php';

start_secure_session();
require_role(['CLIENT']);

$showNotificationDot = false;
try {
  $showNotificationDot = client_has_unread_notifications_for_current_user();
} catch (Throwable $exception) {
  $showNotificationDot = false;
}
?>
<!doctype html>
<html lang="ar">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>الأنشطة و الألعاب</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet" />
  <link rel="icon" type="image/png" href="../images/Silver.png" />
  <link rel="shortcut icon" href="../images/Silver.png" />
  <link rel="apple-touch-icon" href="../images/Silver.png" />
  <link rel="stylesheet" href="../client-dashboard-page/style.css" />
  <link rel="stylesheet" href="./style.css" />
</head>

<body>
  <header class="navbar">
    <div class="menu-btn" id="menuBtn">
      <i class="fa fa-bars"></i>
    </div>
    <div class="nav-title">الأنشطة و الألعاب</div>
    <div class="nav-right">
      <a href="../client-notifications-page/notifications.php" class="bell">
        <i class="fa-regular fa-bell"></i>
        <span class="dot" id="notificationDot"<?= $showNotificationDot ? '' : ' style="display:none;"' ?>></span>
      </a>
    </div>
  </header>

  <?php $clientSidebarActive = 'activities';
  include __DIR__ . '/../partials/sidebar.php'; ?>

  <div class="main-content">
    <div class="background-shapes">

      <section class="hero">
        <h1>الأنشطة و الألعاب العلاجية</h1>
        <p>أنشطة مصممة خصيصاً لمساعدتك في رحلتك العلاجية</p>
      </section>

      <section class="stats-row">
        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">الأنشطة المكتملة</div>
            <div class="stat-value" id="totalCases">6</div>
          </div>
          <div class="stat-icon">
            <img src="../images/Container4.png" alt="إجمالي الحالات" />
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">الأنشطة المؤجلة</div>
            <div class="stat-value" id="activeCases">3</div>
          </div>
          <div class="stat-icon">
            <img src="../images/Container 5.png" alt=" لحالات النشطة" />
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">إجمالي الأنشطة</div>
            <div class="stat-value" id="todayAppointments">9</div>
          </div>
          <div class="stat-icon">
            <img src="../images/Container 6.png" alt="مواعيد اليوم" />
          </div>
        </div>
      </section>

      <div class="upload-section">
        <div class="upload-title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#26b5a4" stroke-width="2"
            stroke-linecap="round">
            <path d="M12 15V3m0 0l-4 4m4-4l4 4M4 20h16" />
          </svg>
          رفع ملفات النشاط
        </div>
        <div class="upload-subtitle">
          ارفع صور أو فيديوهات لطفلك أثناء اللعب، النتائج، أو أي لحظة مميزة
        </div>

        <div class="drop-zone" id="dropZone">
          <input type="file" id="fileInput" multiple accept="image/*,video/*" />
          <div class="upload-icon">
            <img src="../images//Upload.png" alt="رفع الملفات" />
          </div>
          <div class="drop-label">اضغط هنا أو اسحب الملفات</div>
        </div>

        <div class="preview-grid" id="previewGrid"></div>

        <div class="upload-actions">
          <span class="count-label" id="countLabel">لم يتم اختيار ملفات بعد</span>
          <button class="upload-btn" id="uploadBtn" disabled>
            رفع الملفات
          </button>
        </div>
        <div class="success-msg" id="successMsg">تم رفع الملفات بنجاح!</div>
      </div>

      <h2 class="names">التمارين و الأنشطة المتاحة</h2>

      <div class="games">
        <!-- تمارين -->
        <div class="section-header">
          <h1>تمارين</h1>
        </div>

        <section class="games-container" id="gamesContainer">
          <div class="game-card">
            <h2>تمرين التنفس</h2>
            <a href="loader.php?game=./breathing-game/index.php" class="btn">جرّب الآن</a>
          </div>
        </section>

        <div class="section-header">
          <h1>ألعاب التركيز للأطفال</h1>
        </div>

        <section class="games-container" id="gamesContainer">
          <div class="game-card">
            <h2>الفروقات بين الصور</h2>
            <a href="loader.php?game=./difference-game/index.php" class="btn">جرّب الآن</a>
          </div>

          <div class="game-card">
            <h2>ابحث عن العنصر المفقود</h2>
            <a href="loader.php?game=./misplacedpin/index.php" class="btn">جرّب الآن</a>
          </div>
        </section>

        <div class="section-header">
          <h1>ألعاب الذاكرة للأطفال</h1>
        </div>

        <section class="games-container" id="gamesContainer">
          <div class="game-card">
            <h2>لعبة البطاقات</h2>
            <a href="loader.php?game=./memory-game/index.php" class="btn">جرّب الآن</a>
          </div>

          <div class="game-card">
            <h2>الكلمات المتقاطعة</h2>
            <a href="loader.php?game=./crossword-game/index.php" class="btn">جرّب الآن</a>
          </div>

          <div class="game-card">
            <h2>صورة وعليها أسئلة بعد ما تختفي</h2>
            <a href="loader.php?game=./questions/index.php" class="btn">جرّب الآن</a>
          </div>
        </section>
      </div>

      <div class="tip-box">
        <div class="tip-header">
          <img src="../images/Container3.png" alt="أيقونة" />
          <h2>نصائح للأنشطة</h2>
        </div>
        <ul>
          <li>
            خصص وقتاً يومياً لممارسة الألعاب والأنشطة لتعزيز مهارات الطفل
          </li>
          <li>
            اجعل وقت اللعب لحظة ممتعة تقضيها مع طفلك، وليس مجرد نشاط عابر
          </li>
          <li>شارك نتائج أنشطتك مع أخصائيك النفسي لمتابعة أفضل</li>
        </ul>
      </div>
    </div>
  </div>

  <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>
  <script src="./games.js"></script>
  <script src="./activities.js"></script>
</body>

</html>