<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>لعبة البحث عن الكلمات</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@700&display=swap" rel="stylesheet">
</head>
<body>
<h1>🧩 ابحث عن الكلمات</h1>
<button onclick="start()">🚀 ابدأ اللعب</button>
<button id="backBtn" style="
  position: fixed;
  top: 20px;
  left: 30px;
  padding: 20px 30px;
  background-color: #e8f6ff;
  color: #78c1b8;
  border: 2px solid #78c1b8;
  border-radius: 30px;
  font-size: 20px;
  font-weight: bold;
  cursor: pointer;
  z-index: 10000;
">
رجوع
</button>
<script>
function start(){
  window.location.href="level1.php";
}
document.getElementById('backBtn').addEventListener('click', () => {
  window.location.href = '../index.php';
});
</script>
</body>
</html>