<?php
session_start();
include '../php/db.php'; // DB connection

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userId     = $isLoggedIn ? $_SESSION['user_id'] : null;
$userName   = $isLoggedIn && isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$userType   = $isLoggedIn && isset($_SESSION['user_type']) ? $_SESSION['user_type'] : '';
$userEmail  = $isLoggedIn && isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';

// User Info
$user = null;
if($isLoggedIn){
    $user_sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>जैविक खेती | अन्नदाता</title>
  <link rel="stylesheet" href="../styles.css">
  <style>
    .hero-organic {
      background: linear-gradient(to right, #4CAF50, #2e7d32);
      color: white;
      text-align: center;
      padding: 120px 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }
    .hero-organic h1 { font-size: 3rem; font-weight: bold; margin-bottom: 20px; }
    .hero-organic p { font-size: 1.2rem; margin-bottom: 30px; }
    .hero-organic a {
      background: white;
      color: #2e7d32;
      padding: 12px 25px;
      text-decoration: none;
      border-radius: 5px;
      font-weight: bold;
      transition: 0.3s;
    }
    .hero-organic a:hover { background: #e8f5e9; }

    .benefits { display: flex; flex-wrap: wrap; justify-content: center; margin: 50px 20px; gap: 20px; }
    .benefit-card {
      background: #f7f7f7; padding: 25px; border-radius: 10px; width: 250px;
      text-align: center; transition: transform 0.3s;
    }
    .benefit-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    .benefit-card div { font-size: 40px; margin-bottom: 15px; }
    .benefit-card h3 { margin-bottom: 10px; color: #333; }

    .steps { background: #eaf5ea; padding: 50px 20px; }
    .steps h2 { text-align: center; margin-bottom: 40px; color: #2e7d32; }
    .step { display: flex; align-items: center; margin-bottom: 30px; gap: 20px; }
    .step div.icon { font-size: 50px; }
    .step-text { flex: 1; }
    .step-text h3 { margin-bottom: 10px; color: #2e7d32; }

    .tools { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin: 50px 20px; }
    .tool-card { text-align: center; border: 1px solid #ddd; border-radius: 10px; padding: 20px; background: #fff; transition: transform 0.3s; }
    .tool-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    .tool-card div { font-size: 40px; margin-bottom: 10px; }

    .cta { text-align: center; padding: 50px 20px; background: #4CAF50; color: white; }
    .cta a {
      background: white; color: #4CAF50; padding: 15px 30px; border-radius: 5px;
      font-weight: bold; text-decoration: none; transition: 0.3s;
    }
    .cta a:hover { background: #e8f5e9; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }

    @media(max-width: 768px){
      .step { flex-direction: column; text-align: center; }
      .hero-organic h1 { font-size: 2rem; }
      .hero-organic p { font-size: 1rem; }
    }
  </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<section class="hero-organic">
  <h1>स्वस्थ और प्राकृतिक खेती</h1>
  <p>
    <?php if($isLoggedIn): ?>
      स्वागत है, <?= htmlspecialchars($userName) ?>! 
      <?php if($userType=='farmer'): ?>अपनी फसल के उत्पादन को बढ़ाने के सर्वोत्तम तरीके जानें।<?php else: ?>किसानों को प्रभावी सहायता देने के लिए मार्गदर्शन प्राप्त करें।<?php endif; ?>
    <?php else: ?>
      अपनी जमीन और खेती के लिए सर्वोत्तम पद्धतियाँ सीखें।
    <?php endif; ?>
  </p>
  <a href="#benefits">और पढ़ें</a>
</section>

<section id="benefits" class="benefits">
  <div class="benefit-card">
    <div>🌱</div>
    <h3>मिट्टी का स्वास्थ्य</h3>
    <p>मिट्टी की गुणवत्ता सुधारें और दीर्घकालिक लाभ प्राप्त करें।</p>
  </div>
  <div class="benefit-card">
    <div>🥦</div>
    <h3>स्वस्थ फसलें</h3>
    <p>रसायन-मुक्त फसलें, पोषक तत्वों से भरपूर और सुरक्षित भोजन।</p>
  </div>
  <div class="benefit-card">
    <div>🌍</div>
    <h3>पर्यावरण-अनुकूल</h3>
    <p>पर्यावरण की सुरक्षा और प्रदूषण को कम करने के तरीके।</p>
  </div>
</section>

<section class="steps">
  <h2>शुरू करने के कदम</h2>
  <div class="step">
    <div class="icon">🪱</div>
    <div class="step-text">
      <h3>कदम 1: जमीन तैयार करें</h3>
      <p><?php echo $isLoggedIn && $userType=='farmer' ? "अपनी खेत की मिट्टी की जाँच करें और उच्च उत्पादन के लिए जैविक खाद का उपयोग करें।" : "किसानों को जमीन की तैयारी में मदद करें।"; ?></p>
    </div>
  </div>
  <div class="step">
    <div class="icon">🌾</div>
    <div class="step-text">
      <h3>कदम 2: बीज चुनें</h3>
      <p><?php echo $isLoggedIn && $userType=='farmer' ? "उच्च गुणवत्ता वाले स्थानीय बीज का चयन करें।" : "किसानों के लिए उचित बीज चुनने में मदद करें।"; ?></p>
    </div>
  </div>
  <div class="step">
    <div class="icon">🧑‍🌾</div>
    <div class="step-text">
      <h3>कदम 3: प्राकृतिक खाद का उपयोग</h3>
      <p><?php echo $isLoggedIn && $userType=='farmer' ? "मिट्टी की पोषण के लिए जैविक खाद का उपयोग करें।" : "जैविक खाद लगाने में मदद करें।"; ?></p>
    </div>
  </div>
  <div class="step">
    <div class="icon">🐞</div>
    <div class="step-text">
      <h3>कदम 4: प्राकृतिक कीट नियंत्रण</h3>
      <p><?php echo $isLoggedIn && $userType=='farmer' ? "ईको-फ्रेंडली तरीकों से कीट नियंत्रित करें।" : "जैविक तरीके से कीट नियंत्रण में मदद करें।"; ?></p>
    </div>
  </div>
</section>

<section class="tools">
  <div class="tool-card">
    <div>🪴</div>
    <h3>कंपोस्ट बिन</h3>
  </div>
  <div class="tool-card">
    <div>⛏️</div>
    <h3>मिट्टी उपकरण</h3>
  </div>
  <div class="tool-card">
    <div>🪰</div>
    <h3>जैविक कीट नियंत्रण उपकरण</h3>
  </div>
</section>

<section class="cta">
  <h2>
    <?php if($isLoggedIn): ?>
      आज ही अपनी खेती के लिए व्यक्तिगत मार्गदर्शन प्राप्त करें!
    <?php else: ?>
      हम आपको शुरुआत में मदद कर सकते हैं
    <?php endif; ?>
  </h2>
  <a href="../hi/profile.php"><?php echo $isLoggedIn ? "अपने डैशबोर्ड देखें" : "मार्गदर्शन के लिए रजिस्टर करें"; ?></a>
</section>

<script>
  document.querySelector('.hero-organic a').addEventListener('click', function(e){
    e.preventDefault();
    document.querySelector('#benefits').scrollIntoView({behavior:'smooth'});
  });

  function toggleMenu(){
    const navLinks = document.getElementById('nav-links');
    navLinks.classList.toggle('active');
  }
</script>
</body>
</html>
