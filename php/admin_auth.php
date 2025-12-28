<?php
// ----------------------
// Secure Admin Authentication
// ----------------------

// Start session safely if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'use_strict_mode' => true,
        'cookie_samesite' => 'Strict',
        'cookie_secure' => isset($_SERVER['HTTPS']) // only secure if HTTPS
    ]);
}

require_once __DIR__ . '/db.php'; // Database connection

// ----------------------
// Helper Functions
// ----------------------
function redirect($url) {
    header("Location: $url");
    exit;
}

// Flash messages: set or get and clear
function flash($type, $message = null) {
    if ($message !== null) {
        $_SESSION['flash_' . $type] = $message;
    } elseif (isset($_SESSION['flash_' . $type])) {
        $msg = $_SESSION['flash_' . $type];
        unset($_SESSION['flash_' . $type]);
        return $msg;
    }
    return null;
}

// Check if admin is logged in
function checkAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        flash('error', 'Please login to access this page.');
        redirect('../admin/login.php');
    }
}

// Role-based access control (optional)
function requireRole($role) {
    if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== $role) {
        http_response_code(403);
        echo "Access denied.";
        exit;
    }
}

// ----------------------
// Handle POST login
// ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $username_or_email = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username_or_email) || empty($password)) {
        flash('error', 'Please enter username/email and password.');
        redirect('../admin/login.php');
    }

    $sql = "SELECT id, username, email, password, role FROM admins WHERE username = ? OR email = ? LIMIT 1";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $username_or_email, $username_or_email);
        $stmt->execute();
        $res = $stmt->get_result();
        $admin = $res->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            // Successful login
            session_regenerate_id(true); // prevent session fixation
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role'];

            flash('msg', 'Welcome back, ' . htmlspecialchars($admin['username']));
            redirect('../admin/dashboard.php');
        } else {
            flash('error', 'Invalid username/email or password.');
            redirect('../admin/login.php');
        }
    } else {
        flash('error', 'Server error. Try again.');
        redirect('../admin/login.php');
    }
    exit;
}

// ----------------------
// Handle Logout
// ----------------------
if (($_GET['action'] ?? '') === 'logout') {
    // Clear session data
    $_SESSION = [];
    
    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            isset($_SERVER['HTTPS']), // secure only if HTTPS
            true // httponly
        );
    }
    
    session_destroy();
    flash('msg', 'You have been logged out.');
    redirect('../admin/login.php');
}
?>
