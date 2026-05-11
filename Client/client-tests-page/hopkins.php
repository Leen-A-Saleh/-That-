<!doctype html>
<html lang="ar" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>إختبار هوبكنز للقلق والإكتئاب</title>

    <link rel="stylesheet" href="./assets/css/test-page.css" />
  </head>

  <body>
    <div class="test-container">
      <h1>إختبار هوبكنز للقلق والاكتئاب</h1>

      <p class="test-desc">
        فيما يلي مجموعة من الأعراض أو المشكلات التي قد يعاني منها الأشخاص
        أحياناً. يرجى قراءة كل عبارة بعناية، ثم تحديد مدى إنزعاجك منها خلال
        الأسبوع الماضي بما في ذلك اليوم.
      </p>

      <div class="progress">
        <div id="progressBar"></div>
      </div>

      <h3 id="sectionTitle"></h3>
      <div id="questionContainer"></div>

      <div id="resultBox"></div>
    </div>

    <script src="./assets/js/hopkins.js"></script>
  </body>
</html>
