<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Database/helpers.php';
require_once __DIR__ . '/../../Database/auth.php';
start_secure_session();
require_auth();
require_role(['CLIENT']);
require_once __DIR__ . '/tests-database.php';
tests_start_assessment_result(3, (int) current_user()['user_id']);
?>
<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>إختبار الحالة النفسية للأطفال</title>
    <link rel="stylesheet" href="./assets/css/test-page.css" />
  </head>

  <body>
    <div class="test-container">
      <h1>إختبار الحالة النفسية للأطفال</h1>
      <p class="test-desc">أجب على الأسئلة بناءً على حالة طفلك في الآونة الأخيرة:</p>
      <div class="progress">
        <div id="progressBar"></div>
      </div>
      <div id="questionContainer"></div>
      <div id="resultBox"></div>
    </div>

    <script>window.ASSESSMENT_ID = 3;</script>
    <script src="./assets/js/save-result.js"></script>
    <script src="./assets/js/childrenmentalhealth.js"></script>
  </body>
</html>
