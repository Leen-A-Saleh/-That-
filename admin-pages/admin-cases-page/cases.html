<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>إدارة الحالات</title>
    <link
      href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />
    <link
      rel="icon"
      type="image/png"
      sizes="32x32"
      href="../images/Silver.png"
    />
    <link rel="shortcut icon" sizes="10x10" href="../images/Silver.png" />
    <link rel="stylesheet" href="../admin-dashboard-page/admin-dashboard.css" />
    <link rel="stylesheet" href="./cases.css" />
  </head>

  <body>
    <aside class="sidebar">
      <div class="logo">
        <img src="../images/Frame 393.svg" />
      </div>

      <div class="user-card">
        <div class="user-info">
          <div class="user-greeting">مرحباً،</div>
          <div class="user-name">المدير العام</div>
          <div class="user-role">لوحة تحكم الإدارة</div>
        </div>
      </div>

      <ul>
        <li>
          <a href="../admin-dashboard-page/admin-dashboard.html">
            <img src="../images/Icon.svg" />لوحة التحكم
          </a>
        </li>
        <li>
          <a href="../admin-users-page/users.html">
            <img src="../images/Icon (1).svg" />المستخدمين/المرضى
          </a>
        </li>
        <li>
          <a href="../admin-therapist-page/therapist.html">
            <img src="../images/Icon (2).svg" />الأخصائيين
          </a>
        </li>
        <li class="active">
          <a href="./cases.html">
            <img src="../images/Icon (3).svg" />إدارة الحالات
          </a>
        </li>
        <li>
          <a href="../admin-appointments-page/appointments.html">
            <img src="../images/Icon (4).svg" />المواعيد
          </a>
        </li>
        <li>
          <a href="../admin-tests-page/tests.html">
            <img src="../images/Icon (6).svg" />الاختبارات النفسية
          </a>
        </li>
        <li>
          <a href="../admin-activities-page/activities.html">
            <img src="../images/Icon (7).svg" />الأنشطة والألعاب
          </a>
        </li>
        <li>
          <a href="../admin-notifications-page/notifications.html">
            <img src="../images/Icon (7).svg" />الإشعارات
          </a>
        </li>
        <li>
          <a href="../admin-profile-page/profile.html">
            <img src="../images/Icon7.svg" />الملف الشخصي
          </a>
        </li>
      </ul>

      <button class="logout" id="logoutBtn">
        <img src="../images/Link.png" />
      </button>
    </aside>

    <div class="sidebar-overlay"></div>

    <div class="main-wrapper">
      <header class="navbar">
        <div class="menu-btn">
          <i class="fa fa-bars"></i>
        </div>
        <div class="nav-title">إدارة الحالات</div>
        <div class="nav-right">
          <button class="status-btn">
            <span class="status-dot"></span>
            متصل
          </button>
        </div>
      </header>

      <main class="page-content">
        <div class="filter-bar">
          <div class="search-wrapper">
            <i class="fas fa-search"></i>
            <input
              type="text"
              id="search-input"
              placeholder="البحث في الحالات..."
            />
          </div>
          <select class="filter-select" id="status-filter">
            <option value="">كل الحالات</option>
            <option value="active">جارية</option>
            <option value="review">تحت المراجعة</option>
            <option value="pending">مغلقة</option>
          </select>
          <select class="filter-select" id="doctor-filter">
            <option value="">كل الأطباء</option>
          </select>
        </div>

        <div class="stats-row">
          <div class="stat-card">
            <span class="stat-label">إجمالي الحالات</span>
            <span class="stat-value" id="stat-total">0</span>
          </div>
          <div class="stat-card active-stat">
            <span class="stat-label">حالات جارية</span>
            <span class="stat-value" id="stat-active">0</span>
          </div>
          <div class="stat-card review-stat">
            <span class="stat-label">تحت المراجعة</span>
            <span class="stat-value" id="stat-review">0</span>
          </div>
          <div class="stat-card pending-stat">
            <span class="stat-label">حالات مغلقة</span>
            <span class="stat-value" id="stat-pending">0</span>
          </div>
        </div>

        <div class="cases-list" id="cases-container"></div>

        <div class="pagination-row">
          <span class="pagination-info" id="pagination-info"></span>
          <div class="pagination-btns" id="pagination-btns"></div>
        </div>
      </main>

      <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>
    </div>

    <div class="modal-wrap" id="case-modal">
      <div class="modal-overlay" id="modal-overlay"></div>
      <div class="modal-box">
        <div class="modal-header">
          <div class="modal-avatar" id="modal-avatar"></div>
          <div class="modal-header-info">
            <h3 id="modal-name"></h3>
            <span id="modal-condition"></span>
          </div>
          <button class="modal-close" onclick="closeModal()">
            <i class="fas fa-xmark"></i>
          </button>
        </div>
        <div class="modal-body">
          <div class="modal-row">
            <span class="modal-row-label"
              ><i class="fas fa-user-doctor"></i>الأخصائي</span
            >
            <span class="modal-row-val" id="modal-doctor"></span>
          </div>
          <div class="modal-row">
            <span class="modal-row-label"
              ><i class="fas fa-calendar-days"></i>آخر جلسة</span
            >
            <span class="modal-row-val" id="modal-date"></span>
          </div>
          <div class="modal-row">
            <span class="modal-row-label"
              ><i class="fas fa-layer-group"></i>عدد الجلسات</span
            >
            <span class="modal-row-val" id="modal-sessions"></span>
          </div>
          <div class="modal-row">
            <span class="modal-row-label"
              ><i class="fas fa-circle-dot"></i>الحالة</span
            >
            <span class="badge" id="modal-badge"></span>
          </div>
          <div class="modal-row modal-progress-section">
            <div class="modal-progress-top" style="width: 100%">
              <span><i class="fas fa-chart-line"></i>نسبة التقدم</span>
              <span class="modal-progress-pct" id="modal-progress-pct"></span>
            </div>
            <div class="modal-bar-wrap" style="width: 100%">
              <div class="modal-bar-fill" id="modal-progress-fill"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="./cases.js"></script>
  </body>
</html>
