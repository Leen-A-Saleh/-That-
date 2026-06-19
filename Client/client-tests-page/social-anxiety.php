<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/auth.php';
start_secure_session();
require_auth();
require_role(['CLIENT']);
require_once __DIR__ . '/tests-database.php';
tests_start_assessment_result(6, (int) current_user()['user_id']);
?>
<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مقياس القلق الإجتماعي</title>
    <link rel="stylesheet" href="./assets/css/test-page.css" />
  </head>
  <body>
    <div class="test-container">
      <h1>مقياس القلق الإجتماعي</h1>
      <p class="test-desc">
        فيما يلي مجموعة من الفقرات التي تصف مشاعر الفرد، يرجى قراءة كل فقرة ووضع
        إشارة في الخيار المناسب.
      </p>

      <div class="progress">
        <div id="progressBar"></div>
      </div>

      <h3 id="sectionTitle"></h3>
      <div id="questionContainer"></div>
    </div>

    <script>window.ASSESSMENT_ID = 6;</script>
    <script src="./assets/js/save-result.js"></script>
    <script src="./assets/js/social-anxiety.js"></script>
  </body>
</html>
