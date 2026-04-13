  <!-- Sidebar overlay -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <a href="../../homepage/index.php">
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
          <a href="../thirapest-request-page/thirapest-request.html" class="nav-link <?= ($activePage ?? '') === 'requests' ? 'active' : '' ?>">
            <i class="fa-regular fa-bell"></i>
            <span>طلبات المواعيد</span>
          </a>
        </li>
        <li>
          <a href="../thirapest-appointment-page/thirapest-appointment.html" class="nav-link <?= ($activePage ?? '') === 'appointments' ? 'active' : '' ?>">
            <i class="fa-regular fa-calendar-days"></i>
            <span>مواعيدي</span>
          </a>
        </li>
        <li>
          <a href="../thirapest-nots-page/thirapest-nots.html" class="nav-link <?= ($activePage ?? '') === 'notes' ? 'active' : '' ?>">
            <i class="fa-regular fa-note-sticky"></i>
            <span>ملاحظات الجلسات</span>
          </a>
        </li>
         <li>
          <a href="../thirapest-personal-page/thirapest-personal.html" class="nav-link <?= ($activePage ?? '') === 'profile' ? 'active' : '' ?>">
            <i class="fa-solid fa-user"></i>
            <span>الملف الشخصي</span>
          </a>
        </li>
      </ul>
    </nav>

    <div class="sidebar-footer">
            <button class="logout-btn">
                 <a href="../../auth/handlers/logout.php" >
                   <img src="./img/Link.png">
                 </a>
            </button>
        </div>


  </aside>

