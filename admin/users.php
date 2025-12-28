<?php
session_start();
require_once "../php/db.php";

// agar login nahi hua hai to redirect kar do
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Flash message
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// ------------------ Handle actions ------------------
if (isset($_GET['action'], $_GET['id'])) {
    $user_id = intval($_GET['id']);
    $action = $_GET['action'];

    // Valid actions
    $valid_actions = ['approve', 'block', 'delete'];
    if (!in_array($action, $valid_actions)) {
        $_SESSION['flash'] = "Invalid action!";
        header("Location: users.php");
        exit();
    }

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE users SET status='approved' WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash'] = "✅ User approved successfully.";
    } elseif ($action === 'block') {
        $stmt = $conn->prepare("UPDATE users SET status='blocked' WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash'] = "⛔ User blocked successfully.";
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash'] = "🗑 User deleted successfully.";
    }

    // ------------------ Log admin action ------------------
    $stmt_log = $conn->prepare("INSERT INTO admin_logs (admin_id, action, table_name, target_id, created_at) VALUES (?, ?, 'users', ?, NOW())");
    $stmt_log->bind_param("isi", $admin_id, $action, $user_id);
    $stmt_log->execute();
    $stmt_log->close();

    header("Location: users.php");
    exit();
}

// ------------------ Fetch all users ------------------
$res = $conn->query("SELECT id, name, email, status, created_at FROM users ORDER BY created_at DESC");
$users = $res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users - Admin - Anndata</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{font-family:Arial,sans-serif;background:#f4f6f9;margin:0;padding:0;display:flex;}
.sidebar{width:220px;background:#2c3e50;color:#fff;flex-shrink:0;height:100vh;position:fixed;overflow:auto;}
.sidebar h2{padding:20px;text-align:center;background:#1a252f;margin:0;font-size:1.2em;}
.sidebar a{display:block;padding:12px 20px;color:#fff;text-decoration:none;font-weight:500;transition:0.2s;}
.sidebar a:hover{background:#34495e;}
.content{margin-left:220px;padding:20px;width:100%;}
h2{margin-bottom:20px;color:#2c3e50;}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);}
th, td{padding:12px;text-align:center;border-bottom:1px solid #ddd;}
th{background:#2980b9;color:#fff;}
tr:nth-child(even){background:#f9f9f9;}
tr:hover{background:#f1f1f1;}
.btn{padding:6px 12px;border-radius:6px;text-decoration:none;color:#fff;font-weight:bold;transition:0.2s;}
.approve{background:#27ae60;}
.approve:hover{background:#1e8449;}
.block{background:#e74c3c;}
.block:hover{background:#c0392b;}
.delete{background:#95a5a6;}
.delete:hover{background:#7f8c8d;}
.flash{margin-bottom:15px;padding:10px;background:#dff0d8;color:#3c763d;border-radius:6px;}
</style>
</head>
<body>
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="dashboard.php"><i class="fa fa-tachometer-alt"></i> Dashboard</a>
    <a href="users.php"><i class="fa fa-users"></i> Manage Users</a>
    <a href="tools.php"><i class="fa fa-tools"></i> Manage Tools</a>
    <a href="crops.php"><i class="fa fa-seedling"></i> Manage Crops</a>
    <a href="admins.php"><i class="fa fa-user-shield"></i> Admins</a>
    <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <h2>Manage Users</h2>

    <?php if(!empty($flash)): ?>
        <div class="flash"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Registered At</th>
            <th>Actions</th>
        </tr>
        <?php foreach($users as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= ucfirst($user['status']) ?></td>
            <td><?= $user['created_at'] ?></td>
            <td>
                <?php if($user['status'] !== 'approved'): ?>
                    <a href="?action=approve&id=<?= $user['id'] ?>" class="btn approve">Approve</a>
                <?php else: ?>
                    <span class="btn approve" style="opacity:0.6;cursor:not-allowed;">Approved</span>
                <?php endif; ?>
                <?php if($user['status'] !== 'blocked'): ?>
                    <a href="?action=block&id=<?= $user['id'] ?>" class="btn block">Block</a>
                <?php else: ?>
                    <span class="btn block" style="opacity:0.6;cursor:not-allowed;">Blocked</span>
                <?php endif; ?>
                <a href="?action=delete&id=<?= $user['id'] ?>" class="btn delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</body>
</html>
