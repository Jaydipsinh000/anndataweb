<?php
session_start();
require_once "../php/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// admin info
$admin_name = $_SESSION['admin_name'];
$admin_role = $_SESSION['admin_role'];

// ----------------- Fetch Stats -----------------
$stats = [
    'total_users' => 0,
    'pending_users' => 0,
    'blocked_users' => 0,
    'total_tools' => 0,
    'total_crops' => 0
];

// Users stats
$res = $conn->query("SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending_users,
    SUM(CASE WHEN status='blocked' THEN 1 ELSE 0 END) as blocked_users
FROM users");
$row = $res->fetch_assoc();
$stats['total_users'] = $row['total_users'];
$stats['pending_users'] = $row['pending_users'];
$stats['blocked_users'] = $row['blocked_users'];

// Tools
$res = $conn->query("SELECT COUNT(*) as total_tools FROM tools");
$stats['total_tools'] = $res->fetch_assoc()['total_tools'];

// Crops
$res = $conn->query("SELECT COUNT(*) as total_crops FROM crops");
$stats['total_crops'] = $res->fetch_assoc()['total_crops'];

// Recent admin activities (last 5)
$recent = [];
$res = $conn->query("
    SELECT al.*, a.username 
    FROM admin_logs al
    JOIN admins a ON al.admin_id = a.id
    ORDER BY al.created_at DESC
    LIMIT 5
");
if ($res) {
    $recent = $res->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Anndata</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:Arial, sans-serif;}
body{background:#f4f6f9;display:flex;min-height:100vh;}
.sidebar{width:220px;background:#2c3e50;color:#fff;flex-shrink:0;height:100vh;position:fixed;overflow:auto;}
.sidebar h2{padding:20px;text-align:center;background:#1a252f;margin:0;font-size:1.2em;}
.sidebar a{display:block;padding:12px 20px;color:#fff;text-decoration:none;font-weight:500;transition:0.2s;}
.sidebar a:hover{background:#34495e;}
.content{margin-left:220px;padding:20px;width:100%;}
.topbar{display:flex;justify-content:space-between;align-items:center;padding:10px 20px;background:#fff;margin-bottom:20px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:20px;margin-bottom:20px;}
.card{padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);text-align:center;color:#fff;}
.card h3{margin-bottom:10px;}
.card p{font-size:1.5em;font-weight:bold;}
.card.users{background:#2980b9;}
.card.pending{background:#e67e22;}
.card.blocked{background:#c0392b;}
.card.tools{background:#27ae60;}
.card.crops{background:#f1c40f;color:#333;}
.quick-actions{display:flex;gap:15px;flex-wrap:wrap;margin-bottom:20px;}
.quick-actions a{flex:1 1 150px;text-align:center;padding:12px 0;background:#2980b9;color:#fff;border-radius:6px;text-decoration:none;font-weight:bold;transition:0.3s;}
.quick-actions a:hover{background:#1f669e;}
.quick-actions a i{margin-right:6px;}
.recent{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);}
.recent h3{margin-bottom:15px;}
.recent table{width:100%;border-collapse:collapse;}
.recent table th, .recent table td{padding:8px 10px;text-align:left;border-bottom:1px solid #ddd;}
.recent table th{background:#f4f4f4;}
@media(max-width:768px){.sidebar{width:100%;height:auto;position:relative;}.content{margin-left:0;}}
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
    <div class="topbar">
        <div>Welcome, <strong><?= htmlspecialchars($admin_name); ?></strong> (<?= htmlspecialchars($admin_role); ?>)</div>
        <div><?= date("l, d M Y"); ?></div>
    </div>

    <div class="cards">
        <div class="card users">
            <h3><i class="fa fa-users"></i> Total Users</h3>
            <p><?= $stats['total_users']; ?></p>
        </div>
        <div class="card pending">
            <h3><i class="fa fa-hourglass-half"></i> Pending Users</h3>
            <p><?= $stats['pending_users']; ?></p>
        </div>
        <div class="card blocked">
            <h3><i class="fa fa-user-slash"></i> Blocked Users</h3>
            <p><?= $stats['blocked_users']; ?></p>
        </div>
        <div class="card tools">
            <h3><i class="fa fa-tools"></i> Total Tools</h3>
            <p><?= $stats['total_tools']; ?></p>
        </div>
        <div class="card crops">
            <h3><i class="fa fa-seedling"></i> Total Crops</h3>
            <p><?= $stats['total_crops']; ?></p>
        </div>
    </div>

    <div class="quick-actions">
        <a href="users.php"><i class="fa fa-user-check"></i> Approve Users</a>
        <a href="tools.php"><i class="fa fa-plus-circle"></i> Add Tools</a>
        <a href="crops.php"><i class="fa fa-plus-circle"></i> Add Crops</a>
        <a href="admins.php"><i class="fa fa-user-shield"></i> Manage Admins</a>
    </div>

    <div class="recent">
        <h3>Recent Admin Activities</h3>
        <table>
            <tr>
                <th>Admin</th>
                <th>Action</th>
                <th>Target</th>
                <th>Target ID</th>
                <th>Date & Time</th>
            </tr>
            <?php if(!empty($recent)): ?>
                <?php foreach($recent as $act): ?>
                <tr>
                    <td><?= htmlspecialchars($act['username']); ?></td>
                    <td><?= htmlspecialchars($act['action']); ?></td>
                    <td><?= htmlspecialchars($act['table_name'] ?? '-'); ?></td>
                    <td><?= htmlspecialchars($act['target_id'] ?? '-'); ?></td>
                    <td><?= date("d M Y H:i", strtotime($act['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align:center;">No recent activity</td></tr>
            <?php endif; ?>
        </table>
    </div>

</div>

</body>
</html>
