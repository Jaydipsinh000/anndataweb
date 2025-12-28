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
<html lang="gu">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>સાંસર્ગિક ખેતી | અન્નદાતા</title>
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
  <h1>સ્વસ્થ અને કુદરતી ખેતી</h1>
  <p>
    <?php if($isLoggedIn): ?>
      સ્વાગત છે, <?= htmlspecialchars($userName) ?>! 
      <?php if($userType=='farmer'): ?>તમારી ખેતીના ઉત્પાદનને વધારવા માટેની શ્રેષ્ઠ રીતો શોધો.<?php else: ?>ખેડુતોને અસરકારક રીતે સહાય કરવા માટે માર્ગદર્શન મેળવો.<?php endif; ?>
    <?php else: ?>
      તમારી જમીન અને ખેતી માટે શ્રેષ્ઠ પદ્ધતિઓ શીખો.
    <?php endif; ?>
  </p>
  <a href="#benefits">વધુ વાંચો</a>
</section>

<section id="benefits" class="benefits">
  <div class="benefit-card">
    <div>🌱</div>
    <h3>જમીનનો આરોગ્ય</h3>
    <p>જમીનની ગુણવત્તા સુધારે છે અને લાંબા ગાળાના લાભ આપે છે.</p>
  </div>
  <div class="benefit-card">
    <div>🥦</div>
    <h3>આરોગ્યપ્રદ પાકો</h3>
    <p>રસાયણમુક્ત પાકો, પોષણયુક્ત અને સુરક્ષિત ખોરાક માટે.</p>
  </div>
  <div class="benefit-card">
    <div>🌍</div>
    <h3>પર્યાવરણ અનુકૂળ</h3>
    <p>પર્યાવરણની સુરક્ષા માટે અને પ્રદૂષણ ઘટાડવા માટેની રીતો.</p>
  </div>
</section>

<section class="steps">
  <h2>પ્રારંભ કરવા માટેના પગલાં</h2>
  <div class="step">
    <div class="icon">🪱</div>
    <div class="step-text">
      <h3>પગલું 1: જમીન તૈયાર કરો</h3>
      <p><?php echo $isLoggedIn && $userType=='farmer' ? "તમારી ખેતીની જમીનની પરિક્ષા કરો અને ઉચ્ચ ઉત્પાદન માટે કાર્બનિક ખાતરનો ઉપયોગ કરો." : "ખેડુતોની જમીન તૈયારીમાં મદદ કરો."; ?></p>
    </div>
  </div>
  <div class="step">
    <div class="icon">🌾</div>
    <div class="step-text">
      <h3>પગલું 2: બીજ પસંદ કરો</h3>
      <p><?php echo $isLoggedIn && $userType=='farmer' ? "ઉચ્ચ ગુણવત્તાવાળા સ્થાનિક બીજ પસંદ કરો." : "ખેડુતો માટે યોગ્ય બીજ પસંદ કરવામાં મદદ કરો."; ?></p>
    </div>
  </div>
  <div class="step">
    <div class="icon">🧑‍🌾</div>
    <div class="step-text">
      <h3>પગલું 3: કુદરતી ખાતરનો ઉપયોગ</h3>
      <p><?php echo $isLoggedIn && $userType=='farmer' ? "જમીનની પોષણ માટે કાર્બનિક ખાતરનો ઉપયોગ કરો." : "કાર્બનિક ખાતર લગાવવામાં મદદ કરો."; ?></p>
    </div>
  </div>
  <div class="step">
    <div class="icon">🐞</div>
    <div class="step-text">
      <h3>પગલું 4: કુદરતી પરિપાક નિયંત્રણ</h3>
      <p><?php echo $isLoggedIn && $userType=='farmer' ? "ઇકો-ફ્રેન્ડલી રીતોથી જીવાત નિયંત્રિત કરો." : "જૈવિક રીતે જીવાત પર નજર રાખવામાં મદદ કરો."; ?></p>
    </div>
  </div>
</section>

<section class="tools">
  <div class="tool-card">
    <div>🪴</div>
    <h3>કૉમ્પોસ્ટ બિન</h3>
  </div>
  <div class="tool-card">
    <div>⛏️</div>
    <h3>જમીન સાધનો</h3>
  </div>
  <div class="tool-card">
    <div>🪰</div>
    <h3>જૈવિક જીવાત નિયંત્રણ સાધનો</h3>
  </div>
</section>

<section class="cta">
  <h2>
    <?php if($isLoggedIn): ?>
      આજે તમારી ખેતી માટે વ્યક્તિગત માર્ગદર્શન મેળવો!
    <?php else: ?>
      અમે તમને પ્રારંભ કરવામાં મદદ કરી શકીએ છીએ
    <?php endif; ?>
  </h2>
  <a href="../gu/profile.php"><?php echo $isLoggedIn ? "તમારા ડેશબોર્ડ જુઓ" : "માર્ગદર્શન માટે રજીસ્ટર કરો"; ?></a>
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
