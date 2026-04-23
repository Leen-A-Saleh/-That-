<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'THERAPIST') {
    header("Location: /homepage/index.php");
    exit;
}

require_once __DIR__ . '/../../connection.php';

$user_id      = $_SESSION['user_id'];
$therapist_id = $user_id;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
           && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($action === 'upload_avatar') {
        $ok = false;
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $ext  = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (in_array($ext, $allowed) && $_FILES['avatar']['size'] <= 2 * 1024 * 1024) {
                $dir = __DIR__ . '/../../uploads/avatars/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $filename = 'avatar_' . $therapist_id . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . $filename)) {
                    $path = '/uploads/avatars/' . $filename;
                    $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $path, $user_id);
                    $stmt->execute();
                    $stmt->close();
                    $ok = true;
                    $_SESSION['avatar'] = $path;
                }
            }
        }
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $ok, 'path' => $path ?? '']);
            exit;
        }
        header("Location: index.php");
        exit;
    }

    if ($action === 'edit_profile') {
        $name           = trim($_POST['name'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        $bio            = trim($_POST['bio'] ?? '');
        $experience     = (int) ($_POST['experience_years'] ?? 0);

        if ($name !== '') {
            $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $name, $phone, $user_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE therapists SET specialization = ?, bio = ?, experience_years = ? WHERE therapist_id = ?");
            $stmt->bind_param("ssii", $specialization, $bio, $experience, $therapist_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['user_name'] = $name;
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        header("Location: index.php");
        exit;
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $ok = false;
        $msg = '';

        if ($new !== $confirm) {
            $msg = 'كلمة المرور الجديدة غير متطابقة';
        } elseif (strlen($new) < 6) {
            $msg = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($row && password_verify($current, $row['password'])) {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmt->bind_param("si", $hash, $user_id);
                $stmt->execute();
                $stmt->close();
                $ok = true;
                $msg = 'تم تغيير كلمة المرور بنجاح';
            } else {
                $msg = 'كلمة المرور الحالية غير صحيحة';
            }
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $ok, 'message' => $msg]);
            exit;
        }
        header("Location: index.php");
        exit;
    }

    if ($action === 'save_schedule') {
        $slots = json_decode($_POST['slots'] ?? '[]', true);

        $stmt = $conn->prepare("DELETE FROM therapist_availability WHERE therapist_id = ?");
        $stmt->bind_param("i", $therapist_id);
        $stmt->execute();
        $stmt->close();

        if (!empty($slots)) {
            $stmt = $conn->prepare("INSERT INTO therapist_availability (therapist_id, day_of_week, start_time, end_time, is_active) VALUES (?, ?, ?, ?, 1)");
            foreach ($slots as $slot) {
                $day   = $slot['day'];
                $start = $slot['start'];
                $end   = $slot['end'];
                $stmt->bind_param("isss", $therapist_id, $day, $start, $end);
                $stmt->execute();
            }
            $stmt->close();
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        header("Location: index.php");
        exit;
    }
}

$stmt = $conn->prepare("
    SELECT u.name, u.email, u.phone, u.avatar,
           t.specialization, t.bio, t.certification,
           t.experience_years, t.rating, t.rating_count
    FROM users u
    JOIN therapists t ON t.therapist_id = u.user_id
    WHERE u.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$profile) {
    echo '<p>لم يتم العثور على الملف الشخصي</p>';
    exit;
}

$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM appointments WHERE therapist_id = ? AND status = 'CONFIRMED'");
$stmt->bind_param("i", $therapist_id);
$stmt->execute();
$active_sessions = $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(DISTINCT client_id) AS cnt FROM cases WHERE therapist_id = ?");
$stmt->bind_param("i", $therapist_id);
$stmt->execute();
$total_patients = $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

$stmt = $conn->prepare("
    SELECT
        COUNT(CASE WHEN status = 'RECOVERED' THEN 1 END) AS recovered,
        COUNT(*) AS total
    FROM cases
    WHERE therapist_id = ? AND status != 'NEW'
");
$stmt->bind_param("i", $therapist_id);
$stmt->execute();
$sr = $stmt->get_result()->fetch_assoc();
$stmt->close();
$success_rate = ($sr['total'] > 0) ? round(($sr['recovered'] / $sr['total']) * 100) : 0;

$dayOrder = ['SUNDAY','MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY'];
$dayNames = [
    'SUNDAY'    => 'الأحد',
    'MONDAY'    => 'الإثنين',
    'TUESDAY'   => 'الثلاثاء',
    'WEDNESDAY' => 'الأربعاء',
    'THURSDAY'  => 'الخميس',
    'FRIDAY'    => 'الجمعة',
    'SATURDAY'  => 'السبت',
];

$stmt = $conn->prepare("
    SELECT day_of_week, start_time, end_time
    FROM therapist_availability
    WHERE therapist_id = ? AND is_active = 1
    ORDER BY FIELD(day_of_week, 'SUNDAY','MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY'), start_time
");
$stmt->bind_param("i", $therapist_id);
$stmt->execute();
$availRes = $stmt->get_result();
$schedule = [];
foreach ($dayOrder as $d) $schedule[$d] = [];
while ($row = $availRes->fetch_assoc()) {
    $schedule[$row['day_of_week']][] = $row;
}
$stmt->close();

$stmt = $conn->prepare("
    SELECT r.rating, r.comment, r.created_at, u.name AS reviewer_name
    FROM therapist_reviews r
    JOIN users u ON u.user_id = r.client_id
    WHERE r.therapist_id = ?
    ORDER BY r.created_at DESC
    LIMIT 10
");
$stmt->bind_param("i", $therapist_id);
$stmt->execute();
$reviewsRes = $stmt->get_result();
$reviews = [];
while ($row = $reviewsRes->fetch_assoc()) {
    $reviews[] = $row;
}
$stmt->close();

$ratingDist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
if ($profile['rating_count'] > 0) {
    $stmt = $conn->prepare("
        SELECT rating, COUNT(*) AS cnt
        FROM therapist_reviews
        WHERE therapist_id = ?
        GROUP BY rating
    ");
    $stmt->bind_param("i", $therapist_id);
    $stmt->execute();
    $distRes = $stmt->get_result();
    while ($row = $distRes->fetch_assoc()) {
        $ratingDist[(int)$row['rating']] = (int)$row['cnt'];
    }
    $stmt->close();
}
$totalReviews = array_sum($ratingDist);

$initial = mb_substr($profile['name'], 0, 1, 'UTF-8');
function timeAgo($datetime) {
    $now  = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);
    if ($diff->y > 0) return 'منذ ' . $diff->y . ' سنة';
    if ($diff->m > 0) return 'منذ ' . $diff->m . ' شهر';
    if ($diff->d >= 14) return 'منذ ' . floor($diff->d / 7) . ' أسبوع';
    if ($diff->d >= 7) return 'منذ أسبوع';
    if ($diff->d > 0) return 'منذ ' . $diff->d . ' يوم';
    if ($diff->h > 0) return 'منذ ' . $diff->h . ' ساعة';
    return 'منذ قليل';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <title>الملف الشخصي - <?= htmlspecialchars($profile['name']) ?></title>
  <link rel="icon" type="image/png" href="img/Silver.png">
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="../thirapist.css">
  <link rel="stylesheet" href="../total.css" />
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <div class="app">

    <?php $activePage = 'profile'; ?>
    <?php include "../layouts/sidebar.php"; ?>

    <main class="main">
      <header class="main-header">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="فتح القائمة">
          <i class="fa-solid fa-bars"></i>
        </button>
        <h1 class="page-title">الملف الشخصي</h1>
        <button class="status-btn">
          <span class="status-dot"></span>
          متصل
        </button>
      </header>

      <!-- ── Hero Section ── -->
      <section class="profile-hero dash-card">
        <div class="profile-hero-main">
          <div class="profile-hero-info">
            <div class="profile-name-row">
              <h2 class="doctor-name"><?= htmlspecialchars($profile['name']) ?></h2>
            </div>

            <div class="doctor-meta-row">
              <div class="meta-item">
                <i class="fa-regular fa-clock"></i>
                <span><?= htmlspecialchars($profile['specialization']) ?></span>
              </div>
              <div class="meta-item">
                <i class="fa-solid fa-globe"></i>
                <span><?= (int)$profile['experience_years'] ?> سنوات خبرة</span>
              </div>

              <div class="doctor-rating">
                <span class="stars">
                  <i class="fa-solid fa-star"></i>
                </span>
                <span class="rating-value"><?= number_format((float)$profile['rating'], 1) ?></span>
                <span class="rating-count">(<?= (int)$profile['rating_count'] ?> تقييم)</span>
              </div>
            </div>
          </div>

          <div class="profile-hero-avatar">
            <div class="avatar-circle-lg">
              <span class="avatar-initial" <?= $profile['avatar'] ? 'style="display:none"' : '' ?>><?= $initial ?></span>
              <img id="avatarImage" src="<?= $profile['avatar'] ? htmlspecialchars($profile['avatar']) : '' ?>" alt="صورة المعالج" <?= $profile['avatar'] ? 'style="display:block"' : '' ?> />
              <button type="button" class="avatar-change-btn">
                <i class="fa-solid fa-camera"></i>
              </button>
            </div>
            <input type="file" id="avatarInput" accept="image/*" hidden>
          </div>
        </div>

        <div class="profile-hero-bio">
          <h3 class="bio-title">نبذة عني</h3>
          <p class="bio-text">
            <?= nl2br(htmlspecialchars($profile['bio'])) ?>
          </p>
        </div>
      </section>

      <!-- ── Stats Cards ── -->
      <section class="profile-stats-row">
        <div class="profile-stat-card">
          <div class="stat-text">
            <div class="stat-label">سنوات الخبرة</div>
            <div class="stat-value"><?= (int)$profile['experience_years'] ?> سنوات</div>
          </div>
          <div class="stat-icon">
            <img src="./img/خبرات .png" alt="سنوات الخبرة">
          </div>
        </div>

        <div class="profile-stat-card">
          <div class="stat-text">
            <div class="stat-label">الجلسات النشطة</div>
            <div class="stat-value"><?= $active_sessions ?></div>
          </div>
          <div class="stat-icon"><img src="./img/جلسات الانشطة.png" alt="الجلسات النشطة"></div>
        </div>

        <div class="profile-stat-card">
          <div class="stat-text">
            <div class="stat-label">اجمالي المرضى</div>
            <div class="stat-value"><?= $total_patients ?></div>
          </div>
          <div class="stat-icon"><img src="./img/اجمالي المرضى.png" alt="اجمالي المرضى"></div>
        </div>

        <div class="profile-stat-card">
          <div class="stat-text">
            <div class="stat-label">معدل النجاح</div>
            <div class="stat-value"><?= $success_rate ?>%</div>
          </div>
          <div class="stat-icon"><img src="./img/معدل النجاح .png" alt="معدل النجاح"></div>
        </div>
      </section>

      <!-- ── Contact Info + Change Password ── -->
      <section class="profile-middle-row">
        <section class="dash-card contact-card">
          <header class="card-header">
            <h3>معلومات الاتصال</h3>
            <button class="link-btn" id="editProfileBtn">تعديل</button>
          </header>

          <div class="contact-grid">
            <div class="contact-item">
              <img class="img" src="./img/البريد الاكتروني.png" alt="البريد الإلكتروني">
              <div class="label">البريد الإلكتروني</div>
              <div class="value" id="displayEmail"><?= htmlspecialchars($profile['email']) ?></div>
            </div>
            <div class="contact-item">
              <img class="img" src="./img/رقم الهاتف.png" alt="رقم الهاتف">
              <div class="label">رقم الهاتف</div>
              <div class="value" id="displayPhone"><?= htmlspecialchars($profile['phone'] ?? '-') ?></div>
            </div>
            <div class="contact-item">
              <img class="img" src="./img/التخصص.png" alt="التخصص">
              <div class="label">التخصص</div>
              <div class="value" id="displaySpec"><?= htmlspecialchars($profile['specialization']) ?></div>
            </div>
            <div class="contact-item">
              <img class="img" src="./img/سنوات الخبرة.png" alt="سنوات الخبرة">
              <div class="label">سنوات الخبرة</div>
              <div class="value" id="displayExp"><?= (int)$profile['experience_years'] ?> سنوات</div>
            </div>
          </div>
        </section>

        <section class="dash-card plan-card">
          <header class="card-header">
            <h3>تغيير كلمة المرور</h3>
          </header>

          <form id="passwordForm" class="password-form">
            <input type="hidden" name="action" value="change_password">
            <div class="form-group">
              <label>كلمة المرور الحالية</label>
              <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
              <label>كلمة المرور الجديدة</label>
              <input type="password" name="new_password" required minlength="6">
            </div>
            <div class="form-group">
              <label>تأكيد كلمة المرور</label>
              <input type="password" name="confirm_password" required minlength="6">
            </div>
            <button type="submit" class="btn-secondary">تغيير كلمة المرور</button>
            <div id="passwordMsg" class="form-msg"></div>
          </form>
        </section>
      </section>

      <!-- ── Weekly Schedule ── -->
      <section class="dash-card schedule-card">
        <header class="card-header schedule-header">
          <div>
            <h3>جدول المواعيد</h3>
            <p class="schedule-sub">قم بإدارة الأوقات المتاحة للحجز</p>
          </div>
          <button id="editScheduleBtn" class="btn-secondary">
            <i class="fa-regular fa-pen-to-square"></i>
            تعديل الجدول
          </button>
        </header>

        <div id="scheduleContainer">
          <?php foreach ($dayOrder as $day):
            $slots   = $schedule[$day];
            $isOff   = empty($slots);
            $offDays = ['FRIDAY', 'SATURDAY'];
          ?>
          <div class="schedule-day <?= ($isOff && in_array($day, $offDays)) ? 'off-day' : '' ?>"
               data-day="<?= $day ?>">
            <div class="day-name">
              <i class="fa-solid fa-calendar-days"></i>
              <span><?= $dayNames[$day] ?></span>
              <?php if ($isOff && in_array($day, $offDays)): ?>
                <span class="off-note">(يوم عطلة)</span>
              <?php endif; ?>
            </div>
            <div class="day-slots">
              <?php if (!empty($slots)): ?>
                <?php foreach ($slots as $slot):
                  $st = substr($slot['start_time'], 0, 5);
                  $et = substr($slot['end_time'], 0, 5);
                ?>
                <span class="slot-pill" data-start="<?= $st ?>" data-end="<?= $et ?>">
                  <i class="fa-regular fa-clock"></i>
                  <?= $st ?> - <?= $et ?>
                </span>
                <?php endforeach; ?>
              <?php else: ?>
                <span class="no-slots">لا توجد مواعيد متاحة</span>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- ── Reviews ── -->
      <section class="dash-card reviews-card">
        <header class="card-header reviews-header">
          <h3>التقييمات</h3>
          <div class="reviews-summary">
            <span class="stars big">
              <?php
              $fullStars = floor((float)$profile['rating']);
              $half = ((float)$profile['rating'] - $fullStars) >= 0.5;
              for ($i = 0; $i < $fullStars; $i++) echo '<i class="fa-solid fa-star"></i>';
              if ($half) echo '<i class="fa-solid fa-star-half-stroke"></i>';
              for ($i = $fullStars + ($half ? 1 : 0); $i < 5; $i++) echo '<i class="fa-regular fa-star"></i>';
              ?>
            </span>
            <span class="total-rating"><?= number_format((float)$profile['rating'], 1) ?></span>
            <span class="reviews-count">(<?= (int)$profile['rating_count'] ?> تقييم)</span>
          </div>
        </header>

        <div class="reviews-body">
          <div class="rating-bars">
            <?php for ($star = 5; $star >= 1; $star--):
              $pct = $totalReviews > 0 ? round(($ratingDist[$star] / $totalReviews) * 100) : 0;
              $label = $star === 1 ? 'نجمة' : 'نجوم';
            ?>
            <div class="rating-row">
              <span class="rating-percent"><?= $star ?> <?= $label ?></span>
              <div class="rating-bar">
                <div class="rating-fill" style="width: <?= $pct ?>%"></div>
              </div>
              <span class="rating-label"><?= $pct ?>%</span>
            </div>
            <?php endfor; ?>
          </div>

          <div class="reviews-list">
            <?php if (empty($reviews)): ?>
              <p class="no-reviews-text">لا توجد تقييمات حتى الآن</p>
            <?php else: ?>
              <?php foreach ($reviews as $rev):
                $revInitial = mb_substr($rev['reviewer_name'], 0, 1, 'UTF-8');
                $fullStarsRev = (int)$rev['rating'];
              ?>
              <article class="review-item">
                <div class="review-header">
                  <div class="review-header-main">
                    <div class="reviewer-name-row">
                      <span class="reviewer-name"><?= htmlspecialchars($rev['reviewer_name']) ?></span>
                      <div class="case-avatar"><?= $revInitial ?></div>
                    </div>
                    <span class="review-stars">
                      <?php for ($i = 0; $i < $fullStarsRev; $i++) echo '<i class="fa-solid fa-star"></i>'; ?>
                      <?php for ($i = $fullStarsRev; $i < 5; $i++) echo '<i class="fa-regular fa-star"></i>'; ?>
                    </span>
                  </div>
                  <div class="review-time"><?= timeAgo($rev['created_at']) ?></div>
                </div>
                <?php if ($rev['comment']): ?>
                  <p class="review-text"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                <?php endif; ?>
              </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <!-- ── Edit Profile Modal ── -->
      <div class="modal-overlay" id="editProfileModal">
        <div class="modal-card">
          <div class="modal-header">
            <h3>تعديل الملف الشخصي</h3>
            <button class="modal-close" data-close>&times;</button>
          </div>
          <form id="editProfileForm">
            <input type="hidden" name="action" value="edit_profile">
            <div class="form-grid">
              <div class="form-group">
                <label>الاسم</label>
                <input type="text" name="name" value="<?= htmlspecialchars($profile['name']) ?>" required>
              </div>
              <div class="form-group">
                <label>رقم الهاتف</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>التخصص</label>
                <input type="text" name="specialization" value="<?= htmlspecialchars($profile['specialization']) ?>">
              </div>
              <div class="form-group">
                <label>سنوات الخبرة</label>
                <input type="number" name="experience_years" value="<?= (int)$profile['experience_years'] ?>" min="0">
              </div>
              <div class="form-group full-width">
                <label>نبذة عني</label>
                <textarea name="bio" rows="3"><?= htmlspecialchars($profile['bio']) ?></textarea>
              </div>
            </div>
            <div class="modal-actions">
              <button type="submit" class="btn-secondary">حفظ التعديلات</button>
              <button type="button" class="btn-cancel" data-close>إلغاء</button>
            </div>
          </form>
        </div>
      </div>

      <footer class="main-footer">
        &copy; 2026 ذات للاستشارات النفسية جميع الحقوق محفوظة.
      </footer>

    </main>
  </div>
  <script src="main.js"></script>
  <script src="../sidebar-toggle.js"></script>
</body>

</html>
