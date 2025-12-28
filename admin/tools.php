<?php
session_start();
require_once "../php/db.php";

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_role = $_SESSION['admin_role'];

// Flash Message
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// Approve / Reject Status
if (isset($_GET['action'], $_GET['status_id'])) {
    $status_id = intval($_GET['status_id']);
    $status = ($_GET['action'] === 'approve') ? 'approved' : 'rejected';
    $stmt = $conn->prepare("UPDATE tools SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $status_id);
    $stmt->execute();
    $_SESSION['flash'] = "✅ Status updated to $status!";
    header("Location: tools.php");
    exit();
}

// Edit Tool
$edit_tool = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM tools WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_tool = $stmt->get_result()->fetch_assoc();
}

// Add/Edit Tool
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tool_action']) && $_POST['tool_action'] === 'tools') {
    $tool_name = trim($_POST['tool_name']);
    $user_id = intval($_POST['user_id']);
    $quantity = max(0, intval($_POST['available_quantity']));
    $status = in_array($_POST['status'], ['pending', 'approved', 'rejected']) ? $_POST['status'] : 'pending';
    $rent = floatval($_POST['rent']);

    if (!empty($_POST['edit_id'])) {
        $edit_id = intval($_POST['edit_id']);
        $stmt = $conn->prepare("UPDATE tools SET tool_name=?, user_id=?, quantity=?, status=?, rent=? WHERE id=?");
        $stmt->bind_param("siidsi", $tool_name, $user_id, $quantity, $status, $rent, $edit_id);
        $stmt->execute();
        $_SESSION['flash'] = "✅ Tool updated successfully!";
    } else {
        $stmt = $conn->prepare("INSERT INTO tools (tool_name, user_id, quantity, status, rent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("siids", $tool_name, $user_id, $quantity, $status, $rent);
        $stmt->execute();
        $_SESSION['flash'] = "🛠 Tool added successfully!";
    }
    header("Location: tools.php");
    exit();
}

// Delete Tool (superadmin only)
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete' && $admin_role === 'superadmin') {
    $tool_id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM tools WHERE id=?");
    $stmt->bind_param("i", $tool_id);
    $stmt->execute();
    $_SESSION['flash'] = "🗑 Tool deleted.";
    header("Location: tools.php");
    exit();
}

// Manage Master Tools
$master_edit = null;
if (isset($_GET['edit_master'])) {
    $edit_id = intval($_GET['edit_master']);
    $stmt = $conn->prepare("SELECT * FROM master_tools WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $master_edit = $stmt->get_result()->fetch_assoc();
}

// Add/Edit Master Tool
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['master_action'])) {
    $tool_name = trim($_POST['tool_name']);
    $rent = floatval($_POST['rent']);
    $available_quantity = max(0, intval($_POST['available_quantity']));

    if (!empty($_POST['edit_id'])) {
        $edit_id = intval($_POST['edit_id']);
        $stmt = $conn->prepare("UPDATE master_tools SET tool_name=?, rent=?, available_quantity=? WHERE id=?");
        $stmt->bind_param("sdii", $tool_name, $rent, $available_quantity, $edit_id);
        $stmt->execute();
        $_SESSION['flash'] = "✅ Master tool updated successfully!";
    } else {
        $stmt = $conn->prepare("INSERT INTO master_tools (tool_name, rent, available_quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $tool_name, $rent, $available_quantity);
        $stmt->execute();
        $_SESSION['flash'] = "💰 Master tool added successfully!";
    }
    header("Location: tools.php?showRent=1");
    exit();
}

// Delete Master Tool
if (isset($_GET['action'], $_GET['mid']) && $_GET['action'] === 'delete_master') {
    $mid = intval($_GET['mid']);
    $stmt = $conn->prepare("DELETE FROM master_tools WHERE id=?");
    $stmt->bind_param("i", $mid);
    $stmt->execute();
    $_SESSION['flash'] = "🗑 Master tool deleted.";
    header("Location: tools.php?showRent=1");
    exit();
}

// Fetch Data
$tools = $conn->query("SELECT t.id, t.tool_name, t.quantity, t.status, t.rent, t.created_at, u.name AS owner_name 
                       FROM tools t 
                       JOIN users u ON t.user_id=u.id 
                       ORDER BY t.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$users = $conn->query("SELECT id, name FROM users WHERE status='approved'")->fetch_all(MYSQLI_ASSOC);
$master_tools = $conn->query("SELECT * FROM master_tools ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Tools - Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{font-family:'Segoe UI',sans-serif;background:#f0f4f8;margin:0;padding:0;display:flex;}
.sidebar{width:220px;background:#2c3e50;color:#fff;position:fixed;height:100vh;}
.sidebar h2{padding:15px;text-align:center;background:#1a252f;}
.sidebar a{display:block;padding:10px 18px;color:white;text-decoration:none;}
.sidebar a:hover{background:#34495e;}
.content{margin-left:220px;padding:25px;width:calc(100% - 220px);}
h2{color:#2c3e50;}
.flash{background:#d4edda;color:#155724;padding:10px;border-radius:6px;margin-bottom:15px;}
.btn{padding:8px 16px;border-radius:8px;color:#fff;text-decoration:none;margin:4px;display:inline-block;border:none;cursor:pointer;font-size:15px;}
.add-btn{background:#27ae60;}
.edit{background:#3498db;}
.delete{background:#e74c3c;}
.approve{background:#2ecc71;}
.reject{background:#e67e22;}
.cancel-btn{background:#e74c3c;}
table{width:100%;border-collapse:collapse;background:white;margin-top:20px;box-shadow:0 2px 6px rgba(0,0,0,0.1);}
th,td{padding:10px;text-align:center;border-bottom:1px solid #ddd;}
th{background:#2980b9;color:white;}
.status-badge{padding:5px 10px;border-radius:5px;color:white;}
.status-pending{background:#f39c12;}
.status-approved{background:#27ae60;}
.status-rejected{background:#e74c3c;}
.card{background:white;border-radius:15px;box-shadow:0 3px 10px rgba(0,0,0,0.1);padding:20px;margin:20px 0;max-width:550px;transition:all .3s ease;}
.card.hidden{display:none;opacity:0;transform:translateY(-10px);}
.card.show{display:block;opacity:1;transform:translateY(0);}
.card input,.card select{width:100%;padding:8px;margin:6px 0 12px;border:1px solid #ccc;border-radius:6px;}
.card button{width:100%;padding:10px;border:none;border-radius:8px;background:#2980b9;color:white;font-size:16px;cursor:pointer;}
.card button:hover{background:#1f6fa5;}
.qty-controls{display:flex;align-items:center;gap:6px;}
.qty-controls button{width:32px;height:32px;background:#2980b9;color:white;border:none;border-radius:5px;font-size:18px;}
.qty-controls input{width:70px;text-align:center;}
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
<h2>🛠 Manage Tools</h2>
<?php if(!empty($flash)): ?><div class="flash"><?= htmlspecialchars($flash) ?></div><?php endif; ?>

<button class="btn add-btn" id="toggleToolFormBtn" <?= $edit_tool ? 'style="display:none;"' : '' ?>>➕ Add Tool</button>
<button class="btn add-btn" id="toggleRentFormBtn" <?= $master_edit ? 'style="display:none;"' : '' ?>>💰 Manage Master Tools</button>

<!-- Add/Edit Tool Form -->
<div class="card <?= $edit_tool ? 'show' : 'hidden' ?>" id="toolFormCard">
<form method="POST">
<input type="hidden" name="tool_action" value="tools">
<?php if($edit_tool): ?><input type="hidden" name="edit_id" value="<?= $edit_tool['id'] ?>"><?php endif; ?>

<label>Tool Name</label>
<select name="tool_name" id="toolSelect" required>
<option value="">Select Tool</option>
<?php foreach($master_tools as $mt): ?>
<option value="<?= $mt['tool_name'] ?>" data-rent="<?= $mt['rent'] ?>" <?= ($edit_tool && $edit_tool['tool_name']==$mt['tool_name'])?'selected':'' ?>><?= $mt['tool_name'] ?></option>
<?php endforeach; ?>
</select>

<label>Owner</label>
<select name="user_id" required>
<option value="">Select User</option>
<?php foreach($users as $u): ?>
<option value="<?= $u['id'] ?>" <?= ($edit_tool && $edit_tool['user_id']==$u['id'])?'selected':'' ?>><?= htmlspecialchars($u['name']) ?></option>
<?php endforeach; ?>
</select>

<label>Quantity</label>
<div class="qty-controls">
<button type="button" onclick="changeQty(-1)">-</button>
<input type="text" id="qtyInput" name="available_quantity" value="<?= $edit_tool['quantity'] ?? 0 ?>" readonly>
<button type="button" onclick="changeQty(1)">+</button>
</div>

<label>Rent (per day)</label>
<input type="number" id="rentInput" name="rent" value="<?= $edit_tool['rent'] ?? 0 ?>" min="0" step="0.01">

<label>Status</label>
<select name="status" required>
<?php foreach(['pending','approved','rejected'] as $s): ?>
<option value="<?= $s ?>" <?= ($edit_tool && $edit_tool['status']==$s)?'selected':'' ?>><?= ucfirst($s) ?></option>
<?php endforeach; ?>
</select>

<button type="submit"><?= $edit_tool ? "Update Tool" : "Add Tool" ?></button>
<?php if($edit_tool): ?><a href="tools.php" class="btn cancel-btn" style="display:block;text-align:center;margin-top:10px;">Cancel</a><?php endif; ?>
</form>
</div>

<!-- Tools Table -->
<table>
<tr><th>#</th><th>Tool</th><th>Owner</th><th>Qty</th><th>Rent</th><th>Status</th><th>Action</th></tr>
<?php $i=1; foreach($tools as $t): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= htmlspecialchars($t['tool_name']) ?></td>
<td><?= htmlspecialchars($t['owner_name']) ?></td>
<td><?= $t['quantity'] ?></td>
<td><?= $t['rent'] ?></td>
<td><span class="status-badge status-<?= htmlspecialchars($t['status']) ?>"><?= ucfirst(htmlspecialchars($t['status'])) ?></span></td>
<td>
<?php if($t['status']=='pending'): ?>
<a href="?action=approve&status_id=<?= $t['id'] ?>" class="btn approve">Approve</a>
<a href="?action=reject&status_id=<?= $t['id'] ?>" class="btn reject">Reject</a>
<?php endif; ?>
<a href="?edit=<?= $t['id'] ?>" class="btn edit">Edit</a>
<?php if($admin_role==='superadmin'): ?>
<a href="?action=delete&id=<?= $t['id'] ?>" class="btn delete" onclick="return confirm('Delete this tool?')">Delete</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</table>

<!-- Master Tools Form -->
<div class="card <?= $master_edit ? 'show' : 'hidden' ?>" id="masterFormCard">
<h3>💰 Add / Edit Master Tool</h3>
<form method="POST">
<input type="hidden" name="master_action" value="add">
<?php if($master_edit): ?><input type="hidden" name="edit_id" value="<?= $master_edit['id'] ?>"><?php endif; ?>

<label>Tool Name</label>
<input type="text" name="tool_name" value="<?= $master_edit['tool_name'] ?? '' ?>" required>

<label>Rent</label>
<input type="number" name="rent" min="0" step="0.01" value="<?= $master_edit['rent'] ?? 0 ?>" required>

<label>Available Quantity</label>
<div class="qty-controls">
<button type="button" onclick="changeMasterQty(-1)">-</button>
<input type="text" id="masterQtyInput" name="available_quantity" value="<?= $master_edit['available_quantity'] ?? 0 ?>" readonly>
<button type="button" onclick="changeMasterQty(1)">+</button>
</div>

<button type="submit"><?= $master_edit ? "Update" : "Add" ?></button>
<?php if($master_edit): ?><a href="tools.php?showRent=1" class="btn cancel-btn" style="display:block;text-align:center;margin-top:10px;">Cancel</a><?php endif; ?>
</form>
</div>

<table>
<tr><th>#</th><th>Tool Name</th><th>Rent</th><th>Available Qty</th><th>Action</th></tr>
<?php foreach($master_tools as $mt): ?>
<tr>
<td><?= $mt['id'] ?></td>
<td><?= htmlspecialchars($mt['tool_name']) ?></td>
<td><?= $mt['rent'] ?></td>
<td><?= $mt['available_quantity'] ?></td>
<td>
<a href="?edit_master=<?= $mt['id'] ?>&showRent=1" class="btn edit">Edit</a>
<a href="?action=delete_master&mid=<?= $mt['id'] ?>&showRent=1" class="btn delete" onclick="return confirm('Delete this tool?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>

<script>
const toolFormCard = document.getElementById('toolFormCard');
const masterFormCard = document.getElementById('masterFormCard');
const toggleToolFormBtn = document.getElementById('toggleToolFormBtn');
const toggleRentFormBtn = document.getElementById('toggleRentFormBtn');
const toolSelect = document.getElementById('toolSelect');
const rentInput = document.getElementById('rentInput');
const qtyInput = document.getElementById('qtyInput');

let baseRent = parseFloat(rentInput.value) || 0;

if (toggleToolFormBtn) toggleToolFormBtn.addEventListener('click', ()=>{ toolFormCard.classList.toggle('hidden'); toolFormCard.classList.toggle('show'); });
if (toggleRentFormBtn) toggleRentFormBtn.addEventListener('click', ()=>{ masterFormCard.classList.toggle('hidden'); masterFormCard.classList.toggle('show'); });

function changeQty(d){
    let v = parseInt(qtyInput.value || 0) + d;
    if(v<0)v=0;
    qtyInput.value=v;
    rentInput.value=(baseRent*v).toFixed(2);
}

if(toolSelect){
    toolSelect.addEventListener('change', ()=>{
        const selected=toolSelect.selectedOptions[0];
        baseRent=parseFloat(selected.dataset.rent)||0;
        const qty=parseInt(qtyInput.value||0);
        rentInput.value=(baseRent*qty).toFixed(2);
    });
}

function changeMasterQty(d){
    const i=document.getElementById('masterQtyInput');
    let v=parseInt(i.value||0)+d;
    if(v<0)v=0;
    i.value=v;
}
</script>
</body>
</html>
