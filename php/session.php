<?php
// session.php — central session helper
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// function to require login on protected pages
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../en/register.php?error=login_required");
        exit;
    }
}
