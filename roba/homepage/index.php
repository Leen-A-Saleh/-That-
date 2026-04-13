<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>صحتك تبدأ من نفسيتك</title>
  <link rel="icon" type="./img/Silver.png" href="img/Silver.png">

  <!-- خط عربي -->
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
  <!-- أيقونات Font Awesome (استخدم هذا الرابط بدون integrity لتفادي مشكلة عدم التحميل) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="../therapist/total.css">
</head>

<body>

<header class="main-header">
  <div class="header-right">

    <div class="brand">
      <a href="../homepage/index.php">
        <img src="img/Frame 392 (1).png" alt="شعار ذات" class="brand-icon brand-icon-desktop">
        <img src="img/Silver.png" alt="شعار ذات" class="brand-icon brand-icon-mobile">
      </a>
    </div>
  </div>

  <div class="header-left">
    <?php if (isset($_SESSION['user_id'])): ?>
      <span class="nav-link">مرحباً <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span>
      <a href="../auth/handlers/logout.php" class="nav-link">تسجيل الخروج</a>
    <?php else: ?>
      <a href="../auth/login/index.php" class="nav-link active-login">تسجيل الدخول</a>
      <a href="../auth/signup/index.php" class="nav-link active-login btn btn-primary">إنشاء حساب</a>
    <?php endif; ?>
  </div>
</header>


  <!-- HERO SECTION -->
  <section class="hero-section">
  <div class="hero-container">
    <div class="hero-card">
    
      <div class="hero-image-wrapper">
        <img src="./img/جلسةاستشارة.png" alt="استشارة نفسية">
      </div>

      <div class="hero-content">
    
        <h1 class="hero-title">
          صحتك <br>
          <span class="highlight">تبدأ</span> من نفسيتك
        </h1>

       
        <p class="hero-description">
          نحن هنا لنصنع الفرق، بخدمة أولويتنا سعادتك
        </p>

        
        <div class="hero-buttons">
          <a href="#" class="btnn btnn-primary">ابدأ الآن</a>
          <a href="#" class="btnn btnn-outline">احجز جلسة الآن</a>
        </div>

        <div class="hero-stats">
          <div class="stat-item">
            <span class="stat-number">500+</span>
            <span class="stat-label"> مريض</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">50+</span>
            <span class="stat-label">أخصائي</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">98%</span>
            <span class="stat-label">رضا العملاء</span>
          </div>
        </div>

      </div>

      <div class="floating-badge">
        <div class="badge-icon"><i class="fas fa-heart"></i></div>
        <div class="badge-text">        
          <span class="badge-caption">جلسات ناجحة</span>
           <span class="badge-value">+2000</span>
        </div>
      </div>

    </div>
  </div>
</section>

  <!-- ===== FEATURES ===== -->
  <section id="features" class="section">
    <div class="container">
      <div class="section-head center">
        <h1 class="section-title">
          ليه <span>منّا؟</span> وليه منصتنا مختلفة
        </h1>
        <p class="section-subtitle">
          نقدم لك تجربة استثنائية في الاستشارات النفسية
        </p>
      </div>

      <div class="features-grid">

        <div class="feature-card">
          <div class="feature-icon blue">
           
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <h3 class="feature-title"> خصوصية تامة</h3>
          <p class="feature-text">سرية معلوماتك بأعلى معايير الحماية والأمان</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon teal">
            <i class="fa-solid fa-user-group"></i>
          </div>
          <h3 class="feature-title"> أخصائيون معتمدون</h3>
          <p class="feature-text"> نخبة من الأخصائيين المؤهلين والمعتمدين </p>
        </div>

        <div class="feature-card">
          <div class="feature-icon purple">
           <i class=" fa-solid fa-comment"></i>
          </div>
          <h3 class="feature-title">  جلسات مرنة</h3>
          <p class="feature-text"> احجز جلستك عبر الإنترنت في أي وقت يناسبك</p>
        </div>

        <div class="feature-card">
          <div class="feature-icon green">
       <i class="fa-solid fa-wave-square"></i>
          </div>
          <h3 class="feature-title"> دعم مستمر</h3>
          <p class="feature-text">متابعة مستمرة ودعم على مدار الساعة</p>
        </div>

      </div>
    </div>
  </section>
<!-- ===== MOOD CARD ===== -->
<section id="mood" class="section mood-section">
  <div class="container">
    <div class="mood-card">
      <div class="moode-top">
        <h2 class="mood-title">
          كيف <span>شعورك</span> 
          اليوم؟
        </h2>
        <p class="mood-subtitle">
            عبّر عن مزاجك وابدأ رحلتك نحو صحة نفسية أفضل
        </p>
      </div>
      
      <div class="mood-emoji-wrap">
        <div class="mood-emoji-circle">
          <span class="mood-emoji" id="moodEmoji">😐</span>
        </div>

        <div class="mood-current">
          <div class="moode-text" id="moodText">عادي</div>
          <div class="mood-small"> اسحب الشريط للتعبير عن شعورك</div>
        </div>
      </div>
      <div class="mood-choices" aria-hidden="true">
        <span class="mood-choice" data-value="1">😢</span>
        <span class="mood-choice" data-value="2">😞</span>
        <span class="mood-choice active" data-value="3">😐</span>
        <span class="mood-choice" data-value="4">😊</span>
        <span class="mood-choice" data-value="5">😄</span>
      </div>
      <div class="mood-slider">
        <div class="mood-gradient-bar"></div>
        <div class="mood-thumb-dot" id="moodThumb"> .</div>

        <input type="range" id="moodRange" min="1" max="5" value="3" />
      </div>

      <div class="mood-scale-labels" aria-hidden="true">
        <span>سيّئ جدًا</span>
        <span>سيّئ</span>
        <span>عادي</span>
        <span>جيد</span>
        <span>ممتاز</span>
      </div>

    <div class="mood-advice">
    <div class="mood-top">
        <span>♡</span>
         <span>رسالة لك</span>
    </div>
    <div class="mood-text">
        شعورك مفهوم تماماً. لا تتردد في طلب المساعدة، نحن هنا لدعمك في كل خطوة.
    </div>
</div>

      <div class="mood-footer">
        <button class="mood-btn" type="button">
ابدأ رحلتك الآن →
   </button>
      </div>

    </div>
  </div>
</section>
<!-- ===== ARTICLE / EXERCISE ===== -->
<section class="section">
 <div class="layout">
    <div class="offer-card">
      
      <div class="card-content">
        <div class="label-chip">
      
           <i class="fa-solid fa-brain  label-icon" ></i>
          <span>ابدأ حالاً</span>
         
        </div>

        <h1>واقع يحتاج إلى تغيير فوري</h1>

        <p>
          في ظل التحديات التي نواجهها المعاصرة بسبب الضغوط ومن كل صعوبة
          الوصول للمراكز بسبب البعد الجغرافي، توفر منصتنا الخدمات الطبية
          والنفسية المطوّرة, ويتطلب التواصل بين المراجع والمعالج الضرورية والسهولة للدعم النفسي..
        </p>

        <p class="highlight-text">
          "الكثير من الحالات لا تصل للعلاج بسبب صعوبة التنقّل"
        </p>

        <div class="btn-area">
          <a href="#" class="btn-main">
            احجز جلستك عن بُعد
            <span class="btn-arrow">➜</span>
          </a>
        </div>
      </div>
      <div class="card-photo">
       
        <img src="./img/thinling.gif" alt="جلسة استرخاء">
      </div>
    </div>
  </div>
</section>

<!-- ===== STEPS ===== -->
<section class="section no-top">
  <div class="container">
 <div class="section-head center">
     <h1 class="sectione-title">
          كيف 
          <span class="highlight"> نحجز </span>  جلسة؟   
        </h1>
    </div>
 <p class="sectione-text"> ثلاث خطوات بسيطة للبدء في رحلتك العلاجية</p>
    <div class="wrapper">
    <div class="part part-1">
      <div class="step-card">
        <div class="step-number">1</div>
        <h3 class="stepe-title"> ابدأ بالتسجيل</h3>
        <p class="feature-text">أنشئ حسابك في دقائق معدودة</p>
      </div>   
    </div>
    <div class="part part-2">
 <div class="step-card">
        <div class="step-number">2</div>
        <h3 class="stepe-title"> اختر الأخصائي</h3>
        <p class="feature-text">اختر الأخصائي المناسب والموعد الملائم</p>
      </div>
    </div>

    <div class="part part-3">
  <div class="step-card">
        <div class="step-number">3</div>
        <h3 class="stepe-title">ابدأ الجلسة </h3>
        <p class="feature-text"> ابدأ جلستك الأولى عبر الإنترنت</p>
      </div> 
    </div>


  </div>

   
    
  </div>
</section>

<!-- ===== SERVICES ===== -->
 <section class="services-section">
    <div class="services-container">
       <h1 class="sectione-title">
          خدماتنا
          <span class="highlight">المتميزة  </span>     
        </h1>

      <div class="cards">
        
        <article class="carde">
          <div class="card-body">
            <div class="card-top">
              <div class="card-img">
                 <img src="./img/استشارة نفسية فردية .png"alt=" استشارة نفسية فردية" width=280px; height=120px;>
              <div>
                <h3 class="card-title"> استشارات نفسية فردية</h3>
                <p class="card-text"> جلسات فردية مع أخصائيين معتمدين</p>
              </div>  
            </div>
          </div>
           <a href="../auth/login/index.php" class="card-link">اعرف المزيد</a>
          </div>
        </article>

       
        <article class="carde">
          <div class="card-body">
            <div class="card-top">
              <div class="card-img">
                 <img src="./img/بارمج علاجية متخصصة.png"alt=" برامج علاجية متخصصة" width=280px; height=120px;>
              <div>
                <h3 class="card-title"> برامج علاجية متخصصة</h3>
                <p class="card-text">برامج مصممة خصيصاً لحالتك  </p>
              </div>  
            </div>
          </div>
           <a href="../auth/login/index.php" class="card-link">اعرف المزيد</a>
          </div>
        </article>

        
        <article class="carde">
          <div class="card-body">
            <div class="card-top">
              <div class="card-img">
                 <img src="./img/اختبارات نفسية .png"alt="اختبارات نفسية  " width=280px; height=120px;>
              <div>
                <h3 class="card-title"> اختبارات نفسية  </h3>
                <p class="card-text">  تقييم شامل لحالتك النفسية</p>
              </div>  
            </div>
          </div>
           <a href="../auth/login/index.php" class="card-link">اعرف المزيد</a>
          </div>
        </article>
      </div>
    </div>
  </section>

<!-- ===== STORIES ===== -->
<section class="section no-top">
  <div class="container">
    <div class="section-head between">
       <h1 class="sectione-title">
          قصص
          <span class="highlight">نجاحنا  </span>     
        </h1>
        <p class="sectione-text">تجارب حقيقية من مرضى استعادوا حياتهم مع منصة ذات</p>
    </div>

    <div class="stories-grid">

      <div class="card green">
        <div class="quote-box">
          <div class="quote-icon">“</div>
          <p> "كنت أعاني من قلق مستمر أثر على حياتي العملية والاجتماعية. بعد 8 جلسات مع د. سارة، تعلمت كيف أتعامل مع القلق وأصبحت أكثر ثقة بنفسي."</p>
        </div>
        <div class="footer">
          <div class="details">
            <h3 class="name"> نورة العتيبي</h3>
            <p class="location"> 18 عاماً • القلق والتوتر  </p>
            <div class="stars">★★★★★</div>            
            <div class="badge"> شهرين من العلاج </div>
          </div>
          <img src="./img/نورة العتيبي.png" alt= "نورة" class="avatar">
        </div>
      </div>

      
      <div class="card pink">
        <div class="quote-box">
          <div class="quote-icon">“</div>
          <p>"مرّيت بفترة صعبة بعد فقدان وظيفتي. الأخصائي ساعدني أفهم مشاعري وأعيد بناء ثقتي. الآن أنا في وظيفة أفضل وحياتي تحسنت كثير."</p>
        </div>
        <div class="footer">
          <div class="details">
            <h3 class="name">عبدالله السالم</h3>
            <p class="location">   35 عاماً • الاكتئاب </p>
             <div class="stars">★★★★★</div>
            <div class="badge">4 أشهر من العلاج </div>
          </div>
          <img src="./img/عبدالله السالم.png" alt="عبدالله" class="avatar">
        </div>
      </div>

      
      <div class="card blue">
        <div class="quote-box">
          <div class="quote-icon">“</div>
          <p>"عانيت من الأرق لسنوات. من خلال الجلسات، تعلمت تقنيات الاسترخاء وغيّرت عاداتي. الآن أنام بشكل طبيعي وأشعر بطاقة أكبر." </p>
        </div>
        <div class="footer">
          <div class="details">
            <h3 class="name">مريم القحطاني</h3>
            <p class="location">  26 عاماً • اضطرابات النوم</p>
             <div class="stars">★★★★★</div>
            <div class="badge">6 أسابيع من العلاج</div>
          </div>
          <img src="./img/مريم القحطاني.png" alt="مريم" class="avatar">
        </div>
      </div>

    </div>
    
      <div class="stats-grid">

      <div class="stat-card">
        <div class="stat-number">98%</div>
        <div class="stat-label"> معدل الرضا</div>
      </div>

      <div class="stat-card">
        <div class="stat-number">+2000</div>
        <div class="stat-label">جلسة ناجحة </div>
      </div>

      <div class="stat-card">
        <div class="stat-number">+500</div>
        <div class="stat-label"> مريض سعيد</div>
      </div>

      <div class="stat-card">
        <div class="stat-number">4.9/5</div>
        <div class="stat-label">التقييم العام </div>
      </div>

    </div>
  </div>
</section>

<section class="cta-section">
  <div class="cta-container">
    <h2 class="cta-title">
      يمكنك الآن الحصول على الصحة النفسية<br>بسهولة
    </h2>
    <p class="cta-subtitle">
      ابدأ رحلتك نحو حياة أفضل مع أفضل الأخصائيين النفسيين
    </p>
    <div class="cta-buttons">
      <a href="#" class="cta-btn cta-btn-primary">ابدأ مجاناً الآن</a>
      <a href="#" class="cta-btn cta-btn-outline">فريق متخصص</a>
    </div>
  </div>
</section>

<footer class="site-footer">
  <div class="footer-container">

    
    <div class="footer-col">
      <h3 class="footer-logo">ذات</h3>
      <p class="footer-desc">
        منصة رائدة في تقديم الاستشارات النفسية عن بعد 
        بأعلى معايير الجودة والخصوصية
      </p>
    </div>

   
    <div class="footer-col">
      <h4 class="footer-heading">روابط سريعة</h4>
      <ul class="footer-links">
        <li><a href="#">من نحن</a></li>
        <li><a href="#">الأخصائيون</a></li>
        <li><a href="#">حجز جلسة</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4 class="footer-heading">الدعم</h4>
      <ul class="footer-links">
        <li><a href="#">تواصل معنا</a></li>
        <li><a href="#">الأسئلة الشائعة</a></li>
        <li><a href="#">سياسة الخصوصية</a></li>
      </ul>
    </div>

   
    <div class="footer-col">
      <h4 class="footer-heading">تواصل معنا</h4>
      <p class="footer-contact">info@mentalhealth.sa</p>
      <p class="footer-contact">+966 50 000 0000</p>
      <div class="footer-social">
        <a href="https://www.facebook.com/share/18ZC3jbTpz/?mibextid=wwXIfr" class="social-icon"><i class="fab fa-facebook-f"></i></a>
        <a href="https://wa.me/972598584750" class="social-icon"> <i class="fa-brands fa-whatsapp"></i></a>
        <a href="https://www.instagram.com/zat_psychological_counseling?igsh=MXRqYzBmdmtoeG40ZA==" class="social-icon"><i class="fab fa-instagram"></i></a>
      </div>
    </div>

  </div>

  <div class="footer-bottom">
    © 2026 ذات للاستشارات النفسية. جميع الحقوق محفوظة.
  </div>
</footer>

</body>
</html>
