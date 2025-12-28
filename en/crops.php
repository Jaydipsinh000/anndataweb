<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: register.php");
    exit();
}

require_once "../php/db.php";

$user_id = $_SESSION['user_id'];

// 🔹 Fetch user status and crop limit
$query = "SELECT status, crop_limit FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user_data = $res->fetch_assoc();
$stmt->close();

$user_status = $user_data['status'];
$crop_limit = intval($user_data['crop_limit']); // Admin-defined limit

// Set flash message if pending/blocked
if($user_status == 'pending'){
    $_SESSION['error'] = "❌ Your account is pending approval. You cannot add crops yet.";
} elseif($user_status == 'blocked'){
    $_SESSION['error'] = "❌ Your account is blocked. Contact admin.";
}

// Handle form submit
if(isset($_POST['submit'])){
    if($user_status == 'approved'){ // only approved users can add

        // Count existing crops
        $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM crops WHERE user_id = ?");
        $stmt_count->bind_param("i", $user_id);
        $stmt_count->execute();
        $res_count = $stmt_count->get_result();
        $existing_crops = intval($res_count->fetch_assoc()['total']);
        $stmt_count->close();

        if($existing_crops >= $crop_limit){
            $_SESSION['error'] = "❌ You have reached your crop limit ($crop_limit). Wait for admin approval before adding more.";
        } else {
            $crop_name = $_POST['crop_name'];
            $area_value = $_POST['area_value'];
            $area_unit = $_POST['area_unit'];
            $season = $_POST['season'];
            $expected_yield = $_POST['expected_yield'];
            $area_size = $area_value . " " . $area_unit;

            // Insert crop with status 'pending'
            $stmt = $conn->prepare("INSERT INTO crops (user_id, crop_name, area_size, season, expected_yield, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("issss", $user_id, $crop_name, $area_size, $season, $expected_yield);
            $stmt->execute();
            $stmt->close();
        }

    } else {
        $_SESSION['error'] = "❌ You cannot add crops until your account is approved.";
    }
    header("Location: crops.php");
    exit();
}

// Fetch user crops for display
$stmt = $conn->prepare("SELECT crop_name, area_size, season, expected_yield, status, created_at FROM crops WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$crops = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Crops | Anndata</title>
<link rel="stylesheet" href="../styles.css">
<style>
body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
.container { max-width: 950px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 6px 18px rgba(0,0,0,0.1); }
h2 { text-align: center; margin-bottom: 20px; color: #2c3e50; }
form input, form select { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; }
form button { width: 100%; background: linear-gradient(45deg, #27ae60, #2ecc71); color: #fff; border: none; padding: 12px; font-size: 16px; border-radius: 8px; cursor: pointer; transition: 0.3s; }
form button:hover { background: linear-gradient(45deg, #219150, #27ae60); }
table { width: 100%; margin-top: 30px; border-collapse: collapse; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
table th, table td { padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: center; }
table th { background: #27ae60; color: white; }
table tr:nth-child(even) { background: #f9f9f9; }
table tr:hover { background: #f1f1f1; }
.back-link { display: block; text-align: center; margin-top: 20px; color: #2980b9; text-decoration: none; }
.no-data { text-align: center; margin-top: 20px; color: #888; }
.flash-error { color: red; font-weight: bold; text-align: center; margin-bottom: 15px; }
.status-badge { padding: 4px 8px; border-radius: 6px; color: #fff; font-weight: bold; }
.status-pending { background: #f39c12; }
.status-approved { background: #27ae60; }
.status-rejected { background: #e74c3c; }
</style>
</head>
<body>
<div class="container">

<?php
if(isset($_SESSION['error'])){
    echo '<div class="flash-error">'.htmlspecialchars($_SESSION['error']).'</div>';
    unset($_SESSION['error']);
}
?>

<h2>🌱 Add New Crop</h2>
<?php if($user_status == 'approved' && count($crops) < $crop_limit): ?>
<form method="POST" action="crops.php">
  <select name="crop_name" required>
    <option value="">Select Crop</option>
    <option value="Wheat">Wheat</option>
    <option value="Rice">Rice</option>
    <option value="Maize">Maize</option>
    <option value="Sugarcane">Sugarcane</option>
    <option value="Cotton">Cotton</option>
    <option value="Pulses">Pulses</option>
    <option value="Groundnut">Groundnut</option>
    <option value="Mustard">Mustard</option>
    <option value="Barley">Barley</option>
    <option value="Soybean">Soybean</option>
  </select>
  <input type="number" name="area_value" placeholder="Enter Area" required>
  <select name="area_unit" required>
    <option value="Bigha">Bigha</option>
    <option value="Acre">Acre</option>
    <option value="Hectare">Hectare</option>
  </select>
  <select name="season" required>
    <option value="">Select Season</option>
    <option value="Monsoon">Monsoon</option>
    <option value="Winter">Winter</option>
    <option value="Summer">Summer</option>
    <option value="All Season">All Season</option>
  </select>
  <input type="text" name="expected_yield" placeholder="Expected Yield (e.g. 20 Quintals)" required>
  <button type="submit" name="submit">➕ Add Crop</button>
</form>
<?php elseif(count($crops) >= $crop_limit): ?>
<p class="flash-error">❌ You have reached your crop limit (<?= $crop_limit ?>). Wait for admin approval before adding more.</p>
<?php endif; ?>

<h2>🌾 My Crops</h2>
<?php if(count($crops) > 0): ?>
<table>
<tr>
  <th>Crop Name</th>
  <th>Area</th>
  <th>Season</th>
  <th>Expected Yield</th>
  <th>Status</th>
  <th>Date Added</th>
</tr>
<?php foreach($crops as $crop): ?>
<tr>
  <td><?= htmlspecialchars($crop['crop_name']) ?></td>
  <td><?= htmlspecialchars($crop['area_size']) ?></td>
  <td><?= htmlspecialchars($crop['season']) ?></td>
  <td><?= htmlspecialchars($crop['expected_yield']) ?></td>
  <td><span class="status-badge status-<?= $crop['status'] ?>"><?= ucfirst($crop['status']) ?></span></td>
  <td><?= htmlspecialchars($crop['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p class="no-data">No crops added yet. Start by adding above 🌱</p>
<?php endif; ?>

<a href="profile.php" class="back-link">⬅ Back to Profile</a>
</div>
</body>
</html>
