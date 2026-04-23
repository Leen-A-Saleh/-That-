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
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'send_message') {
            $receiver = (int) ($_POST['receiver_id'] ?? 0);
            $content  = trim($_POST['content'] ?? '');
            if ($receiver <= 0 || $content === '') {
                echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة']);
                exit;
            }

            $stmt = $conn->prepare("
                SELECT case_id FROM cases
                WHERE therapist_id = ? AND client_id = ?
                ORDER BY last_updated DESC LIMIT 1
            ");
            $stmt->bind_param("ii", $therapist_id, $receiver);
            $stmt->execute();
            $case = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $case_id = $case['case_id'] ?? null;

            $stmt = $conn->prepare("
                INSERT INTO messages (sender_id, receiver_id, case_id, content, type, is_read)
                VALUES (?, ?, ?, ?, 'TEXT', 0)
            ");
            $stmt->bind_param("iiis", $user_id, $receiver, $case_id, $content);
            $stmt->execute();
            $mid = $stmt->insert_id;
            $stmt->close();

            echo json_encode([
                'success'    => true,
                'message_id' => $mid,
                'sent_at'    => date('H:i')
            ]);
            exit;
        }

        if ($action === 'send_file') {
            $receiver = (int) ($_POST['receiver_id'] ?? 0);
            if ($receiver <= 0 || empty($_FILES['file']['tmp_name'])) {
                echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة']);
                exit;
            }

            $origName = $_FILES['file']['name'];
            $size     = (int) $_FILES['file']['size'];
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if ($size > 10 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'حجم الملف يتجاوز 10 ميجا']);
                exit;
            }

            $imageExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $allowed   = array_merge($imageExts, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'mp3', 'mp4', 'wav', 'zip']);
            if (!in_array($ext, $allowed, true)) {
                echo json_encode(['success' => false, 'message' => 'نوع الملف غير مدعوم']);
                exit;
            }

            $type = in_array($ext, $imageExts, true) ? 'IMAGE' : 'FILE';
            $dir  = __DIR__ . '/../../uploads/messages/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);

            $safeBase = preg_replace('/[^A-Za-z0-9._\-\x{0621}-\x{064A}]+/u', '_', pathinfo($origName, PATHINFO_FILENAME));
            $filename = 'msg_' . $user_id . '_' . time() . '_' . $safeBase . '.' . $ext;

            if (!move_uploaded_file($_FILES['file']['tmp_name'], $dir . $filename)) {
                echo json_encode(['success' => false, 'message' => 'تعذّر حفظ الملف']);
                exit;
            }

            $path = '/uploads/messages/' . $filename;

            $stmt = $conn->prepare("
                SELECT case_id FROM cases
                WHERE therapist_id = ? AND client_id = ?
                ORDER BY last_updated DESC LIMIT 1
            ");
            $stmt->bind_param("ii", $therapist_id, $receiver);
            $stmt->execute();
            $case_id = $stmt->get_result()->fetch_assoc()['case_id'] ?? null;
            $stmt->close();

            $stmt = $conn->prepare("
                INSERT INTO messages (sender_id, receiver_id, case_id, content, type, file_path, is_read)
                VALUES (?, ?, ?, ?, ?, ?, 0)
            ");
            $stmt->bind_param("iiisss", $user_id, $receiver, $case_id, $origName, $type, $path);
            $stmt->execute();
            $mid = $stmt->insert_id;
            $stmt->close();

            echo json_encode([
                'success'    => true,
                'message_id' => $mid,
                'sent_at'    => date('H:i'),
                'type'       => $type,
                'file_path'  => $path,
                'file_name'  => $origName,
            ]);
            exit;
        }

        if ($action === 'mark_read') {
            $other = (int) ($_POST['client_id'] ?? 0);
            if ($other <= 0) {
                echo json_encode(['success' => false]);
                exit;
            }
            $stmt = $conn->prepare("
                UPDATE messages SET is_read = 1
                WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
            ");
            $stmt->bind_param("ii", $other, $user_id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['success' => true]);
            exit;
        }
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'تعذّر تنفيذ الطلب']);
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
    exit;
}

$stmt = $conn->prepare("
    SELECT u.user_id, u.name,
           last_msg.content   AS last_content,
           last_msg.type      AS last_type,
           last_msg.sent_at   AS last_sent_at,
           unread.cnt         AS unread_count
    FROM (
        SELECT DISTINCT
            CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS other_id
        FROM messages
        WHERE sender_id = ? OR receiver_id = ?
    ) conv
    JOIN users u ON u.user_id = conv.other_id AND u.role = 'CLIENT'
    LEFT JOIN (
        SELECT m1.*
        FROM messages m1
        INNER JOIN (
            SELECT
                LEAST(sender_id, receiver_id)    AS a,
                GREATEST(sender_id, receiver_id) AS b,
                MAX(sent_at) AS max_sent
            FROM messages
            WHERE sender_id = ? OR receiver_id = ?
            GROUP BY a, b
        ) m2
          ON LEAST(m1.sender_id, m1.receiver_id)    = m2.a
         AND GREATEST(m1.sender_id, m1.receiver_id) = m2.b
         AND m1.sent_at = m2.max_sent
    ) last_msg
      ON (last_msg.sender_id = conv.other_id AND last_msg.receiver_id = ?)
      OR (last_msg.sender_id = ? AND last_msg.receiver_id = conv.other_id)
    LEFT JOIN (
        SELECT sender_id AS other_id, COUNT(*) AS cnt
        FROM messages
        WHERE receiver_id = ? AND is_read = 0
        GROUP BY sender_id
    ) unread ON unread.other_id = conv.other_id
    ORDER BY last_msg.sent_at DESC
");
$stmt->bind_param("iiiiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$convRes = $stmt->get_result();
$conversations = [];
while ($row = $convRes->fetch_assoc()) {
    $conversations[] = $row;
}
$stmt->close();

$selected_id = isset($_GET['client']) ? (int)$_GET['client'] : null;
if (!$selected_id && !empty($conversations)) {
    $selected_id = (int) $conversations[0]['user_id'];
}

$selected_user = null;
$messages      = [];
if ($selected_id) {
    $stmt = $conn->prepare("SELECT user_id, name FROM users WHERE user_id = ? AND role = 'CLIENT'");
    $stmt->bind_param("i", $selected_id);
    $stmt->execute();
    $selected_user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($selected_user) {
        $stmt = $conn->prepare("
            UPDATE messages SET is_read = 1
            WHERE sender_id = ? AND receiver_id = ? AND is_read = 0
        ");
        $stmt->bind_param("ii", $selected_id, $user_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("
            SELECT message_id, sender_id, receiver_id, content, type, file_path, sent_at
            FROM messages
            WHERE (sender_id = ? AND receiver_id = ?)
               OR (sender_id = ? AND receiver_id = ?)
            ORDER BY sent_at ASC
        ");
        $stmt->bind_param("iiii", $user_id, $selected_id, $selected_id, $user_id);
        $stmt->execute();
        $msgRes = $stmt->get_result();
        while ($row = $msgRes->fetch_assoc()) {
            $messages[] = $row;
        }
        $stmt->close();
    }
}

$stmt = $conn->prepare("
    SELECT
        COUNT(DISTINCT CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END) AS total_conv,
        SUM(CASE WHEN receiver_id = ? AND is_read = 0 THEN 1 ELSE 0 END) AS unread_msgs,
        COUNT(DISTINCT CASE
            WHEN sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            THEN (CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END)
        END) AS active_conv
    FROM messages
    WHERE sender_id = ? OR receiver_id = ?
");
$stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

$total_conv  = (int) ($stats['total_conv']  ?? 0);
$unread_msgs = (int) ($stats['unread_msgs'] ?? 0);
$active_conv = (int) ($stats['active_conv'] ?? 0);

$waiting_count = 0;
foreach ($conversations as $c) {
    if ((int)($c['unread_count'] ?? 0) > 0) $waiting_count++;
}

function initials($name) {
    $parts = preg_split('/\s+/', trim($name));
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        if ($p !== '') $out .= mb_substr($p, 0, 1, 'UTF-8') . ' ';
    }
    return trim($out);
}

function msgTime($datetime) {
    $ts = strtotime($datetime);
    if (date('Y-m-d', $ts) === date('Y-m-d')) {
        return date('H:i', $ts);
    }
    $daysAgo = floor((time() - $ts) / 86400);
    if ($daysAgo === 1.0) return 'أمس';
    if ($daysAgo < 7)     return date('H:i', $ts);
    return date('Y/m/d', $ts);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <title>المحادثات</title>
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

    <?php $activePage = 'messages'; ?>
    <?php include "../layouts/sidebar.php"; ?>

    <main class="main">
      <header class="main-header">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="فتح القائمة">
          <i class="fa-solid fa-bars"></i>
        </button>
        <h1 class="page-title">المحادثات</h1>
        <button class="status-btn">
          <span class="status-dot"></span>
          متصل
        </button>
      </header>

      <section class="stats-row">
        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">إجمالي المحادثات</div>
            <div class="stat-value"><?= $total_conv ?></div>
          </div>
          <div class="stat-icon-teal"><i class="fa-solid fa-message"></i></div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">رسائل غير مقروءة</div>
            <div class="stat-value"><?= $unread_msgs ?></div>
          </div>
          <div class="stat-icon-orange"><i class="fa-solid fa-message"></i></div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">محادثات نشطة</div>
            <div class="stat-value"><?= $active_conv ?></div>
          </div>
          <div class="stat-icon-green"><i class="fa-solid fa-message"></i></div>
        </div>

        <div class="stat-card">
          <div class="stat-info">
            <div class="stat-label">طلبات الانتظار</div>
            <div class="stat-value"><?= $waiting_count ?></div>
          </div>
          <div class="stat-icon-blue"><i class="fa-solid fa-message"></i></div>
        </div>
      </section>

      <div class="chat-container">
        <div class="chat-list">
          <div class="chat-search">
            <input type="text" id="chatSearch" placeholder="بحث عن محادثة..." />
          </div>

          <div class="chat-users">
            <?php if (empty($conversations)): ?>
              <p class="no-conv">لا توجد محادثات</p>
            <?php else: foreach ($conversations as $c):
              $isActive = $selected_id === (int)$c['user_id'];
              $unread   = (int)($c['unread_count'] ?? 0);
            ?>
            <a href="?client=<?= $c['user_id'] ?>"
               class="chat-user <?= $isActive ? 'active' : '' ?>"
               data-name="<?= htmlspecialchars($c['name']) ?>">
              <div class="avatar"><?= htmlspecialchars(initials($c['name'])) ?></div>
              <div class="chat-user-text">
                <h4>
                  <?= htmlspecialchars($c['name']) ?>
                  <?php if ($unread > 0): ?>
                    <span class="unread-badge"><?= $unread ?></span>
                  <?php endif; ?>
                </h4>
                <p>
                  <?php if ($c['last_type'] === 'IMAGE'): ?>
                    <i class="fa-regular fa-image"></i> صورة
                  <?php elseif ($c['last_type'] === 'FILE'): ?>
                    <i class="fa-solid fa-paperclip"></i> ملف
                  <?php else: ?>
                    <?= htmlspecialchars(mb_substr($c['last_content'] ?? '', 0, 60)) ?>
                  <?php endif; ?>
                </p>
              </div>
            </a>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <div class="chat-box">
          <?php if ($selected_user): ?>
            <div class="chat-header">
              <div class="chat-user-info">
                <div class="chat-avatar">
                  <span class="avatar-initials"><?= htmlspecialchars(initials($selected_user['name'])) ?></span>
                  <span class="avatar-status"></span>
                </div>
                <div class="chat-user-details">
                  <h3 class="chat-user-name"><?= htmlspecialchars($selected_user['name']) ?></h3>
                  <p class="chat-user-status">متصل الآن</p>
                </div>
              </div>

              <div class="chat-actions">
                <button class="chat-action-btn favorite-btn" title="المفضلة">
                  <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                </button>
                <button class="chat-action-btn video-btn" title="مكالمة فيديو">
                  <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
                </button>
                <button class="chat-action-btn call-btn" title="مكالمة صوتية">
                  <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.49-5.15-3.8-6.62-6.63l1.97-1.57c.26-.27.36-.66.25-1.01-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3.3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .7-.75.7-1.2v-3.44c0-.54-.45-.98-.99-.98z"/></svg>
                </button>
                <button class="chat-action-btn menu-btn" title="المزيد" id="menuBtn">
                  <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                </button>
              </div>

              <div class="dropdown-menu" id="dropdownMenu">
                <ul>
                  <li><a href="#" class="dropdown-item">عرض الملف الشخصي</a></li>
                  <li><a href="#" class="dropdown-item">البحث في المحادثة</a></li>
                  <li><a href="#" class="dropdown-item">كتم الإشعارات</a></li>
                  <li><a href="#" class="dropdown-item">حذف المحادثة</a></li>
                </ul>
              </div>
            </div>

            <div class="chat-messages" id="chatMessages" data-receiver="<?= $selected_user['user_id'] ?>">
              <?php if (empty($messages)): ?>
                <p class="no-messages">ابدأ المحادثة بإرسال رسالة</p>
              <?php else: foreach ($messages as $m):
                $mine = (int)$m['sender_id'] === $user_id;
              ?>
                <div class="message <?= $mine ? 'me' : 'other' ?>">
                  <?php if ($m['type'] === 'IMAGE' && !empty($m['file_path'])): ?>
                    <a href="<?= htmlspecialchars($m['file_path']) ?>" target="_blank" class="msg-image-link">
                      <img src="<?= htmlspecialchars($m['file_path']) ?>" alt="<?= htmlspecialchars($m['content']) ?>" class="msg-image">
                    </a>
                  <?php elseif ($m['type'] === 'FILE' && !empty($m['file_path'])): ?>
                    <a href="<?= htmlspecialchars($m['file_path']) ?>" download class="msg-file">
                      <i class="fa-solid fa-file"></i>
                      <span><?= htmlspecialchars($m['content']) ?></span>
                    </a>
                  <?php else: ?>
                    <?= nl2br(htmlspecialchars($m['content'])) ?>
                  <?php endif; ?>
                  <span><?= msgTime($m['sent_at']) ?></span>
                </div>
              <?php endforeach; endif; ?>
            </div>

            <div class="chat-input">
              <button class="emoji" type="button">
                <i class="fa-sharp fa-solid fa-face-smile"></i>
              </button>

              <label class="file-btn">
                <i class="fa-solid fa-link"></i>
                <input type="file" hidden id="fileInput" />
              </label>

              <input type="text" id="messageInput" placeholder="اكتب رسالتك ..." />

              <button id="sendBtn" type="button">
                <i class="fa fa-paper-plane"></i>
              </button>
            </div>
          <?php else: ?>
            <div class="empty-chat">
              <i class="fa-regular fa-comments"></i>
              <p>اختر محادثة من القائمة لعرض الرسائل</p>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <?php include "../layouts/footer.php"; ?>
    </main>
  </div>

  <script src="main.js"></script>
  <script src="../sidebar-toggle.js"></script>
</body>
</html>
