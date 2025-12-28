<?php
session_start();
require_once "../php/db.php";

// Redirect if not logged in
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

// --------------------
// Add or Edit Crop
// --------------------
$edit_mode = false;
$edit_crop = null;

if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_mode = true;
    $stmt = $conn->prepare("SELECT * FROM crops WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_crop = $stmt->get_result()->fetch_assoc();
}

// Save form (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crop_name = $_POST['crop_name'];
    $area_value = $_POST['area_value'];
    $area_unit = $_POST['area_unit'];
    $season = $_POST['season'];
    $expected_yield = $_POST['expected_yield'];
    $status = $_POST['status'];
    $user_id = $_POST['user_id'];

    $area_size = "$area_value $area_unit";

    if (!empty($_POST['edit_id'])) {
        // Update existing crop
        $edit_id = intval($_POST['edit_id']);
        $stmt = $conn->prepare("UPDATE crops SET crop_name=?, area_size=?, season=?, expected_yield=?, status=?, user_id=? WHERE id=?");
        $stmt->bind_param("ssssssi", $crop_name, $area_size, $season, $expected_yield, $status, $user_id, $edit_id);
        $stmt->execute();
        $_SESSION['flash'] = "✅ Crop updated successfully!";
    } else {
        // Add new crop
        $stmt = $conn->prepare("INSERT INTO crops (crop_name, area_size, season, expected_yield, status, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssi", $crop_name, $area_size, $season, $expected_yield, $status, $user_id);
        $stmt->execute();
        $_SESSION['flash'] = "🌱 New crop added successfully!";
    }

    header("Location: crops.php");
    exit();
}

// --------------------
// Handle actions (Approve/Reject/Delete)
// --------------------
if (isset($_GET['action'], $_GET['id'])) {
    $crop_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM crops WHERE id=?");
        $stmt->bind_param("i", $crop_id);
        $stmt->execute();
        $_SESSION['flash'] = "🗑 Crop deleted successfully.";
    } elseif ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE crops SET status='approved' WHERE id=?");
        $stmt->bind_param("i", $crop_id);
        $stmt->execute();
        $_SESSION['flash'] = "✅ Crop approved successfully.";
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE crops SET status='rejected' WHERE id=?");
        $stmt->bind_param("i", $crop_id);
        $stmt->execute();
        $_SESSION['flash'] = "❌ Crop rejected.";
    }

    header("Location: crops.php");
    exit();
}

// --------------------
// Fetch all crops
// --------------------
$res = $conn->query("
    SELECT c.id, c.crop_name, c.area_size, c.season, c.expected_yield, c.status, c.created_at, u.name as owner_name 
    FROM crops c 
    JOIN users u ON c.user_id=u.id 
    ORDER BY c.created_at DESC
");
$crops = $res->fetch_all(MYSQLI_ASSOC);

// Fetch users (for dropdown)
$users_res = $conn->query("SELECT id, name FROM users WHERE status='approved'");
$users = $users_res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Crops - Admin - Anndata</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{font-family:Arial,sans-serif;background:#f4f6f9;margin:0;padding:0;display:flex;}
.sidebar{width:220px;background:#2c3e50;color:#fff;flex-shrink:0;height:100vh;position:fixed;overflow:auto;}
.sidebar h2{padding:20px;text-align:center;background:#1a252f;margin:0;font-size:1.2em;}
.sidebar a{display:block;padding:12px 20px;color:#fff;text-decoration:none;font-weight:500;transition:0.2s;}
.sidebar a:hover{background:#34495e;}
.content{margin-left:220px;padding:20px;width:100%;}
h2{margin-bottom:20px;color:#2c3e50;}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);margin-bottom:30px;}
th, td{padding:12px;text-align:center;border-bottom:1px solid #ddd;}
th{background:#2980b9;color:#fff;}
tr:nth-child(even){background:#f9f9f9;}
tr:hover{background:#f1f1f1;}
.btn{padding:6px 12px;border-radius:6px;text-decoration:none;color:#fff;font-weight:bold;transition:0.2s;display:inline-block;margin:2px;}
.approve{background:#27ae60;}
.reject{background:#e67e22;}
.delete{background:#e74c3c;}
.edit{background:#3498db;}
.add-btn{background:#27ae60;margin-bottom:10px;}
.btn:hover{opacity:0.8;}
.flash{margin-bottom:15px;padding:10px;background:#dff0d8;color:#3c763d;border-radius:6px;}
.status-pending{color:#f39c12;font-weight:bold;}
.status-approved{color:#27ae60;font-weight:bold;}
.status-rejected{color:#e74c3c;font-weight:bold;}
form{background:#fff;padding:15px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);margin-bottom:20px;width:400px;display:none;}
form.active{display:block;animation:fadeIn 0.3s ease-in-out;}
@keyframes fadeIn{from{opacity:0;transform:translateY(-10px);}to{opacity:1;transform:translateY(0);}}
label{display:block;margin-top:10px;font-weight:bold;}
input,select{width:100%;padding:8px;margin-top:5px;border:1px solid #ccc;border-radius:6px;}
button{margin-top:15px;background:#27ae60;color:#fff;padding:10px 15px;border:none;border-radius:6px;cursor:pointer;}
button:hover{opacity:0.9;}
.cancel-btn{background:#e74c3c;margin-left:5px;}
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
<h2>🌾 Manage Crops</h2>

<?php if(!empty($flash)): ?>
    <div class="flash"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<!-- Toggle Button -->
<button class="btn add-btn" id="toggleFormBtn"><?= $edit_mode ? "✏️ Edit Crop" : "➕ Add New Crop" ?></button>

<!-- Add/Edit Form -->
<form method="POST" id="cropForm" class="<?= $edit_mode ? 'active' : '' ?>">
    <?php if ($edit_mode): ?>
        <input type="hidden" name="edit_id" value="<?= $edit_crop['id'] ?>">
    <?php endif; ?>

    <label>Crop Name</label>
    <select name="crop_name" required>
        <?php
        $cropsList = ['Wheat','Rice','Maize','Sugarcane','Cotton','Pulses','Groundnut','Mustard','Barley','Soybean'];
        foreach($cropsList as $cropName): ?>
            <option value="<?= $cropName ?>" <?= $edit_mode && $edit_crop['crop_name']==$cropName ? 'selected' : '' ?>><?= $cropName ?></option>
        <?php endforeach; ?>
    </select>

    <label>Area</label>
    <?php
    $area_value = '';
    $area_unit = '';
    if ($edit_mode && strpos($edit_crop['area_size'], ' ') !== false) {
        [$area_value, $area_unit] = explode(' ', $edit_crop['area_size'], 2);
    }
    ?>
    <input type="number" name="area_value" value="<?= htmlspecialchars($area_value) ?>" placeholder="Enter Area" required>
    <select name="area_unit" required>
        <?php foreach(['Bigha','Acre','Hectare'] as $unit): ?>
            <option value="<?= $unit ?>" <?= $edit_mode && $area_unit==$unit ? 'selected' : '' ?>><?= $unit ?></option>
        <?php endforeach; ?>
    </select>

    <label>Season</label>
    <select name="season" required>
        <?php foreach(['Monsoon','Winter','Summer','All Season'] as $season): ?>
            <option value="<?= $season ?>" <?= $edit_mode && $edit_crop['season']==$season ? 'selected' : '' ?>><?= $season ?></option>
        <?php endforeach; ?>
    </select>

    <label>Expected Yield</label>
    <input type="text" name="expected_yield" placeholder="Expected Yield (e.g. 20 Quintals)" value="<?= $edit_mode ? htmlspecialchars($edit_crop['expected_yield']) : '' ?>" required>

    <label>Owner (User)</label>
    <select name="user_id" required>
        <option value="">Select User</option>
        <?php foreach($users as $user): ?>
            <option value="<?= $user['id'] ?>" <?= ($edit_mode && $edit_crop['user_id']==$user['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($user['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Status</label>
    <select name="status" required>
        <option value="pending" <?= $edit_mode && $edit_crop['status']=='pending'?'selected':'' ?>>Pending</option>
        <option value="approved" <?= $edit_mode && $edit_crop['status']=='approved'?'selected':'' ?>>Approved</option>
        <option value="rejected" <?= $edit_mode && $edit_crop['status']=='rejected'?'selected':'' ?>>Rejected</option>
    </select>

    <button type="submit"><?= $edit_mode ? "Update Crop" : "Add Crop" ?></button>
    <?php if ($edit_mode): ?>
        <a href="crops.php" class="btn cancel-btn">Cancel</a>
    <?php endif; ?>
</form>

<!-- Crop Table -->
<table>
    <tr>
        <th>ID</th>
        <th>Crop Name</th>
        <th>Owner</th>
        <th>Season</th>
        <th>Area</th>
        <th>Expected Yield</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php $i=1; foreach($crops as $crop): ?>
    <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($crop['crop_name']) ?></td>
        <td><?= htmlspecialchars($crop['owner_name']) ?></td>
        <td><?= htmlspecialchars($crop['season']) ?></td>
        <td><?= htmlspecialchars($crop['area_size']) ?></td>
        <td><?= htmlspecialchars($crop['expected_yield']) ?></td>
        <td>
            <?php if($crop['status'] == 'pending'): ?>
                <span class="status-pending">Pending</span>
            <?php elseif($crop['status'] == 'approved'): ?>
                <span class="status-approved">Approved</span>
            <?php else: ?>
                <span class="status-rejected">Rejected</span>
            <?php endif; ?>
        </td>
        <td>
            <a href="?edit=<?= $crop['id'] ?>" class="btn edit">Edit</a>
            <a href="?action=approve&id=<?= $crop['id'] ?>" class="btn approve">Approve</a>
            <a href="?action=reject&id=<?= $crop['id'] ?>" class="btn reject">Reject</a>
            <a href="?action=delete&id=<?= $crop['id'] ?>" class="btn delete" onclick="return confirm('Are you sure you want to delete this crop?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<script>
const toggleBtn = document.getElementById('toggleFormBtn');
const cropForm = document.getElementById('cropForm');
if(toggleBtn){
  toggleBtn.addEventListener('click',()=>{
    cropForm.classList.toggle('active');
  });
}
</script>
</div> <!-- end content -->
</body>
</html>
