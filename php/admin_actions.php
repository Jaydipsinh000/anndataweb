<?php
// ----------------------
// Secure Admin Actions Handler
// ----------------------
require_once __DIR__ . '/admin_auth.php'; // check admin session
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';       // CSRF helper

checkAdmin(); // ensure admin logged in

// ----------------------
// Flash helper (already in admin_auth) can be used
// ----------------------
$admin_id = $_SESSION['admin_id'];

// Only allow POST requests for actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF verification
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!csrf_verify($csrf_token)) {
        flash('error', 'CSRF verification failed.');
        header("Location: ../admin/dashboard.php");
        exit();
    }

    $action = $_POST['action'] ?? '';

    // ----------------------
    // USER actions
    // ----------------------
    if (in_array($action, ['approve', 'block', 'delete_user']) && !empty($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);

        switch ($action) {
            case 'approve':
                $stmt = $conn->prepare("UPDATE users SET status='approved' WHERE id=?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute(); $stmt->close();
                $log_action = 'Approve User';
                $flash_msg = "✅ User approved.";
                break;
            case 'block':
                $stmt = $conn->prepare("UPDATE users SET status='blocked' WHERE id=?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute(); $stmt->close();
                $log_action = 'Block User';
                $flash_msg = "⛔ User blocked.";
                break;
            case 'delete_user':
                $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute(); $stmt->close();
                $log_action = 'Delete User';
                $flash_msg = "🗑 User deleted.";
                break;
        }

        // Log admin action
        $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, table_name, target_id, created_at) VALUES (?, ?, 'users', ?, NOW())");
        $stmt->bind_param("isi", $admin_id, $log_action, $user_id);
        $stmt->execute(); $stmt->close();

        flash('msg', $flash_msg);
        header("Location: ../admin/users.php");
        exit();
    }

    // ----------------------
    // TOOL actions
    // ----------------------
    if (in_array($action, ['add_tool', 'edit_tool', 'delete_tool'])) {
        $user_id = intval($_POST['user_id'] ?? 0);
        $tool_id = intval($_POST['tool_id'] ?? 0);
        $tool_name = trim($_POST['tool_name'] ?? '');

        if ($action === 'add_tool' && $user_id && $tool_name) {
            $stmt = $conn->prepare("INSERT INTO tools (user_id, tool_name, approved_by, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("isi", $user_id, $tool_name, $admin_id);
            $stmt->execute();
            $tool_id = $stmt->insert_id;
            $stmt->close();
            $log_action = 'Add Tool';
            $flash_msg = "✅ Tool added.";
        } elseif ($action === 'edit_tool' && $tool_id && $user_id && $tool_name) {
            $stmt = $conn->prepare("UPDATE tools SET tool_name=?, user_id=?, approved_by=? WHERE id=?");
            $stmt->bind_param("siii", $tool_name, $user_id, $admin_id, $tool_id);
            $stmt->execute(); $stmt->close();
            $log_action = 'Edit Tool';
            $flash_msg = "✅ Tool updated.";
        } elseif ($action === 'delete_tool' && $tool_id) {
            $stmt = $conn->prepare("DELETE FROM tools WHERE id=?");
            $stmt->bind_param("i", $tool_id);
            $stmt->execute(); $stmt->close();
            $log_action = 'Delete Tool';
            $flash_msg = "🗑 Tool deleted.";
        } else {
            flash('error', '❌ Tool action failed. Check required fields.');
            header("Location: ../admin/tools.php");
            exit();
        }

        // Log action
        $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, table_name, target_id, created_at) VALUES (?, ?, 'tools', ?, NOW())");
        $stmt->bind_param("isi", $admin_id, $log_action, $tool_id);
        $stmt->execute(); $stmt->close();

        flash('msg', $flash_msg);
        header("Location: ../admin/tools.php");
        exit();
    }

    // ----------------------
    // CROP actions
    // ----------------------
    if ($action === 'add_crop') {
        $crop_name = trim($_POST['crop_name'] ?? '');
        $user_id = intval($_POST['user_id'] ?? 0);
        $area_value = floatval($_POST['area_value'] ?? 0);
        $area_unit = $_POST['area_unit'] ?? 'Bigha';
        $season = $_POST['season'] ?? '';
        $expected_yield = $_POST['expected_yield'] ?? '';

        if (!$crop_name || !$user_id || !$area_value || !$area_unit || !$season || !$expected_yield) {
            flash('error', '❌ Crop action failed. Check required fields.');
            header("Location: ../admin/crops.php");
            exit();
        }

        $area_size = $area_value . " " . $area_unit;

        $stmt = $conn->prepare("INSERT INTO crops (user_id, crop_name, area_size, season, expected_yield, status) VALUES (?, ?, ?, ?, ?, 'approved')");
        $stmt->bind_param("issss", $user_id, $crop_name, $area_size, $season, $expected_yield);
        $stmt->execute();
        $stmt->close();

        flash('msg', '✅ Crop added successfully!');
        header("Location: ../admin/crops.php");
        exit();
    }

    // ----------------------
    // Unknown action fallback
    // ----------------------
    flash('error', '❌ Invalid action.');
    header("Location: ../admin/dashboard.php");
    exit();
}

// Block direct GET access
header("Location: ../admin/dashboard.php");
exit();
?>
