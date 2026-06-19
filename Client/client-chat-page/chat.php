<?php

declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/client.php';
require_once __DIR__ . '/../../Database/profile-database.php';

start_secure_session();
require_auth();
require_role(['CLIENT']);

$showNotificationDot = false;
try {
  $showNotificationDot = hasUnreadNotifications();
} catch (Throwable) {
  $showNotificationDot = false;
}

try {
  $currentClientId = client_current_user_id();
} catch (Throwable) {
  redirect('/That-Copy/Public/login/index.php');
  exit;
}
?>
<!doctype html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>الرسائل</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="../images/Silver.png" />
  <link rel="shortcut icon" href="../images/Silver.png" />
  <link rel="apple-touch-icon" href="../images/Silver.png" />
  <link rel="stylesheet" href="../client-dashboard-page/style.css" />
  <link rel="stylesheet" href="./chat.css" />
</head>

<body>
  <header class="navbar">
    <div class="menu-btn" id="menuBtn">
      <i class="fa fa-bars"></i>
    </div>
    <div class="nav-title">الرسائل</div>
    <div class="nav-right">
      <a href="../client-notifications-page/notifications.php" class="bell">
        <i class="fa-regular fa-bell"></i>
        <span class="dot" id="notificationDot" <?= $showNotificationDot ? '' : ' style="display:none;"' ?>></span>
      </a>
    </div>
  </header>

  <?php $clientSidebarActive = 'chat';
  include __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="chat-page">
    <div class="chat-container">

      <!--  Conversation list -->
      <div class="chat-list">
        <div class="chat-search">
          <input type="text" id="chatSearch" placeholder="بحث عن محادثة أو معالج..." />
        </div>

        <div class="chat-users" id="chatUsers">
        </div>
      </div>

      <!-- Message window -->
      <div class="chat-box">
        <div class="chat-header" id="chatHeader" style="display:none;">
        </div>

        <div class="chat-messages" id="chatMessages">
          <div class="empty-chat">
            <i class="fa-regular fa-comments"></i>
            <p>اختر محادثة من القائمة لعرض الرسائل</p>
          </div>
        </div>

        <div class="chat-input" id="chatInputArea" style="display:none;">
          <button type="button" class="emoji" id="emojiBtn" aria-label="إيموجي">
            <i class="fa-sharp fa-solid fa-face-smile"></i>
          </button>

          <label class="file-btn" aria-label="إرفاق ملف">
            <i class="fa-solid fa-link"></i>
            <input type="file" hidden id="fileInput" />
          </label>

          <input type="text" id="messageInput" placeholder="اكتب رسالتك هنا..." />

          <button id="sendBtn">
            <i class="fa fa-paper-plane"></i>
          </button>
        </div>
      </div>

    </div>
  </main>

  <footer>© 2026 ذات للإستشارات النفسية جميع الحقوق محفوظة</footer>

  <!-- Pass server-side client ID into JS -->
  <script>
    const CURRENT_CLIENT_ID = <?= json_encode($currentClientId) ?>;
    const CHAT_API_URL = '/That-Copy/Client/client-chat-page/chat-database.php';
  </script>
  <div class="sidebar-overlay"></div>
  <script src="./chat.js"></script>
</body>

</html>