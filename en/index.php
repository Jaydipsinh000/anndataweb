<?php
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION["user_id"]);
$userName = $isLoggedIn && isset($_SESSION["user_name"]) ? $_SESSION["user_name"] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Anndata | True Partner of Farmers</title>
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
        🌍 Change Language
      </button>

      <div class="hero-content">
        <h1>Anndata – True Partner of Farmers</h1>
        <p>
          <?php if($isLoggedIn): ?>
            Welcome, <?= htmlspecialchars($userName) ?>! Discover tools and guidance to maximize your farm yield.
          <?php else: ?>
            A simple and secure platform for tools, guidance, and support.
          <?php endif; ?>
        </p>
        <?php if($isLoggedIn): ?>
          <button onclick="window.location.href='profile.php'">Go to Dashboard</button>
        <?php else: ?>
          <button onclick="window.location.href='register.php'">Register / Login</button>
        <?php endif; ?>
      </div>
    </section>

    <section class="about-section">
      <h2>About Us</h2>
      <p>
        Anndata is a platform built for farmers, providing easy-to-understand information 
        about farming tools, organic farming, and more.
      </p>
    </section>
  </main>

  <!-- FOOTER -->
  <?php include '../includes/footer.php'; ?>
  <script src="../script.js"></script>
</body>
</html>
