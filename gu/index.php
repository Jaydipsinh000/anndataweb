<?php
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION["user_id"]);
$userName = $isLoggedIn && isset($_SESSION["user_name"]) ? $_SESSION["user_name"] : '';
?>
<!DOCTYPE html>
<html lang="gu">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>અન્નદાતા | કૃષકનો સાચો ભાગીદાર</title>
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
        🌍 ભાષા બદલો / Change Language
      </button>

      <div class="hero-content">
        <h1>અન્નદાતા – કૃષકનો સાચો ભાગીદાર</h1>
        <p>
          <?php if($isLoggedIn): ?>
            સુagat છો, <?= htmlspecialchars($userName) ?>! તમારા ખેતી ઉત્પાદન માટે સાધનો અને માર્ગદર્શન શોધો.
          <?php else: ?>
            એક સરળ અને સુરક્ષિત પ્લેટફોર્મ: સાધનો, માર્ગદર્શન, અને સહાય માટે.
          <?php endif; ?>
        </p>
        <?php if($isLoggedIn): ?>
          <button onclick="window.location.href='profile.php'">ડેશબોર્ડ પર જાઓ</button>
        <?php else: ?>
          <button onclick="window.location.href='register.php'">રજીસ્ટર / લોગિન</button>
        <?php endif; ?>
      </div>
    </section>

    <section class="about-section">
      <h2>અમારા વિશે</h2>
      <p>
        અન્નદાતા એક પ્લેટફોર્મ છે જે ખેડૂતો માટે રચાયેલું છે, જેમાં ખેતીના સાધનો, સાંસર્ગિક ખેતી અને વધુ માહિતી સરળ ભાષામાં ઉપલબ્ધ છે.
      </p>
    </section>
  </main>

  <!-- FOOTER -->
  <?php include '../includes/footer.php'; ?>
  <script src="../script.js"></script>
</body>
</html>
