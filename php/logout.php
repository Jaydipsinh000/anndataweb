<?php
// logout.php
session_start();

// Determine current language before destroying session
$currentLang = $_SESSION['lang'] ?? 'en';

// Destroy session completely
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirect to correct language register page
header("Location: ../$currentLang/register.php?msg=logged_out");
exit;
