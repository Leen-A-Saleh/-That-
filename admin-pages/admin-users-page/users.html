<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>المستخدمين</title>
    <link
      href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap"
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
    <link rel="stylesheet" href="./users.css" />
  </head>
  <body>
    <aside class="sidebar">
      <div class="logo"><img src="../images/Frame 393.svg" /></div>
      <div class="user-card">
        <div class="user-info">
          <div class="user-greeting">مرحباً،</div>
          <div class="user-name">المدير العام</div>
          <div class="user-role">لوحة تحكم الإدارة</div>
        </div>
      </div>
      <ul>
        <li>
          <a href="../admin-dashboard-page/admin-dashboard.html"
            ><img src="../images/Icon.svg" />لوحة التحكم</a
          >
        </li>
        <li class="active">
          <a href="./users.html"
            ><img src="../images/Icon (1).svg" />المستخدمين/المرضى</a
          >
        </li>
        <li>
          <a href="../admin-therapist-page/therapist.html"
            ><img src="../images/Icon (2).svg" />الأخصائيين</a
          >
        </li>
        <li>
          <a href="../admin-cases-page/cases.html"
            ><img src="../images/Icon (3).svg" />إدارة الحالات</a
          >
        </li>
        <li>
          <a href="../admin-appointments-page/appointments.html"
            ><img src="../images/Icon (4).svg" />المواعيد</a
          >
        </li>
        <li>
          <a href="../admin-tests-page/tests.html"
            ><img src="../images/Icon (6).svg" />الاختبارات النفسية</a
          >
        </li>
        <li>
          <a href="../admin-activities-page/activities.html"
            ><img src="../images/Icon (7).svg" />الأنشطة والألعاب</a
          >
        </li>
        <li>
          <a href="../admin-notifications-page/notifications.html"
            ><img src="../images/Icon (7).svg" />الإشعارات</a
          >
        </li>
        <li>
          <a href="../admin-profile-page/profile.html"
            ><img src="../images/Icon7.svg" />الملف الشخصي</a
          >
        </li>
      </ul>
      <button class="logout" id="logoutBtn">
        <img src="../images/Link.png" />
      </button>
    </aside>

    <div class="sidebar-overlay"></div>

    <div class="main-wrapper">
      <header class="navbar">
        <div class="menu-btn"><i class="fa fa-bars"></i></div>
        <div class="nav-title">المستخدمين/المرضى</div>
        <div class="nav-right">
          <button class="status-btn">
            <span class="status-dot"></span>متصل
          </button>
        </div>
      </header>

      <main class="page-content">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-label">إجمالي المستخدمين</div>
            <div class="stat-value" id="statTotal">0</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">المستخدمين النشطين</div>
            <div class="stat-value" id="statActive">0</div>
          </div>
          <div class="stat-card suspended">
            <div class="stat-label">الحسابات الموقوفة</div>
            <div class="stat-value" id="statSuspended">0</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">إجمالي الجلسات</div>
            <div class="stat-value" id="statSessions">0</div>
          </div>
        </div>

        <div class="users-toolbar">
          <div class="search-box">
            <i class="fa fa-search search-icon"></i>
            <input
              type="text"
              id="searchInput"
              placeholder="البحث عن مستخدم..."
            />
          </div>
          <select class="filter-select" id="statusFilter">
            <option value="all">جميع الحالات</option>
            <option value="active">نشط</option>
            <option value="suspended">موقوف</option>
          </select>
          <button class="btn-export" id="exportBtn">
            <img src="../images/Download.svg" /> تصدير
          </button>
          <button class="btn-add-user" id="addUserBtn">
            <img src="../images/UserPlus.svg" /> إضافة مستخدم
          </button>
        </div>

        <div class="users-table-wrapper">
          <table class="users-table">
            <thead>
              <tr>
                <th>الاسم</th>
                <th>البريد الإلكتروني</th>
                <th>رقم الجوال</th>
                <th>تاريخ التسجيل</th>
                <th>عدد الجلسات</th>
                <th>حالة الحساب</th>
                <th>الإجراءات</th>
              </tr>
            </thead>
            <tbody id="usersTableBody"></tbody>
          </table>
          <div class="pagination-wrapper">
            <div class="pagination-info" id="paginationInfo"></div>
            <div class="pagination-btns" id="paginationBtns"></div>
          </div>
        </div>

        <!-- Add/Edit Modal -->
        <div class="modal-overlay" id="userModal">
          <div class="modal-box">
            <h2><span></span><span id="modalTitle">إضافة مستخدم جديد</span></h2>
            <div class="modal-form-grid">
              <div class="form-group">
                <label for="fieldFirstName">الاسم الأول</label>
                <input
                  type="text"
                  id="fieldFirstName"
                  placeholder="الاسم الأول"
                />
              </div>
              <div class="form-group">
                <label for="fieldLastName">اسم العائلة</label>
                <input
                  type="text"
                  id="fieldLastName"
                  placeholder="اسم العائلة"
                />
              </div>
              <div class="form-group full-width">
                <label for="fieldEmail">البريد الإلكتروني</label>
                <input
                  type="email"
                  id="fieldEmail"
                  placeholder="example@email.com"
                />
              </div>
              <div class="form-group">
                <label for="fieldPhone">رقم الجوال</label>
                <input type="tel" id="fieldPhone" placeholder="05xxxxxxxx" />
              </div>
              <div class="form-group">
                <label for="fieldStatus">الحالة</label>
                <select id="fieldStatus">
                  <option value="active">نشط</option>
                  <option value="suspended">موقوف</option>
                </select>
              </div>
            </div>
            <div class="modal-actions">
              <button class="btn-modal-save" id="saveUserBtn">حفظ</button>
              <button class="btn-modal-cancel" id="cancelModalBtn">
                إلغاء
              </button>
            </div>
          </div>
        </div>

        <div class="modal-overlay" id="confirmModal">
          <div class="confirm-modal-box">
            <div class="confirm-icon"><i class="fa fa-trash"></i></div>
            <h3>تأكيد الحذف</h3>
            <p>
              هل أنت متأكد من حذف هذا المستخدم؟<br />لا يمكن التراجع عن هذا
              الإجراء.
            </p>
            <div class="confirm-actions">
              <button class="btn-confirm-delete" id="confirmDeleteBtn">
                حذف
              </button>
              <button class="btn-modal-cancel" id="cancelDeleteBtn">
                إلغاء
              </button>
            </div>
          </div>
        </div>

        <div id="userModalCard" class="user-modal">
          <div class="user-modal-backdrop" onclick="closeUserCard()"></div>
          <div class="user-modal-content">
            <div class="user-modal-header">
              <div class="user-modal-avatar" id="vm-avatar"></div>
              <div class="user-modal-header-info">
                <h3 id="vm-name"></h3>
                <span id="vm-status-badge"></span>
              </div>
              <button class="user-modal-close" onclick="closeUserCard()">
                <i class="fas fa-xmark"></i>
              </button>
            </div>
            <div class="user-modal-body">
              <div class="user-modal-row">
                <span class="user-modal-label"
                  ><i class="fas fa-envelope"></i>البريد الإلكتروني</span
                >
                <span class="user-modal-val" id="vm-email"></span>
              </div>
              <div class="user-modal-row">
                <span class="user-modal-label"
                  ><i class="fas fa-phone"></i>رقم الجوال</span
                >
                <span class="user-modal-val" id="vm-phone"></span>
              </div>
              <div class="user-modal-row">
                <span class="user-modal-label"
                  ><i class="fas fa-calendar-days"></i>تاريخ التسجيل</span
                >
                <span class="user-modal-val" id="vm-date"></span>
              </div>
              <div class="user-modal-row">
                <span class="user-modal-label"
                  ><i class="fas fa-layer-group"></i>عدد الجلسات</span
                >
                <span class="user-modal-val" id="vm-sessions"></span>
              </div>
            </div>
          </div>
        </div>
      </main>

      <div class="toast" id="toast"></div>
      <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>
    </div>

    <script src="./users.js"></script>
  </body>
</html>
