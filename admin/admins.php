<?php
session_start();
require_once "../php/db.php";

// Only logged-in admins
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['admin_name'];
$admin_role = $_SESSION['admin_role'];

// Fetch all admins
$res = $conn->query("SELECT id, username, email, role, created_at FROM admins ORDER BY id ASC");
$admins = $res->fetch_all(MYSQLI_ASSOC);

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admins - Admin Panel</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{font-family:Arial,sans-serif;background:#f4f6f9;margin:0;padding:0;display:flex;}
.sidebar{width:220px;background:#2c3e50;color:#fff;flex-shrink:0;height:100vh;position:fixed;overflow:auto;}
.sidebar h2{padding:20px;text-align:center;background:#1a252f;margin:0;font-size:1.2em;}
.sidebar a{display:block;padding:12px 20px;color:#fff;text-decoration:none;font-weight:500;transition:0.2s;}
.sidebar a:hover{background:#34495e;}
.content{margin-left:220px;padding:20px;width:100%;}
h2{margin-bottom:15px;color:#2c3e50;}
.flash{margin-bottom:15px;padding:10px;background:#dff0d8;color:#3c763d;border-radius:6px;text-align:center;}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);}
th,td{padding:10px;text-align:left;border-bottom:1px solid #ddd;}
th{background:#f4f4f4;}
a.action-btn{margin-right:8px;color:#2980b9;text-decoration:none;font-weight:bold;}
a.action-btn:hover{color:#1f669e;}
.add-btn{display:inline-block;margin-bottom:15px;padding:10px 15px;background:#27ae60;color:#fff;border-radius:6px;text-decoration:none;font-weight:bold;}
.add-btn:hover{background:#1e8449;}
#adminForm{display:none;margin-bottom:20px;padding:15px;background:#fff;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.1);max-width:500px;}
#adminForm input, #adminForm select{width:100%;padding:8px;margin-bottom:10px;border:1px solid #ccc;border-radius:4px;box-sizing:border-box;}
#adminForm button{width:100%;padding:10px;background:#27ae60;color:#fff;border:none;border-radius:6px;font-weight:bold;cursor:pointer;}
#adminForm button:hover{background:#1e8449;}
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
    <h2>Admins</h2>

    <?php if(!empty($flash)): ?>
        <div class="flash"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <?php if($admin_role === 'superadmin'): ?>
        <a href="#" id="toggleForm" class="add-btn"><i class="fa fa-plus-circle"></i> Add Admin</a>

        <div id="adminForm">
            <h3>🌱 Add / Edit Admin</h3>
            <form method="POST" action="save_admin.php">
                <input type="hidden" name="id" id="adminId">
                <input type="text" name="username" id="username" placeholder="Username" required>
                <input type="email" name="email" id="email" placeholder="Email" required>
                <input type="password" name="password" id="password" placeholder="Password">
                <select name="role" id="role" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Superadmin</option>
                </select>
                <button type="submit">💾 Save</button>
            </form>
        </div>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        <?php foreach($admins as $adm): ?>
        <tr>
            <td><?= htmlspecialchars($adm['id']) ?></td>
            <td><?= htmlspecialchars($adm['username']) ?></td>
            <td><?= htmlspecialchars($adm['email']) ?></td>
            <td><?= htmlspecialchars($adm['role']) ?></td>
            <td><?= htmlspecialchars($adm['created_at']) ?></td>
            <td>
                <?php if($admin_role === 'superadmin'): ?>
                    <a href="#" class="action-btn edit-btn" data-id="<?= $adm['id'] ?>" data-username="<?= htmlspecialchars($adm['username']) ?>" data-email="<?= htmlspecialchars($adm['email']) ?>" data-role="<?= $adm['role'] ?>"><i class="fa fa-edit"></i> Edit</a>
                    <a href="delete_admin.php?id=<?= $adm['id'] ?>" class="action-btn" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i> Delete</a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

</div>

<script>
document.getElementById('toggleForm').addEventListener('click', function(e){
    e.preventDefault();
    let form = document.getElementById('adminForm');
    form.style.display = (form.style.display === 'none') ? 'block' : 'none';
    // Reset form on new add
    document.getElementById('adminId').value = '';
    document.getElementById('username').value = '';
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';
    document.getElementById('role').value = '';
});

// Edit button
let editButtons = document.querySelectorAll('.edit-btn');
editButtons.forEach(btn=>{
    btn.addEventListener('click', function(e){
        e.preventDefault();
        let form = document.getElementById('adminForm');
        form.style.display = 'block';
        document.getElementById('adminId').value = this.dataset.id;
        document.getElementById('username').value = this.dataset.username;
        document.getElementById('email').value = this.dataset.email;
        document.getElementById('password').value = '';
        document.getElementById('role').value = this.dataset.role;
    });
});
</script>

</body>
</html>
