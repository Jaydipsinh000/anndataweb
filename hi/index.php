<?php
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION["user_id"]);
$userName = $isLoggedIn && isset($_SESSION["user_name"]) ? $_SESSION["user_name"] : '';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>अन्नदाता | किसान का सच्चा साथी</title>
  <link rel="stylesheet" href="../styles.css" />
</head>
<body>
  <!-- HEADER -->
<?php include '../includes/header.php'; ?>

  <!-- MAIN -->
  <main>
    <section class="hero-section" id="hero-section" style="position: relative;">
      <!-- Language Switch Button -->
      <button onclick="window.location.href='../index.html'"
        style="
          position: absolute;
          top: 10px;
          right: 10px;
          padding: 8px 16px;
          background-color: #4CAF50;
          color: white;
          border: none;
          border-radius: 5px;
          cursor: pointer;
          font-weight: bold;
          z-index: 10;
        ">
        🌍 भाषा बदलें / Change Language
      </button>

      <div class="hero-content">
        <h1>अन्नदाता – किसान का सच्चा साथी</h1>
        <p>
          <?php if($isLoggedIn): ?>
            स्वागत है, <?= htmlspecialchars($userName) ?>! अपनी फसल के लिए उपकरण और मार्गदर्शन खोजें।
          <?php else: ?>
            एक सरल और सुरक्षित प्लेटफ़ॉर्म: उपकरण, मार्गदर्शन और सहायता के लिए।
          <?php endif; ?>
        </p>
        <?php if($isLoggedIn): ?>
          <button onclick="window.location.href='profile.php'">डैशबोर्ड पर जाएँ</button>
        <?php else: ?>
          <button onclick="window.location.href='register.php'">रजिस्टर / लॉगिन</button>
        <?php endif; ?>
      </div>
    </section>

    <section class="about-section">
      <h2>हमारे बारे में</h2>
      <p>
        अन्नदाता एक ऐसा प्लेटफ़ॉर्म है जो किसानों के लिए बनाया गया है, जिसमें खेती के उपकरण, जैविक खेती और अन्य जानकारी सरल भाषा में उपलब्ध है।
      </p>
    </section>
  </main>

  <!-- FOOTER -->
  <?php include '../includes/footer.php'; ?>
  <script src="../script.js"></script>
</body>
</html>
