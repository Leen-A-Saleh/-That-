  <style>
  .sidebar-nav .unread-badge {
    margin-inline-start: auto;
    flex-shrink: 0;
    background: #30b7c4;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  </style>
  <!-- Sidebar overlay -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <a href="../therapist-dashboard/index.php" >
        <img src="img/Frame 392 (1).png" alt="شعار ذات" class="brand-icon">
      </a>
    </div>

    <div class="user-card">
      <div class="user-info">
        <div class="user-greeting">مرحباً،</div>
        <div class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'المعالج') ?></div>
        <div class="user-role">أخصائي نفسي</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <ul>
        <li>
          <a href="../therapist-dashboard/index.php" class="nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i class="fa-solid fa-gauge"></i>
            <span>لوحة التحكم</span>
          </a>
        </li>
        <li>
          <a href="../therapist-cases/index.php" class="nav-link <?= ($activePage ?? '') === 'cases' ? 'active' : '' ?>">
            <i class="fa-solid fa-users"></i>
            <span>الحالات</span>
          </a>
        </li>
        <li>
          <a href="../appoinment-requests/index.php" class="nav-link <?= ($activePage ?? '') === 'requests' ? 'active' : '' ?>">
            <i class="fa-regular fa-bell"></i>
            <span>طلبات المواعيد</span>
          </a>
        </li>
        <li>
          <a href="../appointments/index.php" class="nav-link <?= ($activePage ?? '') === 'appointments' ? 'active' : '' ?>">
            <i class="fa-regular fa-calendar-days"></i>
            <span>مواعيدي</span>
          </a>
        </li>
        <li>
          <a href="../sessions-notes/index.php" class="nav-link <?= ($activePage ?? '') === 'notes' ? 'active' : '' ?>">
            <i class="fa-regular fa-note-sticky"></i>
            <span>ملاحظات الجلسات</span>
          </a>
        </li>
        <li>
          <a href="../activities-management/index.php" class="nav-link <?= ($activePage ?? '') === 'activities' ? 'active' : '' ?>">
            <i class="fa-solid fa-wave-square"></i>
            <span>إدارة الأنشطة</span>
          </a>
        </li>
        <li>
          <a href="../review-page/index.php" class="nav-link <?= ($activePage ?? '') === 'review' ? 'active' : '' ?>">
            <i class="fa-solid fa-square-check"></i>
            <span>مراجعة نتائج الاختبار</span>
          </a>
        </li>
        <li>
          <a href="../bell-page/index.php" class="nav-link <?= ($activePage ?? '') === 'notifications' ? 'active' : '' ?>">
            <i class="fa-solid fa-bell"></i>
            <span>التنبيهات</span>
            <span class="unread-badge global-notif-badge" style="display:none;"></span>
          </a>
        </li>
        <li>
          <a href="../messages/index.php" class="nav-link <?= ($activePage ?? '') === 'messages' ? 'active' : '' ?>">
            <i class="fa-solid fa-message"></i>
            <span>المحادثات</span>
            <span class="unread-badge global-msg-badge" style="display:none;"></span>
          </a>
        </li>
         <li>
          <a href="../profile/index.php" class="nav-link <?= ($activePage ?? '') === 'profile' ? 'active' : '' ?>">
            <i class="fa-solid fa-user"></i>
            <span>الملف الشخصي</span>
          </a>
        </li>
      </ul>
    </nav>

    <div class="sidebar-footer">
            <button class="logout-btn">
                 <a href="/That-Copy/Public/handlers/logout.php">
                   <img src="./img/Link.png">
                 </a>
            </button>
        </div>


  </aside>

<script src="/That-Copy/Public/js/realtime-badges.js"></script>
