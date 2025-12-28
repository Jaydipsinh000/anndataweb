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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Organic Farming | Anndata</title>
  <link rel="stylesheet" href="../styles.css">
  <style>
    /* Hero Banner */
    .hero-organic {
      background: linear-gradient(to right, #4CAF50, #2e7d32);
      color: white;
      text-align: center;
      padding: 120px 20px;
      position: relative;
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

    /* Benefits Section */
    .benefits { display: flex; flex-wrap: wrap; justify-content: center; margin: 50px 20px; gap: 20px; }
    .benefit-card {
      background: #f7f7f7; padding: 25px; border-radius: 10px; width: 250px;
      text-align: center; transition: transform 0.3s;
    }
    .benefit-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    .benefit-card div { font-size: 40px; margin-bottom: 15px; }
    .benefit-card h3 { margin-bottom: 10px; color: #333; }

    /* Steps Section */
    .steps { background: #eaf5ea; padding: 50px 20px; }
    .steps h2 { text-align: center; margin-bottom: 40px; color: #2e7d32; }
    .step { display: flex; align-items: center; margin-bottom: 30px; gap: 20px; }
    .step div.icon { font-size: 50px; }
    .step-text { flex: 1; }
    .step-text h3 { margin-bottom: 10px; color: #2e7d32; }

    /* Tools Section */
    .tools { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin: 50px 20px; }
    .tool-card { text-align: center; border: 1px solid #ddd; border-radius: 10px; padding: 20px; background: #fff; transition: transform 0.3s; }
    .tool-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    .tool-card div { font-size: 40px; margin-bottom: 10px; }

    /* CTA Section */
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

<!-- Hero Banner -->
<section class="hero-organic">
  <h1>Healthy and Natural Farming</h1>
  <p>
    <?php if($isLoggedIn): ?>
      Welcome, <?= htmlspecialchars($userName) ?>! 
      <?php if($userType=='farmer'): ?>Discover tips to maximize your farm yield.<?php else: ?>Learn how to assist farmers effectively.<?php endif; ?>
    <?php else: ?>
      Learn the best practices for your farm and soil.
    <?php endif; ?>
  </p>
  <a href="#benefits">Read More</a>
</section>

<!-- Benefits Section -->
<section id="benefits" class="benefits">
  <div class="benefit-card">
    <div>🌱</div>
    <h3>Soil Health</h3>
    <p>Improves soil quality and provides long-term benefits.</p>
  </div>
  <div class="benefit-card">
    <div>🥦</div>
    <h3>Healthy Crops</h3>
    <p>Chemical-free crops that are rich in nutrients and safer to consume.</p>
  </div>
  <div class="benefit-card">
    <div>🌍</div>
    <h3>Eco-Friendly</h3>
    <p>Practices that protect the environment and reduce pollution.</p>
  </div>
</section>

<!-- Steps Section -->
<section class="steps">
  <h2>Steps to Get Started</h2>
  <div class="step">
    <div class="icon">🪱</div>
    <div class="step-text">
      <h3>Step 1: Prepare Soil</h3>
      <p><?php echo $isLoggedIn && $userType=='farmer' ? "Test your farm soil and enrich it with organic compost for maximum yield." : "Assist in preparing soil for farmers effectively."; ?></p>
    </div>
  </div>
  <div class="step">
    <div class="icon">🌾</div>
    <div class="step-text">
      <h3>Step 2: Select Seeds</h3>
      <p><?php echo $isLoggedIn && $userType=='farmer' ? "Choose high-quality local seeds for better production." : "Help select proper seeds for the farm."; ?></p>
    </div>
  </div>
  <div class="step">
    <div class="icon">🧑‍🌾</div>
    <div class="step-text">
      <h3>Step 3: Use Natural Fertilizers</h3>
      <p><?php echo $isLoggedIn && $userType=='farmer' ? "Use compost and organic fertilizers for soil nutrition." : "Assist in applying organic fertilizers."; ?></p>
    </div>
  </div>
  <div class="step">
    <div class="icon">🐞</div>
    <div class="step-text">
      <h3>Step 4: Natural Pest Control</h3>
      <p><?php echo $isLoggedIn && $userType=='farmer' ? "Control pests using eco-friendly methods." : "Help monitor and manage pests naturally."; ?></p>
    </div>
  </div>
</section>

<!-- Tools Section -->
<section class="tools">
  <div class="tool-card">
    <div>🪴</div>
    <h3>Compost Bin</h3>
  </div>
  <div class="tool-card">
    <div>⛏️</div>
    <h3>Soil Tools</h3>
  </div>
  <div class="tool-card">
    <div>🪰</div>
    <h3>Pest Control Tools</h3>
  </div>
</section>

<!-- CTA Section -->
<section class="cta">
  <h2>
    <?php if($isLoggedIn): ?>
      Get personalized guidance for your farm today!
    <?php else: ?>
      We Can Help You Get Started
    <?php endif; ?>
  </h2>
  <a href="../en/profile.php"><?php echo $isLoggedIn ? "View Your Dashboard" : "Register for Guidance"; ?></a>
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
