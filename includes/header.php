<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Detect language ---
if (!isset($_SESSION['lang'])) {
    $currentFolder = basename(dirname($_SERVER['PHP_SELF'])); // gu / hi / en
    if (in_array($currentFolder, ['gu','hi','en'])) {
        $_SESSION['lang'] = $currentFolder;
    } else {
        $_SESSION['lang'] = 'en'; // default
    }
}

// Allow language change via GET param
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en','gu','hi'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

$currentLang = $_SESSION['lang'];

// --- Root URL (localhost folder) ---
$baseURL = '/anndataweb/'; // ⚠️ Apne project folder ke hisaab se change karo
$folderPrefix = $baseURL . $currentLang . '/';

// --- Texts for each language ---
$langTexts = [
    'en' => [
        'home' => 'Home',
        'organic' => 'Organic Farming',
        'crops' => 'Crops',
        'tools' => 'Tools',
        'profile' => 'Profile',
        'register' => 'Register / Login',
        'logout' => 'Logout',
        'slogan' => 'True Friend of Farmers',
    ],
    'gu' => [
        'home' => 'હોમ',
        'organic' => 'ઓર્ગેનિક ખેતી',
        'crops' => 'ફસલો',
        'tools' => 'ઉપકરણો',
        'profile' => 'પ્રોફાઇલ',
        'register' => 'નોધણી / લૉગિન',
        'logout' => 'લૉગઆઉટ', 
        'slogan' => 'કિસાનનો સાચો મિત્ર',
    ],
    'hi' => [
        'home' => 'होम',
        'organic' => 'ऑर्गेनिक खेती',
        'crops' => 'फसलें',
        'tools' => 'उपकरण',
        'profile' => 'प्रोफाइल',
        'register' => 'रजिस्टर / लॉगिन',
        'logout' => 'लॉगआउट', 
        'slogan' => 'किसानों का सच्चा मित्र',
    ]
];

// --- Logged-in info ---
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
?>

<style>
/* Mobile fix for your existing code */
@media (max-width: 768px) {
    .navbar {
        display: none; /* initially hidden on mobile */
        flex-direction: column;
        position: absolute;
        top: 60px; /* adjust if header height alag ho */
        right: 10px;
        background: #2a7a2a; /* same as your header */
        padding: 10px 15px;
        border-radius: 5px;
        z-index: 100;
    }
    .navbar.active {
        display: flex;
    }
    .navbar li a {
        color: #fff; /* ensure visible */
        padding: 8px 0;
    }
}
</style>

<header>
  <div style="display: flex; align-items: center;">
    <img src="<?= $baseURL ?>images/logo.jpeg" alt="Anndata Logo" class="logo" />
    <div class="logo-title" style="line-height: 1; margin-left: 8px;">
      <span style="font-size: 2rem; font-weight: bold; color: #fff; line-height: 1;">Anndata</span>
      <span style="font-size: 1rem; color: #e0ffe0; line-height: 1;"><?= $langTexts[$currentLang]['slogan'] ?></span>
    </div>
  </div>

  <nav>
    <div class="hamburger" id="hamburger" onclick="toggleMenu()">
      <span></span><span></span><span></span>
    </div>
    <ul class="navbar" id="nav-links">
      <li><a href="<?= $folderPrefix ?>index.php"><?= $langTexts[$currentLang]['home'] ?></a></li>
      <li><a href="<?= $folderPrefix ?>organic.php"><?= $langTexts[$currentLang]['organic'] ?></a></li>

      <?php if($isLoggedIn): ?>
        <li><a href="<?= $folderPrefix ?>crops.php"><?= $langTexts[$currentLang]['crops'] ?></a></li>
        <li><a href="<?= $folderPrefix ?>tools.php"><?= $langTexts[$currentLang]['tools'] ?></a></li>
        <li><a href="<?= $folderPrefix ?>profile.php"><?= $langTexts[$currentLang]['profile'] ?> (<?= htmlspecialchars($userName) ?>)</a></li>
        <li><a href="<?= $baseURL ?>php/logout.php?lang=<?= $currentLang ?>"><?= $langTexts[$currentLang]['logout'] ?></a></li>
      <?php else: ?>
        <li><a href="<?= $folderPrefix ?>crops.html"><?= $langTexts[$currentLang]['crops'] ?></a></li>
        <li><a href="<?= $folderPrefix ?>tools.html"><?= $langTexts[$currentLang]['tools'] ?></a></li>
        <li><a href="<?= $folderPrefix ?>register.php"><?= $langTexts[$currentLang]['register'] ?></a></li>
      <?php endif; ?>

     
    </ul>
  </nav>
</header>

<script>
// Add this JS at the end of your header
function toggleMenu() {
    const navLinks = document.getElementById('nav-links');
    navLinks.classList.toggle('active');
}
</script>