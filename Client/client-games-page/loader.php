<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تحميل اللعبة...</title>

<style>
body {
  margin: 0;
  height: 100vh;
  background: #7fb5ad;
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: "Tahoma", sans-serif;
  overflow: hidden;
}

.loader-container {
  text-align: center;
  color: white;
  animation: fadeIn 1.5s ease-in-out;
}


.logo {
  width: 200px;
  margin-bottom: 25px;
  animation: float 3s ease-in-out infinite;
}


.loader-container h1 {
  font-size: 48px;
  margin: 0;
  font-weight: bold;
  letter-spacing: 1px;
}


.loader-container p {
  margin-top: 10px;
  font-size: 20px;
  opacity: 0.9;
}


.progress-bar {
  width: 300px;
  height: 8px;
  background: rgba(255,255,255,0.3);
  border-radius: 20px;
  margin: 30px auto 0;
  overflow: hidden;
}

.progress {
  height: 100%;
  width: 0%;
  background: white;
  border-radius: 20px;
  animation: load 3s linear forwards;
}


@keyframes load {
  from { width: 0%; }
  to { width: 100%; }
}


@keyframes float {
  0% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
  100% { transform: translateY(0px); }
}


@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>
</head>

<body>

<div class="loader-container">


  <img src="logo.png" class="logo" alt="logo">
  <h1>ذات</h1>
  <p>للإستشارات النفسية</p>
  <p>تحميل اللعبة ... </p>

  <div class="progress-bar">
    <div class="progress"></div>
  </div>

</div>

<script>
const params = new URLSearchParams(window.location.search);
const gameUrl = params.get("game");
setTimeout(function(){
  if(gameUrl){
    window.location.href = gameUrl;
  }
}, 3000);
</script>

</body>
</html>